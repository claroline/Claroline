<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/cursus_session", name="apiv2_cursus_session_")
 */
class SessionController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    private TokenStorageInterface $tokenStorage;
    private TranslatorInterface $translator;
    private RoutingHelper $routingHelper;
    private ToolManager $toolManager;
    private SessionManager $manager;
    private PdfManager $pdfManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RoutingHelper $routingHelper,
        ToolManager $toolManager,
        SessionManager $manager,
        PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->routingHelper = $routingHelper;
        $this->toolManager = $toolManager;
        $this->manager = $manager;
        $this->pdfManager = $pdfManager;
    }

    public static function getName(): string
    {
        return 'cursus_session';
    }

    public static function getClass(): string
    {
        return Session::class;
    }

    protected function getDefaultHiddenFilters(): array
    {
        $filters = [];
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organization
            $organizations = [];
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            }

            $filters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $organizations);

            // hide hidden sessions for non admin
            if (!$this->checkToolAccess('EDIT')) {
                $filters['hidden'] = false;
            }
        }

        $filters['canceled'] = false;

        return $filters;
    }

    /**
     * @Route("/public", name="public", methods={"GET"})
     */
    public function listPublicAction(Request $request): JsonResponse
    {
        $options = static::getOptions();
        $params = $request->query->all();

        $params['hiddenFilters'] = $this->getDefaultHiddenFilters();
        $params['hiddenFilters']['publicRegistration'] = true;
        $params['hiddenFilters']['terminated'] = false;

        // hide hidden sessions for non admin
        if (!$this->checkToolAccess('EDIT')) {
            $params['hiddenFilters']['hidden'] = false;
        }

        return new JsonResponse(
            $this->crud->list(Session::class, $params, $options['list'] ?? [])
        );
    }

    /**
     * @Route("/copy", name="copy", methods={"POST"})
     */
    public function copyAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        /** @var Session[] $sessions */
        $sessions = $this->om->getRepository(Session::class)->findBy([
            'uuid' => $data['ids'],
        ]);

        foreach ($sessions as $session) {
            if ($this->authorization->isGranted('EDIT', $session)) {
                $processed[] = $this->crud->copy($session, [], ['parent' => $session->getCourse()]);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Session $session) {
            return $this->serializer->serialize($session);
        }, $processed));
    }

    /**
     * @Route("/cancel", name="cancel", methods={"POST"})
     */
    public function cancelAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        /** @var Session[] $sessions */
        $sessions = $this->om->getRepository(Session::class)->findBy([
            'uuid' => $data['ids'],
        ]);

        foreach ($sessions as $session) {
            if ($this->authorization->isGranted('EDIT', $session) && !$session->isCanceled()) {
                $session->setCanceled(true);
                $session->setCancelReason($data['cancelReason'] ?? null);

                $cancelTemplate = null;
                $templateRepo = $this->om->getRepository(Template::class);
                if (!empty($data['canceledTemplate']) && $data['canceledTemplate']['id']) {
                    $cancelTemplate = $templateRepo->findOneBy(['uuid' => $data['canceledTemplate']['id']]);
                    //                    if ($cancelTemplate) {
                    //                        $this->manager->sendSessionCancel($session);
                    //                    }
                }
                $session->setCanceledTemplate($cancelTemplate);

                $processed[] = $session;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Session $session) {
            return $this->serializer->serialize($session);
        }, $processed));
    }

    /**
     * @Route("/{id}/pdf", name="download_pdf", methods={"GET"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function downloadPdfAction(Session $session, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $session, [], true);

        return new StreamedResponse(function () use ($session, $request) {
            echo $this->pdfManager->fromHtml(
                $this->manager->generateFromTemplate($session, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($session->getName()).'.pdf',
        ]);
    }

    /**
     * @Route("/{id}/events", name="list_events")
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function listEventsAction(Session $session, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $session, [], true);

        $params = $request->query->all();
        $params['hiddenFilters'] = $this->getDefaultHiddenFilters();
        $params['hiddenFilters']['session'] = $session->getUuid();

        return new JsonResponse(
            $this->crud->list(Event::class, $params)
        );
    }

    /**
     * @Route("/{id}/users/{type}", name="add_users", methods={"PATCH"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function addUsersAction(Session $session, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        $users = $this->decodeIdsString($request, User::class);
        $nbUsers = count($users);

        if (AbstractRegistration::LEARNER === $type && !$this->manager->checkSessionCapacity($session, $nbUsers)) {
            return new JsonResponse(['errors' => [
                $this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus'),
            ]], 422); // not the best status (same as form validation errors)
        }

        $sessionUsers = $this->manager->addUsers($session, $users, $type, true);

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    /**
     * @Route("/{id}/groups/{type}", name="add_groups", methods={"PATCH"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function addGroupsAction(Session $session, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        /** @var Group[] $groups */
        $groups = $this->decodeIdsString($request, Group::class);
        $nbUsers = 0;

        foreach ($groups as $group) {
            $nbUsers += count($this->om->getRepository(User::class)->findByGroup($group));
        }

        if (AbstractRegistration::LEARNER === $type && !$this->manager->checkSessionCapacity($session, $nbUsers)) {
            return new JsonResponse(['errors' => [
                $this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus'),
            ]], 422); // not the best status (same as form validation errors)
        }

        $sessionGroups = $this->manager->addGroups($session, $groups, $type);

        return new JsonResponse(array_map(function (SessionGroup $sessionGroup) {
            return $this->serializer->serialize($sessionGroup);
        }, $sessionGroups));
    }

    /**
     * @Route("/{id}/pending", name="add_pending", methods={"PATCH"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function addPendingAction(Session $session, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        $users = $this->decodeIdsString($request, User::class);
        $sessionUsers = $this->manager->addUsers($session, $users, AbstractRegistration::LEARNER, false);

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    /**
     * @Route("/{id}/self/register", name="self_register", methods={"PUT"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfRegisterAction(Session $session, User $user, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $session, [], true);

        if (!$session->getPublicRegistration() && !$session->getAutoRegistration()) {
            throw new AccessDeniedException();
        }

        $registrationData = $this->decodeRequest($request);

        $sessionUsers = $this->manager->addUsers($session, [$user], AbstractRegistration::LEARNER, false, [
            $user->getUuid() => $registrationData,
        ]);

        return new JsonResponse($this->serializer->serialize($sessionUsers[0]));
    }

    /**
     * This is the endpoint used by confirmation email.
     *
     * @Route("/{id}/self/confirm", name="self_confirm", methods={"GET"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfConfirmAction(Session $session, User $user): RedirectResponse
    {
        $this->checkPermission('OPEN', $session, [], true);

        $sessionUser = $this->om->getRepository(SessionUser::class)->findOneBy(['session' => $session, 'user' => $user]);
        if ($sessionUser && !$sessionUser->isConfirmed()) {
            $this->manager->confirmUsers([$sessionUser]);
        }

        return new RedirectResponse(
            $this->routingHelper->desktopUrl('trainings').'/course/'.$session->getCourse()->getSlug().'/'.$session->getUuid()
        );
    }

    /**
     * @Route("/{id}/invite/all", name="invite_all", methods={"PUT"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function inviteAllAction(Session $session): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        $this->manager->inviteAllSessionLearners($session);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/stats", name="stats", methods={"GET"})
     *
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"id": "uuid"}})
     */
    public function getStatsAction(Session $session): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        $stats = $this->om->getRepository(Course::class)->getRegistrationStats($session->getCourse(), $session);

        return new JsonResponse([
            'total' => $stats['total'],
            'fields' => array_map(function (array $fieldStats) {
                return [
                    'field' => $this->serializer->serialize($fieldStats['field']),
                    'values' => $fieldStats['values'],
                ];
            }, $stats['fields']),
        ]);
    }

    private function checkToolAccess(?string $rights = 'OPEN'): bool
    {
        $trainingsTool = $this->toolManager->getOrderedTool('trainings', DesktopContext::getName());

        if (is_null($trainingsTool) || !$this->authorization->isGranted($rights, $trainingsTool)) {
            return false;
        }

        return true;
    }
}

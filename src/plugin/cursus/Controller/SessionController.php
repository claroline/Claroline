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

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/cursus_session', name: 'apiv2_cursus_session_')]
class SessionController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly RoutingHelper $routingHelper,
        private readonly SessionManager $manager,
        private readonly PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'cursus_session';
    }

    public static function getClass(): string
    {
        return Session::class;
    }

    #[Route(path: '/context/{context}/{contextId}', name: 'context_list', methods: ['GET'])]
    public function listByContextAction(
        string $context,
        ?string $contextId = null,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        if (WorkspaceContext::getName() === $context) {
            $finderRequest->addFilter('workspace', $contextId);
        }

        $sessions = $this->crud->search(Session::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $sessions->toResponse();
    }

    #[Route(path: '/copy', name: 'copy', methods: ['POST'])]
    public function copyAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        /** @var Session[] $sessions */
        $sessions = $this->om->getRepository(Session::class)->findBy(['uuid' => $data]);

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

    #[Route(path: '/{id}/canceled', name: 'list_canceled', methods: ['GET'])]
    public function listCanceledAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Course $course,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('EDIT', $course, [], true);

        $finderRequest->addFilter('course', $course->getUuid());
        $finderRequest->addFilter('canceled', true);

        $sessions = $this->crud->search(Session::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $sessions->toResponse();
    }

    #[Route(path: '/cancel', name: 'cancel', methods: ['POST'])]
    public function cancelAction(Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);

        $processedSessions = $this->manager->cancelSessions(
            $data['ids'],
            $data['cancelReason'] ?? null,
            $data['canceledTemplate'] ?? null
        );

        return new JsonResponse(array_map(function (Session $session) {
            return $this->serializer->serialize($session);
        }, $processedSessions));
    }

    #[Route(path: '/{id}/pdf', name: 'download_pdf', methods: ['GET'])]
    public function downloadPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session,
        Request $request
    ): StreamedResponse {
        $this->checkPermission('OPEN', $session, [], true);

        return new StreamedResponse(function () use ($session, $request): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->generateFromTemplate($session, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($session->getName()).'.pdf',
        ]);
    }

    #[Route(path: '/{id}/events', name: 'list_events', methods: ['GET'])]
    public function listEventsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $session, [], true);

        return $this->crud
            ->search(Event::class, $finderRequest->addFilters([
                'session' => $session->getUuid(),
            ]), [SerializerInterface::SERIALIZE_LIST])
            ->toResponse();
    }

    #[Route(path: '/{id}/users/{type}', name: 'add_users', methods: ['PATCH'])]
    public function addUsersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session,
        string $type,
        Request $request
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $session, [], true);

        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);
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

    #[Route(path: '/{id}/pending', name: 'add_pending', methods: ['PATCH'])]
    public function addPendingAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session,
        Request $request
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $session, [], true);

        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);
        $sessionUsers = $this->manager->addUsers($session, $users, AbstractRegistration::LEARNER, false);

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    #[Route(path: '/{id}/self/register', name: 'self_register', methods: ['PUT'])]
    public function selfRegisterAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session,
        #[CurrentUser] ?User $user,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $session, [], true);

        if (!$session->getPublicRegistration() && !$session->getAutoRegistration()) {
            throw new AccessDeniedException('The session is not opened to self registrations.');
        }

        $registrationData = $this->decodeRequest($request);

        $sessionUsers = $this->manager->addUsers($session, [$user], AbstractRegistration::LEARNER, false, [
            $user->getUuid() => $registrationData,
        ]);

        return new JsonResponse($this->serializer->serialize($sessionUsers[0]));
    }

    /**
     * This is the endpoint used by confirmation email.
     */
    #[Route(path: '/{id}/self/confirm', name: 'self_confirm', methods: ['GET'])]
    public function selfConfirmAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session,
        #[CurrentUser] ?User $user
    ): RedirectResponse {
        $this->checkPermission('OPEN', $session, [], true);

        $sessionUser = $this->om->getRepository(SessionUser::class)->findOneBy(['session' => $session, 'user' => $user]);
        if ($sessionUser && !$sessionUser->isConfirmed()) {
            $this->manager->confirmUsers([$sessionUser]);
        }

        return new RedirectResponse(
            $this->routingHelper->desktopUrl('trainings').'/course/'.$session->getCourse()->getSlug().'/'.$session->getUuid()
        );
    }

    #[Route(path: '/{id}/invite/all', name: 'invite_all', methods: ['PUT'])]
    public function inviteAllAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $session, [], true);

        $this->manager->inviteAllSessionLearners($session);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/stats', name: 'stats', methods: ['GET'])]
    public function getStatsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $session, [], true);

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

    /**
     * Register users to the workspace of the session.
     */
    #[Route(path: '/{id}/workspace', name: 'register_workspace', methods: ['POST'])]
    public function registerToWorkspaceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Session $session
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $session, [], true);

        $sessionUsers = $this->om->getRepository(SessionUser::class)->findNotRegisteredToWorkspace($session);
        $this->manager->registerUsers($sessionUsers, false);

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser, [SerializerInterface::SERIALIZE_MINIMAL]);
        }, $sessionUsers), 201);
    }
}

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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\CourseManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * @Route("/cursus_course")
 */
class CourseController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Environment */
    private $templating;
    /** @var RoutingHelper */
    private $routing;
    /** @var ToolManager */
    private $toolManager;
    /** @var CourseManager */
    private $manager;
    /** @var PdfManager */
    private $pdfManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        Environment $templating,
        RoutingHelper $routing,
        ToolManager $toolManager,
        CourseManager $manager,
        PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->routing = $routing;
        $this->toolManager = $toolManager;
        $this->manager = $manager;
        $this->pdfManager = $pdfManager;
    }

    public function getName()
    {
        return 'cursus_course';
    }

    public function getClass()
    {
        return Course::class;
    }

    public function getIgnore()
    {
        return ['copyBulk', 'schema'];
    }

    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            'create' => [Options::PERSIST_TAG],
            'update' => [Options::PERSIST_TAG],
        ]);
    }

    protected function getDefaultHiddenFilters()
    {
        $filters = [];
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organizations
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            } else {
                $organizations = $this->om->getRepository(Organization::class)->findBy(['default' => true]);
            }

            $filters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $organizations);

            // hide hidden trainings for non admin
            if (!$this->checkToolAccess('EDIT')) {
                $filters['hidden'] = false;
            }
        }

        return $filters;
    }

    /**
     * @Route("/{slug}/open", name="apiv2_cursus_course_open", methods={"GET"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"slug": "slug"}})
     */
    public function openAction(Course $course): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        $defaultSession = null;

        // search for sessions in which the current user is registered
        $user = $this->tokenStorage->getToken()->getUser();
        $registrations = [];
        if ($user instanceof User) {
            $registeredSessions = [];
            $userRegistrations = $this->finder->fetch(SessionUser::class, [
                'user' => $user->getUuid(),
                'course' => $course->getUuid(),
            ]);

            $groupRegistrations = $this->finder->fetch(SessionGroup::class, [
                'user' => $user->getUuid(),
                'course' => $course->getUuid(),
            ]);

            $courseRegistrations = $this->finder->fetch(CourseUser::class, [
                'user' => $user->getUuid(),
                'course' => $course->getUuid(),
            ]);

            $registrations = [
                'users' => array_map(function (SessionUser $sessionUser) use ($registeredSessions) {
                    $registeredSessions[] = $sessionUser->getSession();

                    return $this->serializer->serialize($sessionUser);
                }, $userRegistrations),
                'groups' => array_map(function (SessionGroup $sessionGroup) use ($registeredSessions) {
                    $registeredSessions[] = $sessionGroup->getSession();

                    return $this->serializer->serialize($sessionGroup);
                }, $groupRegistrations),
                'pending' => array_map(function (CourseUser $courseUser) {
                    return $this->serializer->serialize($courseUser);
                }, $courseRegistrations),
            ];

            if (!empty($registeredSessions)) {
                // by default display one of the session the user is registered to
                $defaultSession = $this->serializer->serialize($registeredSessions[0]);
            }
        }

        $sessions = $this->finder->search(Session::class, [
            'filters' => [
                'terminated' => false,
                'course' => $course->getUuid(),
            ],
            'sortBy' => 'startDate',
        ]);

        if (empty($defaultSession)) {
            // current user is not registered to any session yet
            // get the default session to open
            switch ($course->getSessionOpening()) {
                case 'default':
                    $defaultSession = $this->serializer->serialize($course->getDefaultSession());
                    break;
                case 'first_available':
                    if (!empty($sessions['data'])) {
                        $defaultSession = $sessions['data'][0];
                    }
                    break;
            }
        }

        return new JsonResponse([
            'course' => $this->serializer->serialize($course),
            'defaultSession' => $defaultSession,
            'availableSessions' => $sessions['data'],
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/{id}/pdf", name="apiv2_cursus_course_download_pdf", methods={"GET"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function downloadPdfAction(Course $course, Request $request): StreamedResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        return new StreamedResponse(function () use ($course, $request) {
            echo $this->pdfManager->fromHtml(
                $this->manager->generateFromTemplate($course, $request->getLocale())
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($course->getName()).'.pdf',
        ]);
    }

    /**
     * @Route("/{id}/sessions", name="apiv2_cursus_course_list_sessions", methods={"GET"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function listSessionsAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['course'] = $course->getUuid();

        // hide hidden sessions for non admin
        if (!$this->checkToolAccess('EDIT')) {
            $params['hiddenFilters']['hidden'] = false;
        }

        return new JsonResponse(
            $this->finder->search(Session::class, $params)
        );
    }

    /**
     * @Route("/{id}/users", name="apiv2_cursus_course_list_users", methods={"GET"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function listUsersAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        $params = $request->query->all();
        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['course'] = $course->getUuid();

        // only list participants of the same organization
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organizations
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            } else {
                $organizations = $this->om->getRepository(Organization::class)->findBy(['default' => true]);
            }

            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $organizations);
        }

        return new JsonResponse(
            $this->finder->search(CourseUser::class, $params)
        );
    }

    /**
     * @Route("/{id}/users", name="apiv2_cursus_course_add_users", methods={"PATCH"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function addUsersAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $users = $this->decodeIdsString($request, User::class);

        $sessionUsers = $this->manager->addUsers($course, $users);

        return new JsonResponse(array_map(function (CourseUser $courseUser) {
            return $this->serializer->serialize($courseUser);
        }, $sessionUsers));
    }

    /**
     * @Route("/{id}/users", name="apiv2_cursus_course_remove_users", methods={"DELETE"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function removeUsersAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $courseUsers = $this->decodeIdsString($request, CourseUser::class);
        $this->manager->removeUsers($courseUsers);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/move/users", name="apiv2_cursus_course_move_users", methods={"PUT"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function moveUsersAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $data = $this->decodeRequest($request);
        if (empty($data['target']) || empty($data['courseUsers'])) {
            throw new InvalidDataException('Missing either target session or registrations to move.');
        }

        $targetSession = $this->om->getRepository(Session::class)->findOneBy([
            'uuid' => $data['target'],
        ]);

        $courseUsers = [];
        foreach ($data['courseUsers'] as $courseUserId) {
            $courseUser = $this->om->getRepository(CourseUser::class)->findOneBy([
                'uuid' => $courseUserId,
            ]);

            if (!empty($courseUser)) {
                $courseUsers[] = $courseUser;
            }
        }

        $this->manager->moveUsers($targetSession, $courseUsers);

        return new JsonResponse();
    }

    /**
     * @Route("/{id}/move/pending", name="apiv2_cursus_course_move_pending", methods={"PUT"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function moveToPendingAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $data = $this->decodeRequest($request);
        if (empty($data['sessionUsers'])) {
            throw new InvalidDataException('Missing the user registrations to move.');
        }

        $sessionUsers = [];
        foreach ($data['sessionUsers'] as $sessionUserId) {
            $sessionUser = $this->om->getRepository(SessionUser::class)->findOneBy([
                'uuid' => $sessionUserId,
            ]);

            if (!empty($sessionUser)) {
                $sessionUsers[] = $sessionUser;
            }
        }

        $this->manager->moveToPending($course, $sessionUsers);

        return new JsonResponse();
    }

    /**
     * @Route("/{id}/self/register", name="apiv2_cursus_course_self_register", methods={"PUT"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfRegisterAction(Course $course, User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        if (!$course->getPendingRegistrations()) {
            throw new AccessDeniedException();
        }

        $courseUsers = $this->manager->addUsers($course, [$user]);

        return new JsonResponse($this->serializer->serialize($courseUsers[0]));
    }

    private function checkToolAccess(string $rights = 'OPEN'): bool
    {
        $trainingsTool = $this->toolManager->getOrderedTool('trainings', Tool::DESKTOP);

        if (is_null($trainingsTool) || !$this->authorization->isGranted($rights, $trainingsTool)) {
            return false;
        }

        return true;
    }
}

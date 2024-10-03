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
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
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

/**
 * @Route("/cursus_course", name="apiv2_cursus_course_")
 */
class CourseController extends AbstractCrudController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    private TokenStorageInterface $tokenStorage;
    private RoutingHelper $routing;
    private ToolManager $toolManager;
    private CourseManager $manager;
    private PdfManager $pdfManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        RoutingHelper $routing,
        ToolManager $toolManager,
        CourseManager $manager,
        PdfManager $pdfManager,
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->routing = $routing;
        $this->toolManager = $toolManager;
        $this->manager = $manager;
        $this->pdfManager = $pdfManager;
    }

    public static function getName(): string
    {
        return 'cursus_course';
    }

    public static function getClass(): string
    {
        return Course::class;
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'create' => [Options::PERSIST_TAG],
            'update' => [Options::PERSIST_TAG],
        ]);
    }

    protected function getDefaultHiddenFilters(): array
    {
        $filters = [];
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organizations
            $organizations = [];
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            }

            $filters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $organizations);

            // hide hidden trainings for non admin
            if (!$this->checkToolAccess('EDIT')) {
                $filters['hidden'] = false;
            }
        }

        $filters['archived'] = false;

        return $filters;
    }

    /**
     * @Route("/public", name="list_public", methods={"GET"})
     */
    public function listPublicAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->crud->list(
            Course::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'public' => true,
            ])]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @Route("/list/archived", name="list_archived", methods={"GET"})
     */
    public function listArchivedAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->crud->list(
            Course::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'archived' => true,
            ])]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @Route("/list/existing", name="list_existing", methods={"GET"})
     */
    public function listNoWorkspaceAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $filters = array_merge($request->query->all(), [
            'hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'workspace' => null,
            ]),
        ]);

        $list = $this->crud->list(Course::class, $filters, $this->getOptions()['list']);

        return new JsonResponse($list);
    }

    /**
     * @Route("/archive", name="archive", methods={"POST"})
     */
    public function archiveAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = json_decode($request->getContent(), true);

        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findBy([
            'uuid' => $data['ids'],
        ]);

        foreach ($courses as $course) {
            if ($this->authorization->isGranted('ADMINISTRATE', $course) && !$course->isArchived()) {
                $course->setArchived(true);
                $processed[] = $course;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Course $course) {
            return $this->serializer->serialize($course);
        }, $processed));
    }

    /**
     * @Route("/restore", name="restore", methods={"POST"})
     */
    public function restoreAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = json_decode($request->getContent(), true);

        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findBy([
            'uuid' => $data['ids'],
        ]);

        foreach ($courses as $course) {
            if ($this->authorization->isGranted('ADMINISTRATE', $course) && $course->isArchived()) {
                $course->setArchived(false);
                $processed[] = $course;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Course $course) {
            return $this->serializer->serialize($course);
        }, $processed));
    }

    /**
     * @Route("/copy", name="copy", methods={"POST"})
     */
    public function copyAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        $workspaceData = $data['workspace'] ?? null;
        $workspace = null;

        if ($workspaceData) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
        }

        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findBy([
            'uuid' => $data['ids'],
        ]);

        foreach ($courses as $course) {
            if ($this->authorization->isGranted('ADMINISTRATE', $course)) {
                $copy = $this->crud->copy($course);
                if (1 === count($courses) && $workspace) {
                    $copy->setWorkspace($workspace);
                }
                $processed[] = $copy;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Course $course) {
            return $this->serializer->serialize($course);
        }, $processed));
    }

    /**
     * @Route("/{id}/bind", name="bind_workspace", methods={"PATCH"})
     *
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function bindCourseToWorkspaceAction(Course $course, Request $request): JsonResponse
    {
        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);

        $workspaceData = $data['workspace'] ?? null;
        $workspace = null;

        if ($workspaceData) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $workspaceData['id']]);
        }

        if ($this->authorization->isGranted('ADMINISTRATE', $course)) {
            $course->setWorkspace($workspace);
        } else {
            throw new AccessDeniedException();
        }

        $this->om->endFlushSuite();

        return new JsonResponse($this->serializer->serialize($course));
    }

    /**
     * @Route("/{slug}/open", name="open", methods={"GET"})
     *
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
            $registrations = $this->manager->getRegistrations($course, $user);

            // by default display one of the session the user is registered to
            if (!empty($registrations['users'])) {
                $defaultSession = $this->om->getRepository(Session::class)->findOneBy([
                    'uuid' => $registrations['users'][0]['session']['id'],
                ]);
            } elseif (!empty($registrations['groups'])) {
                $defaultSession = $this->om->getRepository(Session::class)->findOneBy([
                    'uuid' => $registrations['groups'][0]['session']['id'],
                ]);
            }
        }

        $sessions = $this->om->getRepository(Session::class)->findAvailable($course);

        if (empty($defaultSession)) {
            // current user is not registered to any session yet
            // get the default session to open
            switch ($course->getSessionOpening()) {
                case 'default':
                    $defaultSession = $course->getDefaultSession();
                    break;
                case 'first_available':
                    if (!empty($sessions)) {
                        $defaultSession = $sessions[0];
                    }
                    break;
            }
        }

        return new JsonResponse([
            'course' => $this->serializer->serialize($course),
            'defaultSession' => $defaultSession ? $this->serializer->serialize($defaultSession) : null,
            'availableSessions' => array_map(function (Session $session) {
                return $this->serializer->serialize($session);
            }, $sessions),
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/{id}/pdf", name="download_pdf", methods={"GET"})
     *
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
     * @Route("/{id}/sessions", name="list_sessions", methods={"GET"})
     *
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
        $params['hiddenFilters']['canceled'] = false;

        // hide hidden sessions for non admin
        if (!$this->checkToolAccess('EDIT')) {
            $params['hiddenFilters']['hidden'] = false;
        }

        return new JsonResponse(
            $this->crud->list(Session::class, $params)
        );
    }

    /**
     * @Route("/{id}/users", name="add_pending", methods={"PATCH"})
     *
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function addPendingAction(Course $course, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $users = $this->decodeIdsString($request, User::class);

        $sessionUsers = $this->manager->addUsers($course, $users);

        return new JsonResponse(array_map(function (CourseUser $courseUser) {
            return $this->serializer->serialize($courseUser);
        }, $sessionUsers));
    }

    /**
     * @Route("/{id}/move/users", name="move_pending", methods={"PUT"})
     *
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function movePendingAction(Course $course, Request $request): JsonResponse
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
     * @Route("/{id}/move/pending", name="move_to_pending", methods={"PUT"})
     *
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
     * @Route("/{id}/self/register", name="self_register", methods={"PUT"})
     *
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfRegisterAction(Course $course, User $user, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $course, [], true);

        if (!$course->getPendingRegistrations()) {
            throw new AccessDeniedException();
        }

        $registrationData = $this->decodeRequest($request);

        $courseUsers = $this->manager->addUsers($course, [$user], [
            $user->getUuid() => $registrationData,
        ]);

        return new JsonResponse($this->serializer->serialize($courseUsers[0]));
    }

    /**
     * @Route("/{id}/stats", name="stats", methods={"GET"})
     *
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function getStatsAction(Course $course): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $stats = $this->om->getRepository(Course::class)->getRegistrationStats($course);

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

    private function checkToolAccess(string $rights = 'OPEN'): bool
    {
        $trainingsTool = $this->toolManager->getOrderedTool('trainings', DesktopContext::getName());

        if (is_null($trainingsTool) || !$this->authorization->isGranted($rights, $trainingsTool)) {
            return false;
        }

        return true;
    }
}

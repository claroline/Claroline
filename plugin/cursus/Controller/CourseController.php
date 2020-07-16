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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Manager\CourseManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/cursus_course")
 */
class CourseController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ToolManager */
    private $toolManager;
    /** @var CourseManager */
    private $manager;

    /**
     * CourseController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TokenStorageInterface         $tokenStorage
     * @param ToolManager                   $toolManager
     * @param CourseManager                 $manager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        CourseManager $manager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->toolManager = $toolManager;
        $this->manager = $manager;
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
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();

            return [
                'organization' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations()),
            ];
        }

        return [];
    }

    /**
     * @EXT\Route("/available", name="apiv2_cursus_course_available")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAvailableAction(Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();
        $params['hiddenFilters'] = $this->getDefaultHiddenFilters();

        if (empty($params['filters'])) {
            $params['filters'] = [];
        }

        $params['filters']['available'] = true;

        return new JsonResponse(
            $this->finder->search(Course::class, $params)
        );
    }

    /**
     * @EXT\Route("/{slug}/open", name="apiv2_cursus_course_open")
     * @EXT\ParamConverter("course", class="ClarolineCursusBundle:Course", options={"mapping": {"slug": "slug"}})
     *
     * @param Course $course
     *
     * @return JsonResponse
     */
    public function openAction(Course $course)
    {
        if (!$this->authorization->isGranted('OPEN', $course)) {
            throw new AccessDeniedException();
        }

        $sessions = $this->finder->search(CourseSession::class, [
            'filters' => [
                'not_ended' => true,
                'course' => $course->getUuid(),
            ],
        ]);

        return new JsonResponse([
            'course' => $this->serializer->serialize($course),
            'defaultSession' => $course->getDefaultSession() ? $this->serializer->serialize($course->getDefaultSession()) : null,
            'availableSessions' => $sessions['data'],
        ]);
    }

    /**
     * @EXT\Route("/{id}/sessions", name="apiv2_cursus_course_list_sessions")
     * @EXT\ParamConverter(
     *     "course",
     *     class="ClarolineCursusBundle:Course",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Course  $course
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listSessionsListAction(User $user, Course $course, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['course'] = $course->getUuid();

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }

        return new JsonResponse(
            $this->finder->search('Claroline\CursusBundle\Entity\CourseSession', $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/users",
     *     name="apiv2_cursus_list_users"
     * )
     * @EXT\ParamConverter(
     *     "cursus",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("GET")
     *
     * @param Cursus  $cursus
     * @param int     $type
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listCursusUsersAction(Cursus $cursus, $type, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['cursus'] = $cursus->getUuid();
        $params['hiddenFilters']['type'] = intval($type);

        return new JsonResponse(
            $this->finder->search(CursusUser::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/users",
     *     name="apiv2_cursus_add_users"
     * )
     * @EXT\ParamConverter(
     *     "cursus",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PATCH")
     *
     * @param Cursus  $cursus
     * @param int     $type
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addUsersAction(Cursus $cursus, $type, Request $request)
    {
        $this->checkToolAccess();
        $users = $this->decodeIdsString($request, User::class);
        $cursusUsers = $this->manager->addUsersToCursus($cursus, $users, intval($type));

        return new JsonResponse(array_map(function (CursusUser $cursusUser) {
            return $this->serializer->serialize($cursusUser);
        }, $cursusUsers));
    }

    /**
     * @EXT\Route(
     *     "/remove/users",
     *     name="apiv2_cursus_remove_users"
     * )
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeUsersAction(Request $request)
    {
        $this->checkToolAccess();

        $cursusUsers = $this->decodeIdsString($request, CursusUser::class);
        $this->manager->deleteEntities($cursusUsers);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/groups",
     *     name="apiv2_cursus_list_groups"
     * )
     * @EXT\ParamConverter(
     *     "cursus",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("GET")
     *
     * @param Cursus  $cursus
     * @param int     $type
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listCursusGroupsAction(Cursus $cursus, $type, Request $request)
    {
        $this->checkToolAccess();

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['cursus'] = $cursus->getUuid();
        $params['hiddenFilters']['type'] = intval($type);

        return new JsonResponse(
            $this->finder->search(CursusGroup::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/groups",
     *     name="apiv2_cursus_add_groups"
     * )
     * @EXT\ParamConverter(
     *     "cursus",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PATCH")
     *
     * @param Cursus  $cursus
     * @param int     $type
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addGroupsAction(Cursus $cursus, $type, Request $request)
    {
        $this->checkToolAccess();
        $groups = $this->decodeIdsString($request, Group::class);
        $cursusGroups = $this->manager->addGroupsToCursus($cursus, $groups, intval($type));

        return new JsonResponse(array_map(function (CursusGroup $cursusGroup) {
            return $this->serializer->serialize($cursusGroup);
        }, $cursusGroups));
    }

    /**
     * @EXT\Route(
     *     "/remove/groups",
     *     name="apiv2_cursus_remove_groups"
     * )
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeGroupsAction(Request $request)
    {
        $this->checkToolAccess();

        $cursusGroups = $this->decodeIdsString($request, CursusGroup::class);
        $this->manager->deleteEntities($cursusGroups);

        return new JsonResponse();
    }

    /**
     * @param string $rights
     */
    private function checkToolAccess($rights = 'OPEN')
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool');

        if (is_null($cursusTool) || !$this->authorization->isGranted($rights, $cursusTool)) {
            throw new AccessDeniedException();
        }
    }
}

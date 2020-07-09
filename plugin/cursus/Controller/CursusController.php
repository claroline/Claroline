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
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
use Claroline\CursusBundle\Manager\CursusManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/cursus")
 */
class CursusController extends AbstractCrudController
{
    use HasOrganizationsTrait;

    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var CursusManager */
    private $cursusManager;

    /** @var ToolManager */
    private $toolManager;

    /**
     * CursusController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param CursusManager                 $cursusManager
     * @param ToolManager                   $toolManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->toolManager = $toolManager;
    }

    public function getName()
    {
        return 'cursus';
    }

    public function getClass()
    {
        return Cursus::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/list",
     *     name="apiv2_cursus_list"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function cursusListAction(User $user, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['parent'] = null;

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }
        $data = $this->finder->search(Cursus::class, $params, [Options::IS_RECURSIVE]);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/{id}/organization",
     *     name="apiv2_cursus_list_organizations"
     * )
     * @EXT\ParamConverter(
     *     "cursus",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Cursus  $cursus
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listOrganizationsAction(Cursus $cursus, Request $request)
    {
        $this->checkToolAccess();
        $ids = array_map(function (Organization $organization) {
            return $organization->getUuid();
        }, $cursus->getOrganizations()->toArray());

        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Organization\Organization', array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['whitelist' => $ids]]
            ))
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/add/courses",
     *     name="apiv2_cursus_add_courses"
     * )
     * @EXT\ParamConverter(
     *     "parent",
     *     class="ClarolineCursusBundle:Cursus",
     *     options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param Cursus  $parent
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addCourseToCursusAction(Cursus $parent, Request $request)
    {
        $this->checkToolAccess();
        $courses = $this->decodeIdsString($request, Course::class);
        $createdCursus = $this->cursusManager->addCoursesToCursus($parent, $courses);

        return new JsonResponse(array_map(function (Cursus $cursus) {
            return $this->serializer->serialize($cursus);
        }, $createdCursus));
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
        $cursusUsers = $this->cursusManager->addUsersToCursus($cursus, $users, intval($type));

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
        $this->cursusManager->deleteEntities($cursusUsers);

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
        $cursusGroups = $this->cursusManager->addGroupsToCursus($cursus, $groups, intval($type));

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
        $this->cursusManager->deleteEntities($cursusGroups);

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

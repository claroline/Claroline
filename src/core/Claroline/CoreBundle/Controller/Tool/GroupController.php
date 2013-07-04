<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Doctrine\ORM\EntityRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleSubscribeEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleUnsubscribeEvent;
use Claroline\CoreBundle\Library\Resource\Converter;
use Claroline\CoreBundle\Library\Utilities\PaginatorParser;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class GroupController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';
    const NUMBER_GROUP_PER_ITERATION = 25;

    private $groupManager;
    private $roleManager;
    private $userManager;
    private $eventDispatcher;
    private $pagerFactory;
    private $security;
    private $router;
    private $converter;
    private $paginatorParser;

    /**
     * @DI\InjectParams({
     *     "groupManager"       = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"       = @DI\Inject("claroline.manager.user_manager"),
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "pagerFactory"       = @DI\Inject("claroline.pager.pager_factory"),
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "converter"          = @DI\Inject("claroline.resource.converter"),
     *     "paginatorParser"    = @DI\Inject("claroline.utilities.paginator_parser")
     * })
     */
    public function __construct(
        GroupManager $groupManager,
        RoleManager $roleManager,
        UserManager $userManager,
        EventDispatcher $eventDispatcher,
        PagerFactory $pagerFactory,
        SecurityContextInterface $security,
        UrlGeneratorInterface $router,
        Converter $converter,
        PaginatorParser $paginatorParser
    )
    {
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->pagerFactory = $pagerFactory;
        $this->security = $security;
        $this->router = $router;
        $this->converter = $converter;
        $this->paginatorParser = $paginatorParser;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/groups/registered/page/{page}",
     *     name="claro_workspace_registered_group_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Route(
     *     "/{workspaceId}/groups/registered/page/{page}/search/{search}",
     *     name="claro_workspace_registered_group_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\group_management:registeredGroups.html.twig")
     */
    public function registeredGroupsListAction(AbstractWorkspace $workspace, $page, $search)
    {
        $this->checkRegistration($workspace);
        $query = ($search == "") ?
            $this->groupManager->getGroupsByWorkspace($workspace, true) :
            $this->groupManager->getGroupsByWorkspaceAndName($workspace, $search, true);
        $pager = $this->pagerFactory->createPager($query, $page);

        return array('workspace' => $workspace, 'pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/groups/unregistered/page/{page}",
     *     name="claro_workspace_unregistered_group_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Route(
     *     "/{workspaceId}/groups/unregistered/page/{page}/search/{search}",
     *     name="claro_workspace_unregistered_group_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\group_management:unregisteredGroups.html.twig")
     */
    public function unregiseredGroupsListAction(AbstractWorkspace $workspace, $page, $search)
    {
        $this->checkRegistration($workspace, false);
        $query = ($search == "") ?
            $this->groupManager->getWorkspaceOutsiders($workspace, true) :
            $this->groupManager->getWorkspaceOutsidersByName($workspace, $search, true);
        $pager = $this->pagerFactory->createPager($query, $page);

        return array('workspace' => $workspace, 'pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tools/group/{groupId}",
     *     name="claro_workspace_tools_show_group_parameters",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     *
     * @EXT\Route(
     *     "/{workspaceId}/group/{groupId}",
     *     name="claro_workspace_tools_edit_group_parameters",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "groupId"="^(?=.*[1-9].*$)\d*$" }
     * )
     * @EXT\Method({"POST", "GET"})
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\group_management:groupParameters.html.twig")
     *
     * Renders the group parameter page with its layout and
     * edit the group parameters for the selected workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $groupId the group id
     *
     * @return Response
     */
    public function groupParametersAction(AbstractWorkspace $workspace, Group $group)
    {
        $this->checkRegistration($workspace, false);
        $role = $this->roleManager->getWorkspaceRole($group, $workspace);
        $defaultData = array('role' => $role);
        $workspaceId = $workspace->getId();
        $form = $this->createFormBuilder($defaultData, array('translation_domain' => 'platform'))
            ->add(
                'role',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'property' => 'translationKey',
                    'query_builder' => function (EntityRepository $er) use ($workspaceId) {
                        return $er->createQueryBuilder('wr')
                            ->select('role')
                            ->from('Claroline\CoreBundle\Entity\Role', 'role')
                            ->leftJoin('role.workspace', 'workspace')
                            ->where('workspace.id = :workspaceId')
                            ->andWhere("role.name != 'ROLE_ANONYMOUS'")
                            ->setParameter('workspaceId', $workspaceId);
                    }
                )
            )
            ->getForm();

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $parameters = $request->request->all();
            //cannot bind request: why ?
            $newRole = $this->roleManager->getRoleById($parameters['form']['role']);

            //verifications: can we change his role.
            if ($newRole->getId() != $this->roleManager->getManagerRole($workspace)->getId()) {
                $this->checkRemoveManagerRoleIsValid(array($group), $workspace);
            }

            $this->roleManager->dissociateRole($group, $role);
            $this->roleManager->associateRole($group, $newRole);
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('workspaceId' => $workspaceId, 'toolName' => 'group_management')
            );

            $log = new LogWorkspaceRoleUnsubscribeEvent($role, null, $group);
            $this->eventDispatcher->dispatch('log', $log);

            $log = new LogWorkspaceRoleSubscribeEvent($newRole, null, $group);
            $this->eventDispatcher->dispatch('log', $log);

            return new RedirectResponse($route);
        }

        return array(
            'workspace' => $workspace,
            'group' => $group,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/groups",
     *     name="claro_workspace_delete_groups",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("DELETE")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes many groups from a workspace.
     * It uses a query string of groupIds as parameter (groupIds[]=1&groupIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function removeGroupsAction(AbstractWorkspace $workspace, array $groups)
    {
        $this->checkRegistration($workspace, false);
        $roles = $this->roleManager->getRolesByWorkspace($workspace);

        $this->checkRemoveManagerRoleIsValid($groups, $workspace);

        foreach ($groups as $group) {
            foreach ($roles as $role) {
                if ($group->hasRole($role->getName())) {
                    $this->roleManager->dissociateRole($group, $role);

                    $log = new LogWorkspaceRoleUnsubscribeEvent($role, null, $group);
                    $this->eventDispatcher->dispatch('log', $log);
                }
            }
        }

        return new Response("success", 204);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/add/group",
     *     name="claro_workspace_multiadd_group",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("PUT")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true}
     * )
     *
     * Adds many groups to a workspace.
     * It uses a query string of groupIds as parameter (groupIds[]=1&groupIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function addGroupsAction(AbstractWorkspace $workspace, array $groups)
    {
        $this->checkRegistration($workspace, false);
        $role = $this->roleManager->getCollaboratorRole($workspace);

        foreach ($groups as $group) {
            $this->roleManager->associateRole($group, $role);

            $log = new LogWorkspaceRoleSubscribeEvent($role, null, $group);
            $this->eventDispatcher->dispatch('log', $log);
        }

        $response = new Response($this->converter->jsonEncodeGroups($groups));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Checks if the role manager of the group can be changed.
     * There should be awlays at least one manager of a workspace.
     *
     * @param array $groupIds an array of group ids.
     * @param AbstractWorkspace $workspace the relevant workspace
     *
     * @throws LogicException
     */
    private function checkRemoveManagerRoleIsValid(array $groups, AbstractWorkspace $workspace)
    {
        $managerRole = $this->roleManager->getManagerRole($workspace);
        $countRemovedManagers = 0;

        foreach ($groups as $group) {
            if ($group->hasRole($managerRole->getName())) {
                $countRemovedManagers += count($group->getUsers());
            }
        }

        $userManagers = $this->userManager->getUserByWorkspaceAndRole($workspace, $managerRole);
        $countUserManagers = count($userManagers);

        if ($countRemovedManagers >= $countUserManagers) {
            throw new LogicException(
                "You can't remove every managers(you're trying to remove {$countRemovedManagers} "
                . "manager(s) out of {$countUserManagers})"
            );
        }
    }

    /**
     * Checks if the current user has access to the group management tool.
     *
     * @param AbstractWorkspace $workspace
     * @param boolean           $allowAnonymous
     *
     * @throws AccessDeniedException
     */
    private function checkRegistration(AbstractWorkspace $workspace, $allowAnonymous = true)
    {
        if (($this->security->getToken()->getUser() === 'anon.' && !$allowAnonymous)
            || !$this->security->isGranted('group_management', $workspace)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Most dql request required by this controller are paginated.
     * This function transform the results of the repository in an array.
     *
     * @param Paginator $paginator the return value of the Repository using a paginator.
     *
     * @return array.
     */
    private function paginatorToArray($paginator)
    {
        return $this->paginatorParser->paginatorToArray($paginator);
    }
}

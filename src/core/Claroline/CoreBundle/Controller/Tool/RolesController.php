<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;

class RolesController extends Controller
{
    private $roleManager;
    private $userManager;
    private $groupManager;
    private $resourceManager;
    private $security;
    private $formFactory;
    private $router;
    private $request;

    /**
     * @DI\InjectParams({
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"      = @DI\Inject("claroline.manager.group_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "security"         = @DI\Inject("security.context"),
     *     "formFactory"      = @DI\Inject("claroline.form.factory"),
     *     "router"           = @DI\Inject("router"),
     *     "request"          = @DI\Inject("request")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        UserManager $userManager,
        GroupManager $groupManager,
        ResourceManager $resourceManager,
        SecurityContextInterface $security,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request
    )
    {
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->resourceManager = $resourceManager;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
    }
    /**
     * @EXT\Route(
     *     "/{workspace}/roles/config",
     *     name="claro_workspace_roles"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roles.html.twig")
     */
    public function configureRolePageAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $roles = $this->roleManager->getRolesByWorkspace($workspace);

        return array('workspace' => $workspace, 'roles' => $roles);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/roles/create/form",
     *     name="claro_workspace_role_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roleCreation.html.twig")
     */
    public function createRoleFormAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_ROLE);

        return array('workspace' => $workspace, 'form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/roles/create",
     *     name="claro_workspace_role_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roleCreation.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function createRoleAction(AbstractWorkspace $workspace, User $user)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_ROLE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('translationKey')->getData();
            $requireDir = $form->get('requireDir')->getData();
            $role = $this->roleManager
                ->createWorkspaceRole('ROLE_WS_' . strtoupper($name) . '_' . $workspace->getGuid(), $name, $workspace);

            if ($requireDir) {
                $resourceTypes = $this->resourceManager->getAllResourceTypes();
                $creations = array();

                foreach ($resourceTypes as $resourceType) {
                    $creations[] = array('name' => $resourceType->getName());
                }


                $this->resourceManager->create(
                    $this->resourceManager->createResource(
                        'Claroline\CoreBundle\Entity\Resource\Directory',
                        $name
                    ),
                    $this->resourceManager->getResourceTypeByName('directory'),
                    $user,
                    $workspace,
                    $this->resourceManager->getWorkspaceRoot($workspace),
                    null,
                    array(
                        'ROLE_WS_' .  strtoupper($name) => array(
                            'canOpen' => true,
                            'canEdit' => true,
                            'canCopy' => true,
                            'canDelete' => true,
                            'canExport' => true,
                            'canCreate' => $creations,
                            'role' => $role
                        ),
                        'ROLE_WS_MANAGER' => array(
                            'canOpen' => true,
                            'canEdit' => true,
                            'canCopy' => true,
                            'canDelete' => true,
                            'canExport' => true,
                            'canCreate' => $creations,
                            'role' => $this->roleManager->getManagerRole($workspace)
                        )
                    )
                );
            }

            $route = $this->router->generate(
                'claro_workspace_roles',
                array('workspace' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/remove",
     *     name="claro_workspace_role_remove"
     * )
     * @EXT\Method("GET")
     */
    public function removeRoleAction(AbstractWorkspace $workspace, Role $role)
    {
        $this->checkAccess($workspace);
        $this->roleManager->remove($role);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/edit/form",
     *     name="claro_workspace_role_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roleEdit.html.twig")
     */
    public function editRoleFormAction(Role $role, AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_ROLE_TRANSLATION, array(), $role);

        return array('workspace' => $workspace, 'form' => $form->createView(), 'role' => $role);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/edit",
     *     name="claro_workspace_role_edit"
     * )
     * @EXT\Method("POST")
     */
    public function editRoleAction(Role $role, AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_ROLE_TRANSLATION, array(), $role);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->roleManager->edit($role);
            $route = $this->router->generate(
                'claro_workspace_role_edit_form',
                array('role' => $role->getId(), 'workspace' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array('workspace' => $workspace, 'form' => $form->createView(), 'role' => $role);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/remove/role/{role}/user/{user}",
     *     name="claro_workspace_remove_role_from_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method({"DELETE", "GET"})
     */
    public function removeUserFromRoleAction(User $user, Role $role, AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $this->roleManager->dissociateWorkspaceRole($user, $workspace, $role);

        return new Response('success');
    }

   /**
     * @EXT\Route(
     *     "/{workspace}/add/role/user",
     *     name="claro_workspace_add_roles_to_users",
     *     options={"expose"=true}
     * )
     * @EXT\Method({"PUT", "GET"})
     *
     * @EXT\ParamConverter(
     *     "users",
     *     class="ClarolineCoreBundle:User",
     *     options={"multipleIds"=true, "name"="userIds"}
     * )
     *  @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "name"="roleIds"}
     * )
     *
     * @return Response
     */
   public function addUsersToRolesAction(array $users, array $roles, AbstractWorkspace $workspace)
   {
       $this->checkAccess($workspace);
       $this->roleManager->associateRolesToSubjects($users, $roles);

       return new Response('success');
   }

   /**
     * @EXT\Route(
     *     "/{workspace}/remove/role/{role}/group/{group}",
     *     name="claro_workspace_remove_role_from_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method({"DELETE", "GET"})
     */
   public function removeGroupFromRoleAction(Group $group, Role $role, AbstractWorkspace $workspace)
   {
       $this->checkAccess($workspace);
       $this->roleManager->dissociateWorkspaceRole($group, $workspace, $role);

       return new Response('success');
   }

  /**
     * @EXT\Route(
     *     "/{workspace}/add/role/group",
     *     name="claro_workspace_add_roles_to_groups",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds"=true, "name"="groupIds"}
     * )
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "name"="roleIds"}
     * )
     */
   public function addGroupsToRolesAction(array $groups, array $roles, AbstractWorkspace $workspace)
   {
       $this->checkAccess($workspace);
       $this->roleManager->associateRolesToSubjects($groups, $roles);

       return new Response('success');
   }

       /**
     * @EXT\Route(
     *     "/{workspace}/users/registered/page/{page}/{withUnregistered}",
     *     name="claro_workspace_registered_user_list",
     *     defaults={"page"=1, "search"="", "withUnregistered"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/{workspace}/users/registered/page/{page}/search/{search}/{withUnregistered}",
     *     name="claro_workspace_registered_user_list_search",
     *     defaults={"page"=1, "withUnregistered"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "isRequired"=false, "name"="roleIds"}
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:workspaceUsers.html.twig")
     */
    public function usersListAction(
        AbstractWorkspace $workspace,
        $page,
        $search,
        $withUnregistered,
        array $roles = array()
    )
    {
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

        if ($withUnregistered === '1') {
            if ($search === '') {
                $pager = $this->userManager->getOutsidersByWorkspaceRoles(
                    array_map('unserialize', array_diff(array_map('serialize', $wsRoles), array_map('serialize', $roles))),
                    $workspace,
                    $page
                );
            } else {
                $pager = $this->userManager->getOutsidersByWorkspaceRolesAndName(
                    array_map('unserialize', array_diff(array_map('serialize', $wsRoles), array_map('serialize', $roles))),
                    $search,
                    $workspace,
                    $page
                );
            }
        } else {
            if ($search === '') {
                $pager = $this->userManager->getUsersByRoles($roles, $page);
            } else {
                $pager = $this->userManager->getUsersByRolesAndName($roles, $search, $page);
            }
        }

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'roles' => $roles,
            'withUnregistered' => $withUnregistered
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/groups/registered/page/{page}/{withUnregistered}",
     *     name="claro_workspace_registered_group_list",
     *     defaults={"page"=1, "search"="", "withUnregistered"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/{workspace}/groups/registered/page/{page}/search/{search}/{withUnregistered}",
     *     name="claro_workspace_registered_group_list_search",
     *     defaults={"page"=1, "withUnregistered"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "isRequired"=false, "name"="roleIds"}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:workspaceGroups.html.twig")
     */
    public function groupsListAction(
        AbstractWorkspace $workspace,
        $page,
        $search,
        $withUnregistered,
        array $roles = array()
    )
    {
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

        if ($withUnregistered === '1') {
            if ($search === '') {
                $pager = $this->groupManager->getOutsidersByWorkspaceRoles(
                    array_map('unserialize', array_diff(array_map('serialize', $wsRoles), array_map('serialize', $roles))),
                    $workspace,
                    $page
                );
            } else {
                $pager = $this->groupManager->getOutsidersByWorkspaceRolesAndName(
                    array_map('unserialize', array_diff(array_map('serialize', $wsRoles), array_map('serialize', $roles))),
                    $search,
                    $workspace,
                    $page
                );
            }
        } else {
            if ($search === '') {
                $pager = $this->groupManager->getGroupsByRoles($roles, $page);
            } else {
                $pager = $this->groupManager->getGroupsByRolesAndName($roles, $search, $page);
            }
        }

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'roles' => $roles,
            'withUnregistered' => $withUnregistered
        );
    }

    private function checkAccess(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}

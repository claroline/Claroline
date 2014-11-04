<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\CompetenceManager;
use Claroline\CoreBundle\Manager\workspaceUserQueueManager;
use Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException;
use JMS\DiExtraBundle\Annotation as DI;

class RolesController extends Controller
{
    private $roleManager;
    private $userManager;
    private $groupManager;
    private $resourceManager;
    private $rightsManager;
    private $security;
    private $formFactory;
    private $router;
    private $request;
    private $translator;
    private $cptManager;
    private $wksUqmanager;

    /**
     * @DI\InjectParams({
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "groupManager"     = @DI\Inject("claroline.manager.group_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"    = @DI\Inject("claroline.manager.rights_manager"),
     *     "facetManager"     = @DI\Inject("claroline.manager.facet_manager"),
     *     "security"         = @DI\Inject("security.context"),
     *     "formFactory"      = @DI\Inject("claroline.form.factory"),
     *     "router"           = @DI\Inject("router"),
     *     "request"          = @DI\Inject("request"),
     *     "translator"       = @DI\Inject("translator"),
     *     "cptManager"       = @DI\Inject("claroline.manager.competence_manager"),
     *     "wksUqmanager"     = @DI\Inject("claroline.manager.workspace_user_queue_manager"),
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        UserManager $userManager,
        GroupManager $groupManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        FacetManager $facetManager,
        SecurityContextInterface $security,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request,
        TranslatorInterface $translator,
        CompetenceManager $cptManager,
        workspaceUserQueueManager $wksUqmanager
    )
    {
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->facetManager = $facetManager;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
        $this->translator = $translator;
        $this->cptManager = $cptManager;
        $this->wksUqmanager = $wksUqmanager;
    }
    /**
     * @EXT\Route(
     *     "/{workspace}/roles/config",
     *     name="claro_workspace_roles"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roles.html.twig")
     */
    public function configureRolePageAction(Workspace $workspace)
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
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roleCreation.html.twig")
     */
    public function createRoleFormAction(Workspace $workspace)
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
    public function createRoleAction(Workspace $workspace, User $user)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_ROLE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('translationKey')->getData();
            $requireDir = $form->get('requireDir')->getData();
            $role = $this->roleManager
                ->createWorkspaceRole('ROLE_WS_' . strtoupper($name) . '_' . $workspace->getGuid(), $name, $workspace);

            //add the role to every resource of that workspace
            $nodes = $this->resourceManager->getByWorkspace($workspace);

            foreach ($nodes as $node) {
                $this->rightsManager->create(0, $role, $node, false, array());
            }

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
                            'open' => true,
                            'edit' => true,
                            'copy' => true,
                            'delete' => true,
                            'export' => true,
                            'create' => $creations,
                            'role' => $role
                        ),
                        'ROLE_WS_MANAGER' => array(
                            'open' => true,
                            'edit' => true,
                            'copy' => true,
                            'delete' => true,
                            'export' => true,
                            'create' => $creations,
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
     */
    public function removeRoleAction(Workspace $workspace, Role $role)
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
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roleEdit.html.twig")
     */
    public function editRoleFormAction(Role $role, Workspace $workspace)
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
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:roleEdit.html.twig")
     * @EXT\Method("POST")
     */
    public function editRoleAction(Role $role, Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(
            FormFactory::TYPE_ROLE_TRANSLATION,
            array('wsGuid' => $workspace->getGuid()),
            $role
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->roleManager->edit($role);
            $route = $this->router->generate(
                'claro_workspace_roles',
                array('workspace' => $workspace->getId())
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
    public function removeUserFromRoleAction(User $user, Role $role, Workspace $workspace)
    {
        $this->checkAccess($workspace);

        try {
            $this->roleManager->dissociateWorkspaceRole($user, $workspace, $role);
        } catch (LastManagerDeleteException $e) {
            return new JsonResponse(
                array(
                    'message' => $this->translator->trans('last_manager_error_message', array(), 'platform')
                ),
                500
            );
        }

        return new JsonResponse(array(), 200);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/users/unregistered/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_unregistered_user_list",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/{workspace}/users/unregistered/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_unregistered_user_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:unregisteredUsers.html.twig")
     */
    public function unregisteredUserListAction($page, $search, Workspace $workspace, $max, $order, $direction)
    {
        $this->checkAccess($workspace);
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $preferences = $this->facetManager->getVisiblePublicPreference();

        $pager = $search === '' ?
            $this->userManager->getAllUsers($page, $max, $order, $direction) :
            $this->userManager->getUsersByName($search, $page, $max, $order, $direction);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
            'showMail' => $preferences['mail']
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/groups/unregistered/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_unregistered_group_list",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/{workspace}/groups/unregistered/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_unregistered_group_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"= "ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\Group",
     *     options={"orderable"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:unregisteredGroups.html.twig")
     */
    public function unregisteredGroupListAction($page, $search, Workspace $workspace, $max, $order, $direction)
    {
        $this->checkAccess($workspace);
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

        $pager = ($search === '') ?
            $this->groupManager->getGroups($page, $max, $order, $direction) :
            $this->groupManager->getGroupsByName($search, $page, $max, $order, $direction);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => $max,
            'order' => $order,
            'direction' => $direction
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/users/unregistered/from/group/{group}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_workspace_unregistered_users_from_group_list",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="id", "order"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:unregisteredUsersFromGroup.html.twig")
     */
    public function unregisteredUsersFromGroupListAction(
        Group $group,
        Workspace $workspace,
        $page = 1,
        $max = 50,
        $orderedBy = 'id',
        $order = 'ASC',
        $search = ''
    )
    {
        $this->checkAccess($workspace);
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $preferences = $this->facetManager->getVisiblePublicPreference();

        $pager = $search === '' ?
            $this->userManager->getUsersByGroup($group, $page, $max, $orderedBy, $order) :
            $this->userManager->getUsersByNameAndGroup($search, $group, $page, $max, $orderedBy, $order);

        return array(
            'group' => $group,
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'showMail' => $preferences['mail']
        );
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
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "name"="roleIds"}
     * )
     *
     * @param array $users
     * @param array $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return Response
     */
    public function addUsersToRolesAction(array $users, array $roles, Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $this->roleManager->associateRolesToSubjects($users, $roles, true);
        //$listCptNodes = $this->cptManager->getCompetenceByWorkspace($workspace);
        //$this->cptManager->subscribeUserToCompetences($users, $listCptNodes);

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
    public function removeGroupFromRoleAction(Group $group, Role $role, Workspace $workspace)
    {
        $this->checkAccess($workspace);

        try {
            $this->roleManager->dissociateWorkspaceRole($group, $workspace, $role);
        } catch (LastManagerDeleteException $e) {
            return new JsonResponse(
                array(
                    'message' => $this->translator->trans('last_manager_error_message', array(), 'platform')
                ),
                500
            );
        }

        return new JsonResponse(array(), 200);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/add/role/group",
     *     name="claro_workspace_add_roles_to_groups",
     *     options={"expose"=true}
     * )
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
    public function addGroupsToRolesAction(array $groups, array $roles, Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $this->roleManager->associateRolesToSubjects($groups, $roles, true);

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/users/registered/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_registered_user_list",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/{workspace}/users/registered/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_registered_user_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:workspaceUsers.html.twig")
     */
    public function usersListAction(Workspace $workspace, $page, $search, $max, $order, $direction = 'ASC')
    {
        $this->checkAccess($workspace);
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $currentUser = $this->security->getToken()->getUser();
        $preferences = $this->facetManager->getVisiblePublicPreference();

        $pager = $search === '' ?
            $this->userManager->getByRolesIncludingGroups($wsRoles, $page, $max, $order, $direction) :
            $this->userManager->getByRolesAndNameIncludingGroups($wsRoles, $search, $page, $max, $order, $direction);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
            'currentUser' => $currentUser,
            'showMail' => $preferences['mail']
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/groups/registered/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_registered_group_list",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/{workspace}/groups/registered/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_registered_group_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "isRequired"=false, "name"="roleIds"}
     * )
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\Group",
     *     options={"orderable"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:workspaceGroups.html.twig")
     */
    public function groupsListAction(Workspace $workspace, $page, $search, $max, $order, $direction)
    {
        $this->checkAccess($workspace);
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

        $pager = ($search === '') ?
            $pager = $this->groupManager->getGroupsByRoles($wsRoles, $page, $max, $order, $direction) :
            $pager = $this->groupManager->getGroupsByRolesAndName($wsRoles, $search, $page, $max, $order, $direction);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => $max,
            'order' => $order,
            'direction' => $direction
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/groups/{group}/page/{page}/search/{search}/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_users_of_group_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/{workspace}/groups/{group}/page/{page}/{max}/order/{order}/direction/{direction}",
     *     name="claro_workspace_users_of_group",
     *     defaults={"page"=1, "max"=50, "search"="", "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:usersOfGroup.html.twig")
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     */
    public function usersOfGroupAction(
        Workspace $workspace,
        Group $group,
        $page,
        $search,
        $max,
        $order,
        $direction
    )
    {
        $this->checkAccess($workspace);

        $preferences = $this->facetManager->getVisiblePublicPreference();
        $pager = ($search === '') ?
            $this->userManager->getUsersByGroup($group, $page, $max, $order, $direction) :
            $this->userManager->getUsersByNameAndGroup($search, $group, $page, $max, $order, $direction);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'group' => $group,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
            'showMail' => $preferences['mail']
        );
    }

    /**
     * @EXT\Route(
     *     "/users/usernames",
     *     name="claro_usernames_from_users",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     */
    public function retrieveUsernamesFromUsersAction(array $users)
    {
        $usernames = '';

        foreach ($users as $user) {
            $usernames .= $user->getUsername() . ';';
        }

        return new Response($usernames, 200);
    }

    /**
     * @EXT\Route(
     *     "/groups/names",
     *     name="claro_names_from_groups",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "groupIds"}
     * )
     */
    public function retrieveNamesFromGroupsAction(array $groups)
    {
        $names = '';

        foreach ($groups as $group) {
            $names .= '{' . $group->getName() . '};';
        }

        return new Response($names, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspaces/names",
     *     name="claro_names_from_workspaces",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     */
    public function retrieveNamesFromWorkspacesAction(array $workspaces)
    {
        $names = '';

        foreach ($workspaces as $workspace) {
            $names .= '[' . $workspace->getCode() . '];';
        }

        return new Response($names, 200);
    }

    /**
     * @EXT\Route("/users/pending/{workspace}",
     *     name="claro_users_pending"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\roles:workspaceusersPending.html.twig")
     */
    public function pendingUsersAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);

        return array(
            'workspace' => $workspace,
            'pager' => $this->wksUqmanager->getAll($workspace)
        );
    }

    /**
     * @EXT\Route("/users/pending/validation/{workspace}/{wksqueue}",
     *     name="claro_users_pending_validation"
     * )
     */
    public function pendingUsersValidationAction(
        WorkspaceRegistrationQueue $wksqueue,
        Workspace $workspace
    )
    {
        $this->checkWorkspaceManagerAccess($workspace);
        $this->wksUqmanager->validateRegistration($wksqueue, $workspace);
        $route = $this->router->generate(
            'claro_users_pending',
            array('workspace' => $workspace->getId())
        );

        return new RedirectResponse($route);
    }

    /**
     * @EXT\Route("/users/pending/decline/{workspace}/{wksqueue}",
     *     name="claro_users_pending_decline"
     * )
     */
    public function pendingUsersDeclineAction(
        WorkspaceRegistrationQueue $wksqueue,
        Workspace $workspace
    )
    {
        $this->checkWorkspaceManagerAccess($workspace);
        $this->wksUqmanager->removeRegistrationQueue($wksqueue);
        $route = $this->router->generate(
            'claro_users_pending',
            array('workspace' => $workspace->getId())
        );

        return new RedirectResponse($route);
    }

    private function checkAccess(Workspace $workspace)
    {
        if (!$this->security->isGranted('users', $workspace)) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceManagerAccess(Workspace $workspace)
    {
        $role = $this->roleManager->getManagerRole($workspace);

        if (is_null($role) || !$this->security->isGranted($role->getName())) {
            throw new AccessDeniedException();
        }
    }
}

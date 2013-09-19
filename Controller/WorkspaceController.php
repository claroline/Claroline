<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Security\TokenUpdater;
use Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This controller is able to:
 * - list/create/delete/show workspaces.
 * - return some users/groups list (ie: (un)registered users to a workspace).
 * - add/delete users/groups to a workspace.
 */
class WorkspaceController extends Controller
{
    private $homeTabManager;
    private $workspaceManager;
    private $resourceManager;
    private $roleManager;
    private $userManager;
    private $tagManager;
    private $toolManager;
    private $eventDispatcher;
    private $security;
    private $router;
    private $utils;
    private $formFactory;
    private $tokenUpdater;

    /**
     * @DI\InjectParams({
     *     "homeTabManager"     = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "tagManager"         = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router"),
     *     "utils"              = @DI\Inject("claroline.security.utilities"),
     *     "formFactory"        = @DI\Inject("claroline.form.factory"),
     *     "tokenUpdater"       = @DI\Inject("claroline.security.token_updater")
     * })
     */
    public function __construct(
        HomeTabManager $homeTabManager,
        WorkspaceManager $workspaceManager,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        UserManager $userManager,
        WorkspaceTagManager $tagManager,
        ToolManager $toolManager,
        StrictDispatcher $eventDispatcher,
        SecurityContextInterface $security,
        UrlGeneratorInterface $router,
        Utilities $utils,
        FormFactory $formFactory,
        TokenUpdater $tokenUpdater
    )
    {
        $this->homeTabManager = $homeTabManager;
        $this->workspaceManager = $workspaceManager;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->tagManager = $tagManager;
        $this->toolManager = $toolManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->router = $router;
        $this->utils = $utils;
        $this->formFactory = $formFactory;
        $this->tokenUpdater = $tokenUpdater;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_workspace_list",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the workspace list page with its claroline layout.
     *
     * @return Response
     */
    public function listAction()
    {
        $datas = $this->tagManager->getDatasForWorkspaceList(false);

        return array(
            'workspaces' => $datas['workspaces'],
            'tags' => $datas['tags'],
            'tagWorkspaces' => $datas['tagWorkspaces'],
            'hierarchy' => $datas['hierarchy'],
            'rootTags' => $datas['rootTags'],
            'displayable' => $datas['displayable']
        );
    }

    /**
     * @EXT\Route(
     *     "/user",
     *     name="claro_workspace_by_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the registered workspace list for a user.
     *
     * @return Response
     */
    public function listWorkspacesByUserAction()
    {
        $this->assertIsGranted('ROLE_USER');
        $token = $this->security->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);

        $datas = $this->tagManager->getDatasForWorkspaceListByUser($user, $roles);

        return array(
            'user' => $user,
            'workspaces' => $datas['workspaces'],
            'tags' => $datas['tags'],
            'tagWorkspaces' => $datas['tagWorkspaces'],
            'hierarchy' => $datas['hierarchy'],
            'rootTags' => $datas['rootTags'],
            'displayable' => $datas['displayable']
        );
    }

    /**
     * @EXT\Route(
     *     "/displayable/selfregistration",
     *     name="claro_list_workspaces_with_self_registration",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the displayable workspace list.
     *
     * @return Response
     */
    public function listWorkspacesWithSelfRegistrationAction()
    {
        $this->assertIsGranted('ROLE_USER');
        $token = $this->security->getToken();
        $user = $token->getUser();
        $datas = $this->tagManager->getDatasForSelfRegistrationWorkspaceList();

        return array(
            'user' => $user,
            'workspaces' => $datas['workspaces'],
            'tags' => $datas['tags'],
            'tagWorkspaces' => $datas['tagWorkspaces'],
            'hierarchy' => $datas['hierarchy'],
            'rootTags' => $datas['rootTags'],
            'displayable' => $datas['displayable']
        );
    }

    /**
     * @EXT\Route(
     *     "/displayable/selfunregistration/page/{page}",
     *     name="claro_list_workspaces_with_self_unregistration",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Renders the displayable workspace list with self-unregistration.
     *
     * @return Response
     */
    public function listWorkspacesWithSelfUnregistrationAction(User $currentUser, $page = 1)
    {
        $token = $this->security->getToken();
        $roles = $this->utils->getRoles($token);

        $workspacesPager = $this->workspaceManager
            ->getWorkspacesWithSelfUnregistrationByRoles($roles, $page);

        return array(
            'user' => $currentUser,
            'workspaces' => $workspacesPager
        );
    }

    /**
     * @EXT\Route(
     *     "/new/form",
     *     name="claro_workspace_creation_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the workspace creation form.
     *
     * @return Response
     */
    public function creationFormAction()
    {
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE);

        return array('form' => $form->createView());
    }

    /**
     * Creates a workspace from a form sent by POST.
     *
     * @EXT\Route(
     *     "/",
     *     name="claro_workspace_create"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Workspace:creationForm.html.twig")
     *
     * @return RedirectResponse
     */
    public function createAction()
    {
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE);
        $form->handleRequest($this->getRequest());

        $templateDir = $this->container->getParameter('claroline.param.templates_directory');
        $ds = DIRECTORY_SEPARATOR;

        if ($form->isValid()) {
            $type = $form->get('type')->getData() == 'simple' ?
                Configuration::TYPE_SIMPLE :
                Configuration::TYPE_AGGREGATOR;
            $config = Configuration::fromTemplate($templateDir.$ds.$form->get('template')->getData()->getHash());
            $config->setWorkspaceType($type);
            $config->setWorkspaceName($form->get('name')->getData());
            $config->setWorkspaceCode($form->get('code')->getData());
            $config->setDisplayable($form->get('displayable')->getData());
            $config->setSelfRegistration($form->get('selfRegistration')->getData());
            $config->setSelfUnregistration($form->get('selfUnregistration')->getData());
            $user = $this->security->getToken()->getUser();
            $this->workspaceManager->create($config, $user);
            $this->tokenUpdater->update($this->security->getToken());
            $route = $this->router->generate('claro_workspace_list');

            return new RedirectResponse($route);
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}",
     *     name="claro_workspace_delete",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function deleteAction(AbstractWorkspace $workspace)
    {
        $this->assertIsGranted('DELETE', $workspace);
        $this->eventDispatcher->dispatch(
            'log',
            'Log\LogWorkspaceDelete',
            array($workspace)
        );
        $this->workspaceManager->deleteWorkspace($workspace);

        $this->tokenUpdater->cancelUsurpation($this->security->getToken());

        return new Response('success', 204);
    }

    /**
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Renders the left tool bar. Not routed.
     *
     * @param $_workspace
     *
     * @return Response
     */
    public function renderToolListAction(AbstractWorkspace $workspace, $_breadcrumbs)
    {
        if ($_breadcrumbs != null) {
            //for manager.js, id = 0 => "no root".
            if ($_breadcrumbs[0] != 0) {
                $rootId = $_breadcrumbs[0];
            } else {
                $rootId = $_breadcrumbs[1];
            }
            $workspace = $this->resourceManager->getNode($rootId)->getWorkspace();
        }

        if (!$this->security->isGranted('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }

        $currentRoles = $this->utils->getRoles($this->security->getToken());

        $orderedTools = $this->toolManager->getOrderedToolsByWorkspaceAndRoles($workspace, $currentRoles);

        return array(
            'orderedTools' => $orderedTools,
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open/tool/{toolName}",
     *     name="claro_workspace_open_tool",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Opens a tool.
     *
     * @param type $toolName
     * @param type $workspaceId
     *
     * @return Response
     */
    public function openToolAction($toolName, AbstractWorkspace $workspace)
    {
        $this->assertIsGranted($toolName, $workspace);
        $event = $this->eventDispatcher->dispatch(
            'open_tool_workspace_' . $toolName,
            'DisplayTool',
            array($workspace)
        );
        $this->eventDispatcher->dispatch(
            'log',
            'Log\LogWorkspaceToolRead',
            array($workspace, $toolName)
        );

        if ($toolName === 'resource_manager') {
            $this->get('session')->set('isDesktop', false);
        }

        return new Response($event->getContent());
    }

    /**
     * Routing is not needed.
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:widgets.html.twig")
     *
     * Display registered widgets.
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo Reduce the number of sql queries for this action (-> dql)
     */
    public function widgetsAction(AbstractWorkspace $workspace, $homeTabId)
    {
        // No right checking is done : security is delegated to each widget renderer.
        // The routing is now removed. Checking doesn't need to be done.
        $configs = $this->get('claroline.widget.manager')
            ->generateWorkspaceDisplayConfig($workspace->getId());

        if ($this->security->getToken()->getUser() !== 'anon.') {
            $rightToConfigure = $this->security->isGranted('parameters', $workspace);
        } else {
            $rightToConfigure = false;
        }

        $widgets = array();

        $homeTab = $this->homeTabManager->getHomeTabById($homeTabId);

        if (!is_null($homeTab) &&
            $this->homeTabManager->checkHomeTabVisibilityByWorkspace($homeTab, $workspace)) {

            $configs = $this->homeTabManager->getWidgetConfigsByWorkspace($homeTab, $workspace);

            if ($this->security->getToken()->getUser() !== 'anon.') {
                $rightToConfigure = $this->security->isGranted('parameters', $workspace);
            } else {
                $rightToConfigure = false;
            }

            foreach ($configs as $config) {
                if ($config->isVisible()) {
                    $eventName = "widget_{$config->getWidget()->getName()}_workspace";
                    $event = $this->eventDispatcher
                        ->dispatch($eventName, 'DisplayWidget', array($workspace));

                    if ($event->hasContent()) {
                        $widget['id'] = $config->getWidget()->getId();
                        if ($event->hasTitle()) {
                            $widget['title'] = $event->getTitle();
                        } else {
                            $widget['title'] = strtolower($config->getWidget()->getName());
                        }
                        $widget['content'] = $event->getContent();
                        $widget['configurable'] = (
                            $rightToConfigure
                            and $config->isLocked() !== true
                            and $config->getWidget()->isConfigurable()
                        );

                        $widgets[] = $widget;
                    }
                }
            }
        }

        return array(
            'widgets' => $widgets,
            'isDesktop' => false,
            'workspaceId' => $workspace->getId()
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open",
     *     name="claro_workspace_open"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Open the first tool of a workspace.
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openAction(AbstractWorkspace $workspace)
    {
        if ('anon.' != $this->security->getToken()->getUser()) {
            $roles = $this->roleManager->getRolesByWorkspace($workspace);
            $foundRoles = array();

            foreach ($roles as $wsRole) {
                foreach ($this->security->getToken()->getUser()->getRoles() as $userRole) {
                    if ($userRole == $wsRole->getName()) {
                        $foundRoles[] = $userRole;
                    }
                }
            }

            $isAdmin = $this->security->getToken()->getUser()->hasRole('ROLE_ADMIN');

            if (count($foundRoles) === 0 && !$isAdmin) {
                throw new AccessDeniedException('No role found in that workspace');
            }

            if ($isAdmin) {
                //admin always open the home.
                $openedTool = array($this->toolManager->getOneToolByName('home'));
            } else {
                $openedTool = $this->toolManager->getDisplayedByRolesAndWorkspace(
                    $foundRoles,
                    $workspace
                );
            }

        } else {
            $foundRole = 'ROLE_ANONYMOUS';
            $openedTool = $this->toolManager->getDisplayedByRolesAndWorkspace(
                array('ROLE_ANONYMOUS'),
                $workspace
            );
        }

        if ($openedTool == null) {
            throw new AccessDeniedException("No tool found for role {$foundRole}");
        }

        $route = $this->router->generate(
            'claro_workspace_open_tool',
            array('workspaceId' => $workspace->getId(), 'toolName' => $openedTool[0]->getName())
        );

        return new RedirectResponse($route);
    }

    /**
     * @EXT\Route(
     *     "/search/role/code/{code}",
     *     name="claro_resource_find_role_by_code",
     *     options={"expose"=true}
     * )
     */
    public function findRoleByWorkspaceCodeAction($code)
    {
        $roles = $this->roleManager->getRolesBySearchOnWorkspaceAndTag($code);
        $arWorkspace = array();

        foreach ($roles as $role) {
            $arWorkspace[$role->getWorkspace()->getCode()][$role->getName()] = array(
                'name' => $role->getName(),
                'translation_key' => $role->getTranslationKey(),
                'id' => $role->getId(),
                'workspace' => $role->getWorkspace()->getName()
            );
        }

        return new JsonResponse($arWorkspace);
    }

    /**
     * @todo Security context verification.
     * @EXT\Route(
     *     "/{workspaceId}/add/user/{userId}",
     *     name="claro_workspace_add_user",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method({"POST", "GET"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     *
     * Adds a user to a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param User              $user
     *
     * @return Response
     */
    public function addUserAction(AbstractWorkspace $workspace, User $user)
    {
        $role = $this->roleManager->getCollaboratorRole($workspace);

        $userRoles = $this->roleManager->getWorkspaceRolesForUser($user, $workspace);

        if (count($userRoles) === 0) {
            $this->roleManager->associateRole($user, $role);
            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogRoleSubscribe',
                array($role, $user)
            );
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->security->setToken($token);

        return new JsonResponse($this->userManager->convertUsersToArray(array($user)));
    }

    /** @EXT\Route(
     *     "/list/tag/{workspaceTagId}/page/{page}",
     *     name="claro_workspace_list_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders the workspace list associate to a tag in a pager.
     *
     * @return Response
     */
    public function workspaceListByTagPagerAction(WorkspaceTag $workspaceTag, $page = 1)
    {
        $relations = $this->tagManager->getPagerRelationByTag($workspaceTag, $page);

        return array(
            'workspaceTagId' => $workspaceTag->getId(),
            'relations' => $relations
        );
    }

    /** @EXT\Route(
     *     "/list/self_reg/tag/{workspaceTagId}/page/{page}",
     *     name="claro_workspace_list_with_self_reg_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders the workspace list with self-registration associate to a tag in a pager.
     *
     * @return Response
     */
    public function workspaceListWithSelfRegByTagPagerAction(
        WorkspaceTag $workspaceTag,
        $page = 1
    )
    {
        $relations = $this->tagManager
            ->getPagerRelationByTagForSelfReg($workspaceTag, $page);

        return array(
            'workspaceTagId' => $workspaceTag->getId(),
            'relations' => $relations
        );
    }

    /**
     * @EXT\Route(
     *     "/list/workspaces/page/{page}",
     *     name="claro_all_workspaces_list_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the workspace list in a pager.
     *
     * @return Response
     */
    public function workspaceCompleteListPagerAction($page = 1)
    {
        $workspaces = $this->tagManager->getPagerAllWorkspaces($page);

        return array('workspaces' => $workspaces);
    }

    /**
     * @EXT\Route(
     *     "/list/workspaces/self_reg/page/{page}",
     *     name="claro_all_workspaces_list_with_self_reg_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the workspace list with self-registration in a pager.
     *
     * @return Response
     */
    public function workspaceCompleteListWithSelfRegPagerAction($page = 1)
    {
        $workspaces = $this->tagManager->getPagerAllWorkspacesWithSelfReg($page);

        return array('workspaces' => $workspaces);
    }

    /**
     * @todo Security context verification.
     * @EXT\Route(
     *     "/{workspaceId}/remove/user/{userId}",
     *     name="claro_workspace_delete_user",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method({"DELETE", "GET"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     *
     * Removes an user from a workspace.
     *
     * @param AbstractWorkspace $workspace
     * @param User              $userId
     *
     * @return Response
     */
    public function removeUserAction(AbstractWorkspace $workspace, User $user)
    {
        try {
            $roles = $this->roleManager->getRolesByWorkspace($workspace);
            $this->roleManager->checkWorkspaceRoleEditionIsValid(array($user), $workspace, $roles);

            foreach ($roles as $role) {
                if ($user->hasRole($role->getName())) {
                    $this->roleManager->dissociateRole($user, $role);
                    $this->eventDispatcher->dispatch(
                        'log',
                        'Log\LogRoleUnsubscribe',
                        array($role, $user, $workspace)
                    );
                }
            }
            $this->tagManager->deleteAllRelationsFromWorkspaceAndUser($workspace, $user);

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->security->setToken($token);

            return new Response('success', 204);
        }
        catch (LastManagerDeleteException $e) {
            return new Response(
                'cannot_delete_unique_manager',
                200,
                array('XXX-Claroline-delete-last-manager')
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/registration/list/tag/{workspaceTagId}/page/{page}",
     *     name="claro_workspace_list_registration_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "workspaceTag",
     *      class="ClarolineCoreBundle:Workspace\WorkspaceTag",
     *      options={"id" = "workspaceTagId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Renders the workspace list associate to a tag in a pager for registation.
     *
     * @return Response
     */
    public function workspaceListByTagRegistrationPagerAction(WorkspaceTag $workspaceTag, $page = 1)
    {
        $relations = $this->tagManager->getPagerRelationByTag($workspaceTag, $page);

        return array(
            'workspaceTagId' => $workspaceTag->getId(),
            'relations' => $relations
        );
    }

    /**
     * @EXT\Route(
     *     "/registration/list/workspaces/page/{page}",
     *     name="claro_all_workspaces_list_registration_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the workspace list in a pager for registration.
     *
     * @return Response
     */
    public function workspaceCompleteListRegistrationPagerAction($page = 1)
    {
        $workspaces = $this->tagManager->getPagerAllWorkspaces($page);

        return array('workspaces' => $workspaces);
    }

    /**
     * @EXT\Route(
     *     "/registration/list/workspaces/search/{search}/page/{page}",
     *     name="claro_workspaces_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the workspace list in a pager for registration.
     *
     * @return Response
     */
    public function workspaceSearchedListRegistrationPagerAction($search, $page = 1)
    {
        $pager = $this->workspaceManager->getDisplayableWorkspacesBySearchPager($search, $page);

        return array('workspaces' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open/tool/home/tab/{tabId}",
     *     name="claro_display_workspace_home_tabs"
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabs.html.twig")
     *
     * Displays the workspace home tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayWorkspaceHomeTabsAction(AbstractWorkspace $workspace, $tabId)
    {
        $adminHomeTabConfigsTemp = $this->homeTabManager
            ->generateAdminHomeTabConfigsByWorkspace($workspace);
        $adminHomeTabConfigs = $this->homeTabManager
            ->filterVisibleHomeTabConfigs($adminHomeTabConfigsTemp);
        $workspaceHomeTabConfigs = $this->homeTabManager
            ->getVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);

        return array(
            'workspace' => $workspace,
            'adminHomeTabConfigs' => $adminHomeTabConfigs,
            'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
            'tabId' => $tabId
        );
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->security->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }
}

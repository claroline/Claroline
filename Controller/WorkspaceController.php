<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
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
use Claroline\CoreBundle\Manager\WidgetManager;
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
    private $widgetManager;
    private $request;
    private $templateDir;
    private $translator;
    private $session;

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
     *     "tokenUpdater"       = @DI\Inject("claroline.security.token_updater"),
     *     "widgetManager"      = @DI\Inject("claroline.manager.widget_manager"),
     *     "request"            = @DI\Inject("request"),
     *     "templateDir"        = @DI\Inject("%claroline.param.templates_directory%"),
     *     "translator"         = @DI\Inject("translator"),
     *     "session"            = @DI\Inject("session")
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
        TokenUpdater $tokenUpdater,
        WidgetManager $widgetManager,
        Request $request,
        $templateDir,
        TranslatorInterface $translator,
        SessionInterface $session
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
        $this->widgetManager = $widgetManager;
        $this->request = $request;
        $this->templateDir = $templateDir;
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_workspace_list",
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = false})
     * @EXT\Template()
     *
     * Renders the workspace list page with its claroline layout.
     *
     * @return Response
     */
    public function listAction($currentUser)
    {
        $user = $currentUser instanceof User ? $currentUser : null;

        return $this->tagManager->getDatasForWorkspaceList(false, $user);
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

        $data = $this->tagManager->getDatasForWorkspaceListByUser($user, $roles);
        $favouriteWorkspaces = $this->workspaceManager
            ->getFavouriteWorkspacesByUser($user);
        $favourites = array();

        foreach ($data['workspaces'] as $workspace) {
            if (isset($favouriteWorkspaces[$workspace->getId()])) {
                $favourites[$workspace->getId()] = $workspace;
            }
        }

        $data['user'] = $user;
        $data['favourites'] = $favourites;

        return $data;
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
        $user = $this->security->getToken()->getUser();

        return $this->tagManager->getDatasForSelfRegistrationWorkspaceList($user);
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
     * @param \Claroline\CoreBundle\Entity\User $currentUser
     * @param int $page
     *
     * @return array
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
     * @return array
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
     * @return RedirectResponse | array
     */
    public function createAction()
    {
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE);
        $form->handleRequest($this->request);
        $ds = DIRECTORY_SEPARATOR;

        if ($form->isValid()) {
            $type = $form->get('type')->getData() == 'simple' ?
                Configuration::TYPE_SIMPLE :
                Configuration::TYPE_AGGREGATOR;
            $config = Configuration::fromTemplate(
                $this->templateDir . $ds . $form->get('template')->getData()->getHash()
            );
            $config->setWorkspaceType($type);
            $config->setWorkspaceName($form->get('name')->getData());
            $config->setWorkspaceCode($form->get('code')->getData());
            $config->setDisplayable($form->get('displayable')->getData());
            $config->setSelfRegistration($form->get('selfRegistration')->getData());
            $config->setSelfUnregistration($form->get('selfUnregistration')->getData());            
            $config->setWorkspaceDescription($form->get('description')->getData());
            
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
     * @param AbstractWorkspace $workspace
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

        $sessionFlashBag = $this->session->getFlashBag();
        $sessionFlashBag->add('success', $this->translator->trans(
            'workspace_delete_success_message', 
            array('%workspaceName%' => $workspace->getName()), 
            'platform'
            )
        );

        return new Response('success', 204);
    }

    /**
     * @EXT\Template()
     *
     * Renders the left tool bar. Not routed.
     *
     * @param AbstractWorkspace $workspace
     * @param integer[] $_breadcrumbs
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return array
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

        $currentRoles = $this->utils->getRoles($this->security->getToken());
        //do I need to display every tools.
        $hasManagerAccess = false;
        $managerRole = $this->roleManager->getManagerRole($workspace);

        foreach ($currentRoles as $role) {
            if ($managerRole->getName() === $role) {
                $hasManagerAccess = true;
            }
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $hasManagerAccess = true;
        }

        //if manager or admin, show every tools
        if ($hasManagerAccess) {
            $orderedTools = $this->toolManager->getOrderedToolsByWorkspace($workspace);
        //otherwise only shows the relevant tools
        } else {
            $orderedTools = $this->toolManager->getOrderedToolsByWorkspaceAndRoles($workspace, $currentRoles);
        }

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
     * @param string            $toolName
     * @param AbstractWorkspace $workspace
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

        $this->eventDispatcher->dispatch(
            'log',
            'Log\LogWorkspaceEnter',
            array($workspace)
        );

        if ($toolName === 'resource_manager') {
            $this->session->set('isDesktop', false);
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
     * @EXT\Template("ClarolineCoreBundle:Widget:widgetsWithoutConfig.html.twig")
     *
     * Display visible registered widgets.
     *
     * @param AbstractWorkspace $workspace
     * @param integer           $homeTabId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo Reduce the number of sql queries for this action (-> dql)
     */
    public function widgetsWithoutConfigAction(
        AbstractWorkspace $workspace,
        $homeTabId
    )
    {
        $widgets = array();

        $homeTab = $this->homeTabManager->getHomeTabById($homeTabId);

        if (is_null($homeTab)) {
            $isVisibleHomeTab = false;
        } else {
            $isVisibleHomeTab = $this->homeTabManager
                ->checkHomeTabVisibilityByWorkspace($homeTab, $workspace);
        }

        if ($isVisibleHomeTab) {

            $widgetHomeTabConfigs = $this->homeTabManager
                ->getVisibleWidgetConfigsByWorkspace($homeTab, $workspace);

            foreach ($widgetHomeTabConfigs as $widgetHomeTabConfig) {
                $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();

                $event = $this->eventDispatcher->dispatch(
                    "widget_{$widgetInstance->getWidget()->getName()}",
                    'DisplayWidget',
                    array($widgetInstance)
                );

                $widget['config'] = $widgetHomeTabConfig;
                $widget['content'] = $event->getContent();
                $widgets[] = $widget;
            }
        }

        return array('widgetsDatas' => $widgets);
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
     * @EXT\Template("ClarolineCoreBundle:Widget:widgetsWithConfig.html.twig")
     *
     * Display registered widgets.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $homeTabId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo Reduce the number of sql queries for this action (-> dql)
     */
    public function widgetsWithConfigAction(AbstractWorkspace $workspace, $homeTabId)
    {
        $this->checkWorkspaceManagerAccess($workspace);

        if ($this->security->getToken()->getUser() !== 'anon.') {
            $rightToConfigure = $this->security->isGranted('parameters', $workspace);
        } else {
            $rightToConfigure = false;
        }

        $widgets = array();
        $lastWidgetOrder = 1;
        $homeTab = $this->homeTabManager
            ->getHomeTabByIdAndWorkspace($homeTabId, $workspace);
        $isVisibleHomeTab = is_null($homeTab) ? false: true;

        if ($isVisibleHomeTab) {

            $widgetHomeTabConfigs = $this->homeTabManager
                ->getWidgetConfigsByWorkspace($homeTab, $workspace);

            if (count($widgetHomeTabConfigs) > 0) {
                $lastWidgetOrder = count($widgetHomeTabConfigs);
            }

            foreach ($widgetHomeTabConfigs as $widgetHomeTabConfig) {
                $widgetInstance = $widgetHomeTabConfig->getWidgetInstance();

                $event = $this->eventDispatcher->dispatch(
                    "widget_{$widgetInstance->getWidget()->getName()}",
                    'DisplayWidget',
                    array($widgetInstance)
                );

                $widget['config'] = $widgetHomeTabConfig;
                $widget['content'] = $event->getContent();
                $widget['configurable'] = $rightToConfigure
                    && $widgetInstance->getWidget()->isConfigurable();
                $widgets[] = $widget;
            }
        }

        return array(
            'widgetsDatas' => $widgets,
            'isDesktop' => false,
            'workspaceId' => $workspace->getId(),
            'isVisibleHomeTab' => $isVisibleHomeTab,
            'isLockedHomeTab' => false,
            'lastWidgetOrder' => $lastWidgetOrder
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
     * @param AbstractWorkspace $workspace
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function openAction(AbstractWorkspace $workspace)
    {
        if ($this->security->isGranted('OPEN', $workspace)) {

            //get every roles of the user in the current $workspace
            $foundRoles = array();
            $roles = $this->roleManager->getRolesByWorkspace($workspace);

            foreach ($roles as $wsRole) {
                foreach ($this->security->getToken()->getRoles() as $userRole) {
                    if ($userRole->getRole() === $wsRole->getName()) {
                        $foundRoles[] = $wsRole;
                    }
                }
            }

            if (count($foundRoles) > 0) {
                $openableTools = $this->toolManager->getDisplayedByRolesAndWorkspace($foundRoles, $workspace);

                if (count($openableTools) > 0) {
                    $openedTool = $openableTools[0];
                } else {
                    $openedTool = $this->toolManager->getOneToolByName('home');
                }

            } else {
                //this should be the 1st tool of the workspace tool list.
                //@todo see the above comment
                $openedTool = $this->toolManager->getOneToolByName('home');
            }

            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('workspaceId' => $workspace->getId(), 'toolName' => $openedTool->getName())
            );

            return new RedirectResponse($route);
        }

        throw new AccessDeniedException("Access denied");
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
        $this->workspaceManager->addUserAction($workspace, $user);

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
     * @param \Claroline\CoreBundle\Entity\Workspace\WorkspaceTag $workspaceTag
     * @param integer $page
     *
     * @return array
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
     * @param \Claroline\CoreBundle\Entity\Workspace\WorkspaceTag $workspaceTag
     * @param integer $page
     *
     * @return array
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
     * @EXT\Template()
     *
     * @param integer $page
     *
     * @return array
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
     * @param int $page
     *
     * @return array
     */
    public function workspaceCompleteListWithSelfRegPagerAction($page = 1)
    {
        $this->assertIsGranted('ROLE_USER');
        $workspaces = $this->tagManager->getPagerAllWorkspacesWithSelfReg(
            $this->security->getToken()->getUser(),
            $page
        );

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
     * @param AbstractWorkspace                 $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
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
        } catch (LastManagerDeleteException $e) {
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
     * @param \Claroline\CoreBundle\Entity\Workspace\WorkspaceTag $workspaceTag
     * @param int                                                 $page
     *
     * @return array
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
     * @param int $page
     *
     * @return array
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
     * @param string $search
     * @param int    $page
     *
     * @return array
     */
    public function workspaceSearchedListRegistrationPagerAction($search, $page = 1)
    {
        $pager = $this->workspaceManager->getDisplayableWorkspacesBySearchPager($search, $page);

        return array('workspaces' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open/tool/no_config/home/tab/{tabId}",
     *     name="claro_display_workspace_home_tabs_without_config",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabsWithoutConfig.html.twig")
     *
     * Displays the workspace home tab without config.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $tabId
     *
     * @return array
     */
    public function displayWorkspaceHomeTabsActionWithoutConfig(
        AbstractWorkspace $workspace,
        $tabId
    )
    {
        $workspaceHomeTabConfigs = $this->homeTabManager
            ->getVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);
        $homeTabId = intval($tabId);
        $firstElement = true;

        if ($homeTabId !== -1) {
            foreach ($workspaceHomeTabConfigs as $workspaceHomeTabConfig) {
                if ($homeTabId === $workspaceHomeTabConfig->getHomeTab()->getId()) {
                    $firstElement = false;
                    break;
                }
            }
        }

        if ($firstElement) {
            $firstHomeTabConfig = reset($workspaceHomeTabConfigs);

            if ($firstHomeTabConfig) {
                $homeTabId = $firstHomeTabConfig->getHomeTab()->getId();
            }
        }

        return array(
            'workspace' => $workspace,
            'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
            'tabId' => $homeTabId
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open/tool/config/home/tab/{tabId}",
     *     name="claro_display_workspace_home_tabs_with_config",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabsWithConfig.html.twig")
     *
     * Displays the workspace home tab.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param integer $tabId
     *
     * @return array
     */
    public function displayWorkspaceHomeTabsActionWithConfig(
        AbstractWorkspace $workspace,
        $tabId
    )
    {
        $this->checkWorkspaceManagerAccess($workspace);

        $workspaceHomeTabConfigs = $this->homeTabManager
            ->getWorkspaceHomeTabConfigsByWorkspace($workspace);
        $homeTabId = intval($tabId);
        $firstElement = true;

        if ($homeTabId === 0) {
            $firstElement = false;
            $lastHomeTabConfig = end($workspaceHomeTabConfigs);

            if ($lastHomeTabConfig) {
                $homeTabId = $lastHomeTabConfig->getHomeTab()->getId();
            }
        } elseif ($homeTabId !== -1) {
            foreach ($workspaceHomeTabConfigs as $workspaceHomeTabConfig) {
                if ($homeTabId === $workspaceHomeTabConfig->getHomeTab()->getId()) {
                    $firstElement = false;
                    break;
                }
            }
        }
        if ($firstElement) {
            $firstHomeTabConfig = reset($workspaceHomeTabConfigs);

            if ($firstHomeTabConfig) {
                $homeTabId = $firstHomeTabConfig->getHomeTab()->getId();
            }
        }

        return array(
            'workspace' => $workspace,
            'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
            'tabId' => $homeTabId
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/update/favourite",
     *     name="claro_workspace_update_favourite",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Adds a workspace to the favourite list.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function updateWorkspaceFavourite(AbstractWorkspace $workspace)
    {
        $this->assertIsGranted('ROLE_USER');
        $token = $this->security->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);
        $resultWorkspace = $this->workspaceManager
            ->getWorkspaceByWorkspaceAndRoles($workspace, $roles);

        if (!is_null($resultWorkspace)) {
            $favourite = $this->workspaceManager
                ->getFavouriteByWorkspaceAndUser($workspace, $user);

            if (is_null($favourite)) {
                $this->workspaceManager->addFavourite($workspace, $user);

                return new Response('added', 200);
            } else {
                $this->workspaceManager->removeFavourite($favourite);

                return new Response('removed', 200);
            }
        }

        return new Response('error', 400);
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->security->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceManagerAccess(AbstractWorkspace $workspace)
    {
        $role = $this->roleManager->getManagerRole($workspace);

        if (is_null($role) || !$this->security->isGranted($role->getName())) {
            throw new AccessDeniedException();
        }
    }
}

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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
use Claroline\CoreBundle\Manager\WorkspaceModelManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\WorkspaceUserQueueManager;
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
    private $workspaceModelManager;
    private $workspaceUserQueueManager;
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
     *     "homeTabManager"            = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "workspaceManager"          = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceModelManager"     = @DI\Inject("claroline.manager.workspace_model_manager"),
     *     "workspaceUserQueueManager" = @DI\Inject("claroline.manager.workspace_user_queue_manager"),
     *     "resourceManager"           = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"               = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"               = @DI\Inject("claroline.manager.user_manager"),
     *     "tagManager"                = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "toolManager"               = @DI\Inject("claroline.manager.tool_manager"),
     *     "eventDispatcher"           = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"                  = @DI\Inject("security.context"),
     *     "router"                    = @DI\Inject("router"),
     *     "utils"                     = @DI\Inject("claroline.security.utilities"),
     *     "formFactory"               = @DI\Inject("claroline.form.factory"),
     *     "tokenUpdater"              = @DI\Inject("claroline.security.token_updater"),
     *     "widgetManager"             = @DI\Inject("claroline.manager.widget_manager"),
     *     "request"                   = @DI\Inject("request"),
     *     "templateDir"               = @DI\Inject("%claroline.param.templates_directory%"),
     *     "translator"                = @DI\Inject("translator"),
     *     "session"                   = @DI\Inject("session")
     * })
     */
    public function __construct(
        HomeTabManager $homeTabManager,
        WorkspaceManager $workspaceManager,
        WorkspaceModelManager $workspaceModelManager,
        WorkspaceUserQueueManager $workspaceUserQueueManager,
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
        $this->workspaceModelManager = $workspaceModelManager;
        $this->workspaceUserQueueManager = $workspaceUserQueueManager;
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
     *     "/search/{search}",
     *     name="claro_workspace_list",
     *     defaults={"search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = false})
     * @EXT\Template()
     *
     * Renders the workspace list page with its claroline layout.
     *
     * @param $currentUser
     *
     * @return Response
     */
    public function listAction($currentUser, $search = '')
    {
//        $user = $currentUser instanceof User ? $currentUser : null;

        return $this->tagManager->getDatasForWorkspaceList(false, $search);
    }

    /**
     * @EXT\Route(
     *     "/user",
     *     name="claro_workspace_by_user",
     *     options={"expose"=true}
     * )
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
     *     "/displayable/selfregistration/search/{search}",
     *     name="claro_list_workspaces_with_self_registration",
     *     defaults={"search"=""},
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template()
     *
     * Renders the displayable workspace list.
     *
     * @return Response
     */
    public function listWorkspacesWithSelfRegistrationAction($search = '')
    {
        $this->assertIsGranted('ROLE_USER');
        $user = $this->security->getToken()->getUser();

        return $this->tagManager
            ->getDatasForSelfRegistrationWorkspaceList($user, $search);
    }

    /**
     * @EXT\Route(
     *     "/displayable/selfunregistration/page/{page}",
     *     name="claro_list_workspaces_with_self_unregistration",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
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
        $user = $this->security->getToken()->getUser();
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE, array($user));

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
        $user = $this->security->getToken()->getUser();
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE, array($user));
        $form->handleRequest($this->request);
        $ds = DIRECTORY_SEPARATOR;

        if ($form->isValid()) {
            $model = $form->get('model')->getData();

            if (!is_null($model)) {
                $this->createWorkspaceFromModel($model, $form);
            } else {
                $config = Configuration::fromTemplate(
                    $this->templateDir . $ds . 'default.zip'
                );
                $config->setWorkspaceName($form->get('name')->getData());
                $config->setWorkspaceCode($form->get('code')->getData());
                $config->setDisplayable($form->get('displayable')->getData());
                $config->setSelfRegistration($form->get('selfRegistration')->getData());
                $config->setRegistrationValidation($form->get('registrationValidation')->getData());
                $config->setSelfUnregistration($form->get('selfUnregistration')->getData());
                $config->setWorkspaceDescription($form->get('description')->getData());

                $user = $this->security->getToken()->getUser();
                $this->workspaceManager->create($config, $user);
            }
            $this->tokenUpdater->update($this->security->getToken());
            $route = $this->router->generate('claro_workspace_by_user');

            $msg = $this->get('translator')->trans(
                'successfull_workspace_creation',
                array('%name%' => $form->get('name')->getData()),
                'platform'
            );
            $this->get('request')->getSession()->getFlashBag()->add('success', $msg);

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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function deleteAction(Workspace $workspace)
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
        $sessionFlashBag->add(
            'success', $this->translator->trans(
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
     * @param Workspace $workspace
     * @param integer[] $_breadcrumbs
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return array
     */
    public function renderToolListAction(Workspace $workspace, $_breadcrumbs)
    {
        //first we add check if some tools will be missing from the navbar and we add them if necessary
        $this->toolManager->addMissingWorkspaceTools($workspace);

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

        } else {
            //otherwise only shows the relevant tools
            $orderedTools = $this->toolManager->getOrderedToolsByWorkspaceAndRoles($workspace, $currentRoles);
        }
        $roleHasAccess = array();
        $workspaceRolesWithAccess = $this->roleManager
            ->getWorkspaceRoleWithToolAccess($workspace);

        foreach ($workspaceRolesWithAccess as $workspaceRole) {
            $roleHasAccess[$workspaceRole->getId()] = $workspaceRole;
        }

        return array(
            'hasManagerAccess' => $hasManagerAccess,
            'orderedTools' => $orderedTools,
            'workspace' => $workspace,
            'roleHasAccess' => $roleHasAccess
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open/tool/{toolName}",
     *     name="claro_workspace_open_tool",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Opens a tool.
     *
     * @param string    $toolName
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function openToolAction($toolName, Workspace $workspace)
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:widgetsWithoutConfig.html.twig")
     *
     * Display visible registered widgets.
     *
     * @param Workspace $workspace
     * @param integer           $homeTabId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo Reduce the number of sql queries for this action (-> dql)
     */
    public function widgetsWithoutConfigAction(
        Workspace $workspace,
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Widget:widgetsWithConfig.html.twig")
     *
     * Display registered widgets.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $homeTabId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo Reduce the number of sql queries for this action (-> dql)
     */
    public function widgetsWithConfigAction(Workspace $workspace, $homeTabId)
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
        $isVisibleHomeTab = is_null($homeTab) ? false : true;

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
     *     name="claro_workspace_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Open the first tool of a workspace.
     *
     * @param Workspace $workspace
     * @throws AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function openAction(Workspace $workspace)
    {
        if ($this->security->isGranted('OPEN', $workspace)) {
            $roles = $this->utils->getRoles($this->security->getToken());
            $tools = $this->toolManager->getDisplayedByRolesAndWorkspace($roles, $workspace);

            if (count($tools) > 0) {
                $route = $this->router->generate(
                    'claro_workspace_open_tool',
                    array(
                        'workspaceId' => $workspace->getId(),
                        'toolName' => $tools[0]->getName()
                    )
                );

                return new RedirectResponse($route);
            }
        }

        throw new AccessDeniedException();
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
     *     "/{workspace}/add/user/{user}",
     *     name="claro_workspace_add_user",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     *
     * Adds a user to a workspace.
     *
     * @param Workspace $workspace
     * @param User              $user
     *
     * @return Response
     */
    public function addUserAction(Workspace $workspace, User $user)
    {
        $this->workspaceManager->addUserAction($workspace, $user);

        return new JsonResponse($this->userManager->convertUsersToArray(array($user)));
    }

    /**
     * @todo Security context verification.
     * @EXT\Route(
     *     "/{workspace}/add/user/{user}/queue",
     *     name="claro_workspace_add_user_queue",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     *
     * Adds a user to a workspace.
     *
     * @param Workspace $workspace
     * @param User              $user
     *
     * @return Response
     */
    public function addUserQueueAction(Workspace $workspace, User $user)
    {
        $this->workspaceManager->addUserQueue($workspace, $user);

        return new JsonResponse(array('true'));
    }


    /**
     * @EXT\Route(
     *     "/{workspace}/registration/queue/remove",
     *     name="claro_workspace_remove_user_from_queue",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = false})
     *
     * Removes user from Workspace registration queue.
     *
     * @param Workspace $workspace
     * @param User $user
     *
     * @return Response
     */
    public function removeUserFromQueueAction(Workspace $workspace, User $user)
    {
        $this->workspaceUserQueueManager
            ->removeUserFromWorkspaceQueue($workspace, $user);

        return new Response('success', 204);

    }

    /** @EXT\Route(
     *     "/list/tag/{workspaceTagId}/page/{page}",
     *     name="claro_workspace_list_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
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
     *     "/list/non/personal/workspaces/page/{page}/max/{max}/search/{search}",
     *     name="claro_all_non_personal_workspaces_list_pager",
     *     defaults={"page"=1,"max"=20,"seach"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param integer $page
     *
     * @return array
     */
    public function nonPersonalWorkspacesListPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    )
    {
        $nonPersonalWs = $this->workspaceManager
            ->getDisplayableNonPersonalWorkspaces($page, $max, $search);

        return array(
            'nonPersonalWs' => $nonPersonalWs,
            'max' => $max,
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "/list/personal/workspaces/page/{page}/max/{max}/search/{search}",
     *     name="claro_all_personal_workspaces_list_pager",
     *     defaults={"page"=1,"max"=20,"seach"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param integer $page
     *
     * @return array
     */
    public function personalWorkspacesListPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    )
    {
        $personalWs = $this->workspaceManager
            ->getDisplayablePersonalWorkspaces($page, $max, $search);

        return array(
            'personalWs' => $personalWs,
            'max' => $max,
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "/list/workspaces/self_reg/page/{page}",
     *     name="claro_all_workspaces_list_with_self_reg_pager",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
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
     * @param Workspace                 $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function removeUserAction(Workspace $workspace, User $user)
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
     *     "/registration/list/non/personal/workspaces/page/{page}/max/{max}/search/{search}",
     *     name="claro_all_non_personal_workspaces_list_registration_pager",
     *     defaults={"page"=1,"max"=20,"seach"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param integer $page
     *
     * @return array
     */
    public function nonPersonalWorkspacesListRegistrationPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    )
    {
        $nonPersonalWs = $this->workspaceManager
            ->getDisplayableNonPersonalWorkspaces($page, $max, $search);

        return array(
            'nonPersonalWs' => $nonPersonalWs,
            'max' => $max,
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "/registration/list/personal/workspaces/page/{page}/max/{max}/search/{search}",
     *     name="claro_all_personal_workspaces_list_registration_pager",
     *     defaults={"page"=1,"max"=20,"seach"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param integer $page
     *
     * @return array
     */
    public function personalWorkspacesListRegistrationPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    )
    {
        $personalWs = $this->workspaceManager
            ->getDisplayablePersonalWorkspaces($page, $max, $search);

        return array(
            'personalWs' => $personalWs,
            'max' => $max,
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "/registration/list/workspaces/search/{search}/page/{page}",
     *     name="claro_workspaces_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabsWithoutConfig.html.twig")
     *
     * Displays the workspace home tab without config.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $tabId
     *
     * @return array
     */
    public function displayWorkspaceHomeTabsActionWithoutConfig(
        Workspace $workspace,
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHomeTabsWithConfig.html.twig")
     *
     * Displays the workspace home tab.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param integer $tabId
     *
     * @return array
     */
    public function displayWorkspaceHomeTabsActionWithConfig(
        Workspace $workspace,
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Adds a workspace to the favourite list.
     *
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function updateWorkspaceFavourite(Workspace $workspace)
    {
        $this->assertIsGranted('ROLE_USER');
        $token = $this->security->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);
        $workspaceManagerRole = $this->roleManager
            ->getManagerRole($workspace)->getRole();
        $isWorkspaceManager = false;

        if (in_array($workspaceManagerRole, $roles)) {
            $isWorkspaceManager = true;
        }

        if (!$isWorkspaceManager) {
            $resultWorkspace = $this->workspaceManager
                ->getWorkspaceByWorkspaceAndRoles($workspace, $roles);
        }

        if ($isWorkspaceManager || !is_null($resultWorkspace)) {
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

    /**
     * @EXT\Route(
     *     "/{workspace}/export",
     *     name="claro_workspace_export",
     *     options={"expose"=true}
     * )
     */
    public function exportAction(Workspace $workspace)
    {
        $archive = $this->container->get('claroline.manager.transfert_manager')->export($workspace);

        $fileName = $workspace->getCode();
        $mimeType = 'application/zip';
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($archive) {
                readfile($archive);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($fileName));
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/import/form",
     *     name="claro_workspace_import_form",
     * )
     * @EXT\Template()
     *
     * @param int $page
     *
     * @return array
     */
    public function importFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_IMPORT, array());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/import/submit",
     *     name="claro_workspace_import",
     * )
     * @EXT\Template()
     *
     * @param int $page
     *
     * @return array
     */
    public function importAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_IMPORT, array());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $template = $form->get('workspace')->getData();
            $config = Configuration::fromTemplate($template);
            $config->setWorkspaceName($form->get('name')->getData());
            $config->setWorkspaceCode($form->get('code')->getData());
            $config->setDisplayable(true);
            $config->setSelfRegistration(true);
            $config->setRegistrationValidation(true);
            $config->setSelfUnregistration(true);
            $config->setWorkspaceDescription(true);
            $this->workspaceManager->create($config, $this->security->getToken()->getUser());
            $this->workspaceManager->importRichText();
        } else {
            throw new \Exception('Invalid form');
        }

        $route = $this->router->generate('claro_workspace_by_user');

        return new RedirectResponse($route);
    }

    /**
     * @EXT\Route(
     *     "/list/all/workspaces/pager/page/{page}/max/{wsMax}/resource/{resource}/search/{wsSearch}",
     *     name="claro_all_workspaces_list_pager_for_resource_rights",
     *     defaults={"page"=1,"wsMax"=10,"seach"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param ResourceNode $resource
     * @param integer $page
     * @param integer $wsMax
     * @param string $wsSearch
     *
     * @return array
     */
    public function allWorkspacesListPagerForResourceRightsAction(
        ResourceNode $resource,
        $page = 1,
        $wsMax = 10,
        $wsSearch = ''
    )
    {
        if ($wsSearch === '') {
            $workspaces = $this->workspaceManager
                ->getDisplayableWorkspacesPager($page, $wsMax);
        } else {
            $workspaces = $this->workspaceManager
                ->getDisplayableWorkspacesBySearchPager($wsSearch, $page, $wsMax);
        }
        $workspaceRoles = array();
        $roles = $this->roleManager->getAllWhereWorkspaceIsDisplayableAndInList(
            $workspaces->getCurrentPageResults()
        );

        foreach ($roles as $role) {
            $wsRole = $role->getWorkspace();

            if (!is_null($wsRole)) {
                $code = $wsRole->getCode();

                if (!isset($workspaceRoles[$code])) {
                    $workspaceRoles[$code] = array();
                }

                $workspaceRoles[$code][] = $role;
            }
        }

        return array(
            'workspaces' => $workspaces,
            'wsMax' => $wsMax,
            'wsSearch' => $wsSearch,
            'workspaceRoles' => $workspaceRoles,
            'resource' => $resource
        );
    }

    private function createWorkspaceFromModel(WorkspaceModel $model, FormInterface $form)
    {
        $workspace = $this->workspaceManager->createWorkspaceFromModel(
            $model,
            $this->security->getToken()->getUser(),
            $form->get('name')->getData(),
            $form->get('code')->getData(),
            $form->get('description')->getData(),
            $form->get('displayable')->getData(),
            $form->get('selfRegistration')->getData(),
            $form->get('selfUnregistration')->getData(),
            $errors
        );

        $flashBag = $this->session->getFlashBag();

        foreach ($errors['widgetConfigErrors'] as $widgetConfigError) {
            $widgetName = $widgetConfigError['widgetName'];
            $widgetInstanceName = $widgetConfigError['widgetInstanceName'];
            $msg = '[' .
                $this->translator->trans($widgetName, array(), 'widget') .
                '] ' .
                $this->translator->trans(
                    'widget_configuration_copy_warning',
                    array('%widgetInstanceName%' => $widgetInstanceName),
                    'widget'
                );
            $flashBag->add('error', $msg);
        }

        foreach ($errors['resourceErrors'] as $resourceError) {
            $resourceName = $resourceError['resourceName'];
            $resourceType = $resourceError['resourceType'];
            $isCopy = $resourceError['type'] === 'copy';

            $msg = '[' .
                $this->translator->trans($resourceType, array(), 'resource') .
                '] ';

            if ($isCopy) {
                $msg .= $this->translator->trans(
                    'resource_copy_warning',
                    array('%resourceName%' => $resourceName),
                    'resource'
                );
            }
            $flashBag->add('error', $msg);
        }
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->security->isGranted($attributes, $object)) {
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

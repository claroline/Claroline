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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Log\LogRoleUnsubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Form\ImportWorkspaceType;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Logger\FileLogger;
use Claroline\CoreBundle\Library\Security\TokenUpdater;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\WorkspaceUserQueueManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This controller is able to:
 * - list/create/delete/show workspaces.
 * - return some users/groups list (ie: (un)registered users to a workspace).
 * - add/delete users/groups to a workspace.
 */
class WorkspaceController extends Controller
{
    private $authorization;
    private $eventDispatcher;
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $resourceManager;
    private $roleManager;
    private $router;
    private $session;
    private $tagManager;
    private $templateArchive;
    private $templating;
    private $tokenStorage;
    private $tokenUpdater;
    private $toolManager;
    private $translator;
    private $userManager;
    private $utils;
    private $widgetManager;
    private $workspaceManager;
    private $workspaceUserQueueManager;

    /**
     * @DI\InjectParams({
     *     "authorization"             = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"           = @DI\Inject("event_dispatcher"),
     *     "formFactory"               = @DI\Inject("form.factory"),
     *     "homeTabManager"            = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"                   = @DI\Inject("request"),
     *     "resourceManager"           = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"               = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                    = @DI\Inject("router"),
     *     "session"                   = @DI\Inject("session"),
     *     "tagManager"                = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "templateArchive"           = @DI\Inject("%claroline.param.default_template%"),
     *     "templating"                = @DI\Inject("templating"),
     *     "tokenStorage"              = @DI\Inject("security.token_storage"),
     *     "tokenUpdater"              = @DI\Inject("claroline.security.token_updater"),
     *     "toolManager"               = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"                = @DI\Inject("translator"),
     *     "userManager"               = @DI\Inject("claroline.manager.user_manager"),
     *     "utils"                     = @DI\Inject("claroline.security.utilities"),
     *     "widgetManager"             = @DI\Inject("claroline.manager.widget_manager"),
     *     "workspaceManager"          = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceUserQueueManager" = @DI\Inject("claroline.manager.workspace_user_queue_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        FormFactory $formFactory,
        HomeTabManager $homeTabManager,
        Request $request,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        WorkspaceTagManager $tagManager,
        $templateArchive,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        TokenUpdater $tokenUpdater,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        Utilities $utils,
        WidgetManager $widgetManager,
        WorkspaceManager $workspaceManager,
        WorkspaceUserQueueManager $workspaceUserQueueManager
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->homeTabManager = $homeTabManager;
        $this->request = $request;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->session = $session;
        $this->tagManager = $tagManager;
        $this->templateArchive = $templateArchive;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->tokenUpdater = $tokenUpdater;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->utils = $utils;
        $this->widgetManager = $widgetManager;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceUserQueueManager = $workspaceUserQueueManager;
    }

    /**
     * @EXT\Route(
     *     "/search/{search}",
     *     name="claro_workspace_list",
     *     defaults={"search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * Renders the workspace list page with its claroline layout.
     *
     * @param $search
     *
     * @return Response
     */
    public function listAction($search = '')
    {
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
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);

        $data = $this->tagManager->getDatasForWorkspaceListByUser($user, $roles);
        $favouriteWorkspaces = $this->workspaceManager
            ->getFavouriteWorkspacesByUser($user);
        $favourites = [];

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
     *     "/user/picker",
     *     name="claro_workspace_by_user_picker",
     *     options={"expose"=true}
     * )
     *
     * Renders the registered workspace list for a user to be used by the picker.
     *
     * @return Response
     */
    public function listWorkspacesByUserForPickerAction()
    {
        $isGranted = $this->authorization->isGranted('ROLE_USER');
        $response = new JsonResponse('', 401);
        if ($isGranted === true) {
            $token = $this->tokenStorage->getToken();
            $user = $token->getUser();

            $workspaces = $this->workspaceManager
                ->getWorkspacesByUser($user);

            $workspacesData = [];
            foreach ($workspaces as $workspace) {
                array_push($workspacesData, $workspace->serializeForWidgetPicker());
            }
            $data = ['items' => $workspacesData];
            $response->setData($data)->setStatusCode(200);
        }

        return $response;
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
     * @param $search
     *
     * @return Response
     */
    public function listWorkspacesWithSelfRegistrationAction($search = '')
    {
        $this->assertIsGranted('ROLE_USER');
        $user = $this->tokenStorage->getToken()->getUser();

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
     * @EXT\ParamConverter("currentUser", converter="current_user")
     *
     * @EXT\Template()
     *
     * Renders the displayable workspace list with self-unregistration.
     *
     * @param \Claroline\CoreBundle\Entity\User $currentUser
     * @param int                               $page
     *
     * @return array
     */
    public function listWorkspacesWithSelfUnregistrationAction(User $currentUser, $page = 1)
    {
        $token = $this->tokenStorage->getToken();
        $roles = $this->utils->getRoles($token);

        $workspacesPager = $this->workspaceManager
            ->getWorkspacesWithSelfUnregistrationByRoles($roles, $page);

        return [
            'user' => $currentUser,
            'workspaces' => $workspacesPager,
        ];
    }

    /**
     * @EXT\Route(
     *     "/new/form",
     *     name="claro_workspace_creation_form",
     *     options={"expose"=true}
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
        $user = $this->tokenStorage->getToken()->getUser();
        $workspaceType = new WorkspaceType($user);
        $form = $this->formFactory->create($workspaceType);

        return ['form' => $form->createView()];
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
        $user = $this->tokenStorage->getToken()->getUser();
        $workspaceType = new WorkspaceType($user);
        $form = $this->formFactory->create($workspaceType, new Workspace());
        $form->handleRequest($this->request);
        $modelLog = $this->container->getParameter('kernel.root_dir').'/logs/models.log';
        $logger = FileLogger::get($modelLog);
        $this->workspaceManager->setLogger($logger);

        if ($form->isValid()) {
            $model = $form->get('model')->getData();
            $workspace = $form->getData();
            $user = $this->tokenStorage->getToken()->getUser();
            $workspace->setCreator($user);
            if (!$model) {
                $model = $this->workspaceManager->getDefaultModel();
            }
            $workspace = $this->workspaceManager->copy($model, $workspace);
            $this->tokenUpdater->update($this->tokenStorage->getToken());
            $route = $this->router->generate('claro_workspace_open', ['workspaceId' => $workspace->getId()]);

            $msg = $this->get('translator')->trans(
                'successfull_workspace_creation',
                ['%name%' => $form->get('name')->getData()],
                'platform'
            );
            $this->get('request')->getSession()->getFlashBag()->add('success', $msg);

            return new RedirectResponse($route);
        }

        return ['form' => $form->createView()];
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
        $this->eventDispatcher->dispatch('log', new LogWorkspaceDeleteEvent($workspace));
        $this->workspaceManager->deleteWorkspace($workspace);

        $this->tokenUpdater->cancelUsurpation($this->tokenStorage->getToken());

        $sessionFlashBag = $this->session->getFlashBag();
        $sessionFlashBag->add(
            'success', $this->translator->trans(
                'workspace_delete_success_message',
                ['%workspaceName%' => $workspace->getName()],
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
     * @param int[]     $_breadcrumbs
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return array
     */
    public function renderToolListAction(Workspace $workspace, $_breadcrumbs)
    {
        //first we add check if some tools will be missing from the navbar and we add them if necessary
        //lets be sure we loaded everything properly because the pathbundle broke something
        $workspace = $this->workspaceManager->getWorkspaceByCode($workspace->getCode());
        $this->toolManager->addMissingWorkspaceTools($workspace);

        if (!empty($_breadcrumbs)) {
            //for manager.js, id = 0 => "no root".
            if ($_breadcrumbs[0] !== 0) {
                $rootId = $_breadcrumbs[0];
            } else {
                $rootId = $_breadcrumbs[1];
            }
            $workspace = $this->resourceManager->getNode($rootId)->getWorkspace();
        }

        $currentRoles = $this->utils->getRoles($this->tokenStorage->getToken());
        //do I need to display every tools.
        $hasManagerAccess = false;
        $managerRole = $this->roleManager->getManagerRole($workspace);

        foreach ($currentRoles as $role) {
            if ($managerRole->getName() === $role) {
                $hasManagerAccess = true;
            }
        }

        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            $hasManagerAccess = true;
        }

        if ($workspace->isModel()) {
            $orderedTools = array_filter($this->toolManager->getOrderedToolsByWorkspace($workspace), function ($orderedTool) {
                return in_array($orderedTool->getTool()->getName(), ['home', 'resource_manager', 'users', 'parameters']);
            });
            $hideToolsMenu = false;
        } else {
            //if manager or admin, show every tools
          if ($hasManagerAccess) {
              $orderedTools = $this->toolManager->getOrderedToolsByWorkspace($workspace);
              $hideToolsMenu = false;
          } else {
              //otherwise only shows the relevant tools
              $orderedTools = $this->toolManager->getOrderedToolsByWorkspaceAndRoles($workspace, $currentRoles);
              $hideToolsMenu = $this->workspaceManager->isToolsMenuHidden($workspace);
          }
        }

        $roleHasAccess = [];
        $workspaceRolesWithAccess = $this->roleManager
            ->getWorkspaceRoleWithToolAccess($workspace);

        foreach ($workspaceRolesWithAccess as $workspaceRole) {
            $roleHasAccess[$workspaceRole->getId()] = $workspaceRole;
        }

        return [
            'hasManagerAccess' => $hasManagerAccess,
            'orderedTools' => $orderedTools,
            'workspace' => $workspace,
            'roleHasAccess' => $roleHasAccess,
            'hideToolsMenu' => $hideToolsMenu,
        ];
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
        $event = $this->eventDispatcher->dispatch('open_tool_workspace_'.$toolName, new DisplayToolEvent($workspace));
        $this->eventDispatcher->dispatch('log', new LogWorkspaceToolReadEvent($workspace, $toolName));
        $this->eventDispatcher->dispatch('log', new LogWorkspaceEnterEvent($workspace));
        // Add workspace to recent workspaces if user is not Usurped
        if (!$this->isUsurpator($this->tokenStorage->getToken())) {
            $this->workspaceManager->addRecentWorkspaceForUser($this->tokenStorage->getToken()->getUser(), $workspace);
        }

        if ($toolName === 'resource_manager') {
            $this->session->set('isDesktop', false);
        }

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tab/{homeTabId}/picker",
     *     name="claro_workspace_home_tab_widget_list_picker",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * Returns a list with all visible registered widgets for a homeTab of a workspace.
     *
     * @param Workspace $workspace
     * @param int       $homeTabId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listWidgetsForPickerAction(
        Workspace $workspace,
        $homeTabId
    ) {
        $response = new JsonResponse('', 401);
        $isGranted = $this->authorization->isGranted('OPEN', $workspace);

        if ($isGranted === true) {
            $widgetData = [];
            $widgetHomeTabConfigs = $this->homeTabManager
                ->getVisibleWidgetConfigsByTabIdAndWorkspace($homeTabId, $workspace);
            foreach ($widgetHomeTabConfigs as $widgetHomeTabConfig) {
                array_push($widgetData, $widgetHomeTabConfig->getWidgetInstance()->serializeForWidgetPicker());
            }
            $data = [
                'items' => $widgetData,
            ];

            $response->setData($data)->setStatusCode(200);
        }

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tab/{homeTabId}/widget/{widgetId}/embed",
     *     name="claro_workspace_home_tab_widget_embed_picker",
     *     options={"expose"=true}
     * )
     *
     * Returns the html iframe to embed a widget
     *
     * @param int $workspaceId
     * @param int $homeTabId
     * @param int $widgetId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function embedWidgetForPickerAction(
        $workspaceId,
        $homeTabId,
        $widgetId
    ) {
        return new Response(
            $this->templating->render(
                'ClarolineCoreBundle:Widget:embed/iframe.html.twig',
                [
                    'widgetId' => $widgetId,
                    'workspaceId' => $workspaceId,
                    'homeTabId' => $homeTabId,
                ]
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tab/{homeTabId}/widget/{widgetId}/embeded",
     *     name="claro_workspace_hometab_embeded_widget",
     *     options={"expose"=true}
     * )
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Returns the widget's html content
     *
     * @param Workspace $workspace
     * @param int       $homeTabId
     * @param int       $widgetId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEmbededWidgetAction(
        Workspace $workspace,
        $homeTabId,
        $widgetId
    ) {
        $this->assertIsGranted('OPEN', $workspace);

        $widgetConfig = $this->homeTabManager
            ->getVisibleWidgetConfigByWidgetIdAndTabIdAndWorkspace($widgetId, $homeTabId, $workspace);

        $widget = null;

        if (!empty($widgetConfig)) {
            $widgetInstance = $widgetConfig->getWidgetInstance();
            $event = $this->eventDispatcher->dispatch("widget_{$widgetInstance->getWidget()->getName()}", new DisplayWidgetEvent($widgetInstance));
            $widget = [
                'title' => $widgetInstance->getName(),
                'content' => $event->getContent(),
            ];
        }

        return new Response(
            $this->templating->render(
                'ClarolineCoreBundle:Widget:embed/widget.html.twig',
                [
                    'widget' => $widget,
                ]
            )
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
     * @SEC\PreAuthorize("canAccessWorkspace('OPEN')")
     *
     * Open the first tool of a workspace.
     *
     * @param Workspace $workspace
     *
     * @throws AccessDeniedException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function openAction(Workspace $workspace)
    {
        $options = $workspace->getOptions();

        if (!is_null($options)) {
            $details = $options->getDetails();

            if (isset($details['use_workspace_opening_resource']) &&
                $details['use_workspace_opening_resource'] &&
                isset($details['workspace_opening_resource']) &&
                !empty($details['workspace_opening_resource'])) {
                $resourceNode = $this->resourceManager->getById($details['workspace_opening_resource']);

                if (!is_null($resourceNode)) {
                    $this->session->set('isDesktop', false);
                    $route = $this->router->generate(
                        'claro_resource_open',
                        [
                            'node' => $resourceNode->getId(),
                            'resourceType' => $resourceNode->getResourceType()->getName(),
                        ]
                    );

                    return new RedirectResponse($route);
                }
            }
        }

        $tool = $this->workspaceManager->getFirstOpenableTool($workspace);

        if ($tool) {
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                [
                    'workspaceId' => $workspace->getId(),
                    'toolName' => $tool->getName(),
                ]
            );

            return new RedirectResponse($route);
        }

        $this->throwWorkspaceDeniedException($workspace);
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
        $arWorkspace = [];

        foreach ($roles as $role) {
            $arWorkspace[$role->getWorkspace()->getCode()][$role->getName()] = [
                'name' => $role->getName(),
                'translation_key' => $role->getTranslationKey(),
                'id' => $role->getId(),
                'workspace' => $role->getWorkspace()->getName(),
            ];
        }

        return new JsonResponse($arWorkspace);
    }

    /**
     * @todo Security context verification
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
     * @param User      $user
     *
     * @return Response
     */
    public function addUserAction(Workspace $workspace, User $user)
    {
        $this->workspaceManager->addUserAction($workspace, $user);

        return new JsonResponse($this->userManager->convertUsersToArray([$user]));
    }

    /**
     * @todo Security context verification
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
     * @param User      $user
     *
     * @return Response
     */
    public function addUserQueueAction(Workspace $workspace, User $user)
    {
        $this->workspaceManager->addUserQueue($workspace, $user);

        return new JsonResponse(['true']);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/registration/queue/remove",
     *     name="claro_workspace_remove_user_from_queue",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * Removes user from Workspace registration queue.
     *
     * @param Workspace $workspace
     * @param User      $user
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
     * @param int                                                 $page
     *
     * @return array
     */
    public function workspaceListByTagPagerAction(WorkspaceTag $workspaceTag, $page = 1)
    {
        $relations = $this->tagManager->getPagerRelationByTag($workspaceTag, $page);

        return [
            'workspaceTagId' => $workspaceTag->getId(),
            'relations' => $relations,
        ];
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
     * @param int                                                 $page
     *
     * @return array
     */
    public function workspaceListWithSelfRegByTagPagerAction(
        WorkspaceTag $workspaceTag,
        $page = 1
    ) {
        $relations = $this->tagManager
            ->getPagerRelationByTagForSelfReg($workspaceTag, $page);

        return [
            'workspaceTagId' => $workspaceTag->getId(),
            'relations' => $relations,
        ];
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
     * @param int $page
     *
     * @return array
     */
    public function workspaceCompleteListPagerAction($page = 1)
    {
        $workspaces = $this->tagManager->getPagerAllWorkspaces($page);

        return ['workspaces' => $workspaces];
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
     * @param int $page
     *
     * @return array
     */
    public function nonPersonalWorkspacesListPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    ) {
        $nonPersonalWs = $this->workspaceManager
            ->getDisplayableNonPersonalWorkspaces($page, $max, $search);

        return [
            'nonPersonalWs' => $nonPersonalWs,
            'max' => $max,
            'search' => $search,
        ];
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
     * @param int $page
     *
     * @return array
     */
    public function personalWorkspacesListPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    ) {
        $personalWs = $this->workspaceManager
            ->getDisplayablePersonalWorkspaces($page, $max, $search);

        return [
            'personalWs' => $personalWs,
            'max' => $max,
            'search' => $search,
        ];
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
            $this->tokenStorage->getToken()->getUser(),
            $page
        );

        return ['workspaces' => $workspaces];
    }

    /**
     * @todo Security context verification
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
     * @param Workspace                         $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function removeUserAction(Workspace $workspace, User $user)
    {
        try {
            $roles = $this->roleManager->getRolesByWorkspace($workspace);
            $this->roleManager->checkWorkspaceRoleEditionIsValid([$user], $workspace, $roles);

            foreach ($roles as $role) {
                if ($user->hasRole($role->getName())) {
                    $this->roleManager->dissociateRole($user, $role);
                    $this->eventDispatcher->dispatch('log', new LogRoleUnsubscribeEvent($role, $user));
                }
            }
            $this->tagManager->deleteAllRelationsFromWorkspaceAndUser($workspace, $user);

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            return new Response('success', 204);
        } catch (LastManagerDeleteException $e) {
            return new Response(
                'cannot_delete_unique_manager',
                200,
                ['XXX-Claroline-delete-last-manager']
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

        return [
            'workspaceTagId' => $workspaceTag->getId(),
            'relations' => $relations,
        ];
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

        return ['workspaces' => $workspaces];
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
     * @param int $page
     *
     * @return array
     */
    public function nonPersonalWorkspacesListRegistrationPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    ) {
        $nonPersonalWs = $this->workspaceManager
            ->getDisplayableNonPersonalWorkspaces($page, $max, $search);

        return [
            'nonPersonalWs' => $nonPersonalWs,
            'max' => $max,
            'search' => $search,
        ];
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
     * @param int $page
     *
     * @return array
     */
    public function personalWorkspacesListRegistrationPagerAction(
        $page = 1,
        $max = 20,
        $search = ''
    ) {
        $personalWs = $this->workspaceManager
            ->getDisplayablePersonalWorkspaces($page, $max, $search);

        return [
            'personalWs' => $personalWs,
            'max' => $max,
            'search' => $search,
        ];
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

        return ['workspaces' => $pager, 'search' => $search];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/open/tool/home/tab/{tabId}",
     *     name="claro_display_workspace_home_tab",
     *     options = {"expose"=true}
     * )
     *
     * Displays the workspace home tab.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param int                                              $tabId
     */
    public function displayWorkspaceHomeTabAction(Workspace $workspace, $tabId)
    {
        return $this->redirectToRoute('claro_workspace_home_display', ['workspace' => $workspace->getId(), 'tabId' => $tabId]);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tabs/picker",
     *     name="claro_list_visible_workspace_home_tabs_picker",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Returns the list of visible tabs for a workspace
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function listWorkspaceVisibleHomeTabsForPickerAction(
        Workspace $workspace
    ) {
        $response = new JsonResponse('', 401);
        $isGranted = $this->authorization->isGranted('OPEN', $workspace);
        if ($isGranted === true) {
            $workspaceHomeTabConfigs = $this->homeTabManager
                ->getVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);

            $tabsData = [];
            foreach ($workspaceHomeTabConfigs as $workspaceHomeTabConfig) {
                array_push($tabsData, $workspaceHomeTabConfig->getHomeTab()->serializeForWidgetPicker());
            }
            $data = ['items' => $tabsData];
            $response->setData($data)->setStatusCode(200);
        }

        return $response;
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
        $token = $this->tokenStorage->getToken();
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
        $archive = $this->container->get('claroline.manager.transfer_manager')->export($workspace);

        $fileName = $workspace->getCode().'.zip';
        $mimeType = 'application/zip';
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($archive) {
                readfile($archive);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($fileName));
        $response->headers->set('Content-Length', filesize($archive));
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Connection', 'close');

        return $response->send();
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
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $importType = new ImportWorkspaceType();
        $form = $this->container->get('form.factory')->create($importType);

        return ['form' => $form->createView()];
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
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $importType = new ImportWorkspaceType();
        $form = $this->container->get('form.factory')->create($importType, new Workspace());
        $form->handleRequest($this->request);
        $modelLog = $this->container->getParameter('kernel.root_dir').'/logs/models.log';
        $logger = FileLogger::get($modelLog);
        $this->workspaceManager->setLogger($logger);

        if ($form->isValid()) {
            $urlImport = false;
            if ($form->get('workspace')->getData()) {
                $file = $form->get('workspace')->getData();
                $template = new File($file);
            } elseif ($form->get('fileUrl')->getData() && filter_var($form->get('fileUrl')->getData(), FILTER_VALIDATE_URL)) {
                $urlImport = true;
                $url = $form->get('fileUrl')->getData();
                $template = $this->importFromUrl($url);
                if ($template === null) {
                    $msg = $this->translator->trans(
                        'invalid_host',
                        ['%url%' => $url],
                        'platform'
                    );
                    $this->session->getFlashBag()->add('error', $msg);
                }
            }

            if ($template !== null) {
                $workspace = $form->getData();
                $workspace->setCreator($this->tokenStorage->getToken()->getUser());
                $this->workspaceManager->create($workspace, $template);
                //delete manually created tmp if url import
                if ($urlImport) {
                    $fs = new FileSystem();
                    $fs->remove($template);
                }
                $this->tokenUpdater->update($this->tokenStorage->getToken());

                $route = $this->router->generate('claro_workspace_by_user');
                $msg = $this->get('translator')->trans(
                    'successfull_workspace_creation',
                    ['%name%' => $form->get('name')->getData()],
                    'platform'
                );
                $this->session->getFlashBag()->add('success', $msg);

                return new RedirectResponse($route);
            }
        }

        return new Response(
            $this->templating->render(
                'ClarolineCoreBundle:Workspace:importForm.html.twig',
                ['form' => $form->createView()]
            )
        );
    }

    private function importFromUrl($url)
    {
        $template = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);

        //check if url is a valid provider
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($retcode === 200 || $retcode === 201) {
            $import_sub_folder = 'import'.DIRECTORY_SEPARATOR;
            $import_folder_path = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir').DIRECTORY_SEPARATOR.$import_sub_folder;
            $fs = new FileSystem();
            if (!$fs->exists($import_folder_path)) {
                $fs->mkdir($import_folder_path);
            }

            //REST URI hash used as unique file identifier for temporary template
            $filepath = $import_folder_path.md5($url).'.zip';

            //if already exists resume using it, no need to upload it again
            if (file_exists($filepath)) {
                return new File($filepath);
            } else {
                $fileWriter = fopen($filepath, 'w+');
                curl_setopt($ch, CURLOPT_NOBODY, false);
                curl_setopt($ch, CURLOPT_FILE, $fileWriter);
                curl_setopt($ch, CURLOPT_TIMEOUT, 900);
                curl_exec($ch);

                if (!curl_errno($ch)) {
                    $template = new File($filepath);
                }
                fclose($fileWriter);
            }
        }
        curl_close($ch);

        return $template;
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
     * @param int          $page
     * @param int          $wsMax
     * @param string       $wsSearch
     *
     * @return array
     */
    public function allWorkspacesListPagerForResourceRightsAction(
        ResourceNode $resource,
        $page = 1,
        $wsMax = 10,
        $wsSearch = ''
    ) {
        if ($wsSearch === '') {
            $workspaces = $this->workspaceManager
                ->getDisplayableWorkspacesPager($page, $wsMax);
        } else {
            $workspaces = $this->workspaceManager
                ->getDisplayableWorkspacesBySearchPager($wsSearch, $page, $wsMax);
        }
        $workspaceRoles = [];
        $roles = $this->roleManager->getAllWhereWorkspaceIsDisplayableAndInList(
            $workspaces->getCurrentPageResults()
        );

        foreach ($roles as $role) {
            $wsRole = $role->getWorkspace();

            if (!is_null($wsRole)) {
                $code = $wsRole->getCode();

                if (!isset($workspaceRoles[$code])) {
                    $workspaceRoles[$code] = [];
                }

                $workspaceRoles[$code][] = $role;
            }
        }

        return [
            'workspaces' => $workspaces,
            'wsMax' => $wsMax,
            'wsSearch' => $wsSearch,
            'workspaceRoles' => $workspaceRoles,
            'resource' => $resource,
        ];
    }

    private function isUsurpator($token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role->getRole() === 'ROLE_USURPATE_WORKSPACE_ROLE' || $role instanceof SwitchUserRole) {
                return true;
            }
        }

        return false;
    }

    private function throwWorkspaceDeniedException(Workspace $workspace)
    {
        $exception = new Exception\WorkspaceAccessDeniedException();
        $exception->setWorkspace($workspace);

        throw $exception;
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->authorization->isGranted($attributes, $object)) {
            if ($object instanceof Workspace) {
                $this->throwWorkspaceDeniedException($object);
            }
        }
    }
}

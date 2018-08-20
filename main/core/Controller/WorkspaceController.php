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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Library\Security\TokenUpdater;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
    private $homeTabManager;
    private $request;
    private $resourceManager;
    private $roleManager;
    private $router;
    private $session;
    private $tokenStorage;
    private $tokenUpdater;
    private $toolManager;
    private $translator;
    private $utils;
    private $workspaceManager;
    private $om;
    /** @var ParametersSerializer */
    private $parametersSerializer;

    /**
     * @DI\InjectParams({
     *     "authorization"             = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"           = @DI\Inject("event_dispatcher"),
     *     "homeTabManager"            = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "request"                   = @DI\Inject("request_stack"),
     *     "resourceManager"           = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"               = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                    = @DI\Inject("router"),
     *     "session"                   = @DI\Inject("session"),
     *     "tokenStorage"              = @DI\Inject("security.token_storage"),
     *     "tokenUpdater"              = @DI\Inject("claroline.security.token_updater"),
     *     "toolManager"               = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"                = @DI\Inject("translator"),
     *     "utils"                     = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"          = @DI\Inject("claroline.manager.workspace_manager"),
     *     "parametersSerializer"      = @DI\Inject("claroline.serializer.parameters"),
     *     "om"                        = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        HomeTabManager $homeTabManager,
        RequestStack $request,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        TokenUpdater $tokenUpdater,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        ParametersSerializer $parametersSerializer,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->homeTabManager = $homeTabManager;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->tokenUpdater = $tokenUpdater;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->parametersSerializer = $parametersSerializer;
        $this->om = $om;
    }

    /**
     * @EXT\Route(
     *     "/list",
     *     name="claro_workspace_list"
     * )
     * @EXT\Template
     */
    public function listAction()
    {
        return ['parameters' => $this->parametersSerializer->serialize()];
    }

    /**
     * @EXT\Route(
     *     "/list/currentuser",
     *     name="claro_workspace_by_user"
     * )
     * @EXT\Template
     */
    public function listByUserAction()
    {
        return ['parameters' => $this->parametersSerializer->serialize()];
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
     *
     * @deprecated
     */
    public function listWorkspacesByUserForPickerAction()
    {
        $isGranted = $this->authorization->isGranted('ROLE_USER');
        $response = new JsonResponse('', 401);
        if (true === $isGranted) {
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
     *     "/{workspaceId}",
     *     name="claro_workspace_delete",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
     * )
     *
     * @param Workspace $workspace
     *
     * @return Response
     *
     * @deprecated use the one provided by api
     */
    public function deleteAction(Workspace $workspace)
    {
        $this->assertIsGranted('DELETE', $workspace);
        $notDeletableNodes = $this->resourceManager->getNotDeletableResourcesByWorkspace($workspace);
        $sessionFlashBag = $this->session->getFlashBag();

        if (0 === count($notDeletableNodes)) {
            $this->eventDispatcher->dispatch('log', new LogWorkspaceDeleteEvent($workspace));
            $this->workspaceManager->deleteWorkspace($workspace);

            $this->tokenUpdater->cancelUsurpation($this->tokenStorage->getToken());

            $sessionFlashBag->add(
                'success',
                $this->translator->trans(
                    'workspace_delete_success_message',
                    ['%workspaceName%' => $workspace->getName()],
                    'platform'
                )
            );

            return new Response('success', 204);
        } else {
            $sessionFlashBag->add(
                'error',
                $this->translator->trans(
                    'workspace_not_deletable_resources_error_message',
                    ['%workspaceName%' => $workspace->getName()],
                    'platform'
                )
            );

            foreach ($notDeletableNodes as $node) {
                $sessionFlashBag->add('error', $node->getPathForDisplay());
            }

            return new Response('error', 403);
        }
    }

    /**
     * Renders the left tool bar. Not routed.
     *
     * @EXT\Template("ClarolineCoreBundle:workspace:toolbar.html.twig")
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return array
     */
    public function renderToolbarAction(Workspace $workspace, Request $request)
    {
        $orderedTools = [];

        $hasManagerAccess = $this->workspaceManager->isManager($workspace, $this->tokenStorage->getToken());
        $hideToolsMenu = $this->workspaceManager->isToolsMenuHidden($workspace);
        if ($hasManagerAccess || !$hideToolsMenu) {
            // load tool list
            if ($hasManagerAccess) {
                // gets all available tools
                $orderedTools = $this->toolManager->getOrderedToolsByWorkspace($workspace);
                // always display tools to managers
                $hideToolsMenu = false;
            } else {
                // gets accessible tools by user
                $currentRoles = $this->utils->getRoles($this->tokenStorage->getToken());
                $orderedTools = $this->toolManager->getOrderedToolsByWorkspaceAndRoles($workspace, $currentRoles);
            }
        }

        $current = null;
        if ('claro_workspace_open_tool' === $request->get('_route')) {
            $params = $request->get('_route_params');
            if (!empty($params['toolName'])) {
                $current = $params['toolName'];
            }
        }

        // mega hack to make the resource manager active when inside a resource
        if (in_array($request->get('_route'), ['claro_resource_show', 'claro_resource_show_short'])) {
            $current = 'resource_manager';
        }

        return [
            'current' => $current,
            'tools' => array_values(array_map(function (OrderedTool $orderedTool) use ($workspace) { // todo : create a serializer
                return [
                    'icon' => $orderedTool->getTool()->getClass(),
                    'name' => $orderedTool->getTool()->getName(),
                    'open' => ['claro_workspace_open_tool', ['workspaceId' => $workspace->getId(), 'toolName' => $orderedTool->getTool()->getName()]],
                ];
            }, $orderedTools)),
            'workspace' => $workspace,
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
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
     * )
     *
     * Opens a tool.
     *
     * @param string    $toolName
     * @param Workspace $workspace
     *
     * @return Response
     */
    public function openToolAction($toolName, Workspace $workspace, Request $request)
    {
        $this->assertIsGranted($toolName, $workspace);
        $this->forceWorkspaceLang($workspace, $request);
        $event = $this->eventDispatcher->dispatch('open_tool_workspace_'.$toolName, new DisplayToolEvent($workspace));
        $this->eventDispatcher->dispatch('log', new LogWorkspaceToolReadEvent($workspace, $toolName));
        $this->eventDispatcher->dispatch('log', new LogWorkspaceEnterEvent($workspace));
        // Add workspace to recent workspaces if user is not Usurped
        if ('anon.' !== $this->tokenStorage->getToken()->getUser() && !$this->isUsurpator($this->tokenStorage->getToken())) {
            $this->workspaceManager->addRecentWorkspaceForUser($this->tokenStorage->getToken()->getUser(), $workspace);
        }

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/open",
     *     name="claro_workspace_open",
     *     options={"expose"=true}
     * )
     *
     * @param Workspace $workspace
     *
     * @throws AccessDeniedException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function openAction($workspaceId, Request $request)
    {
        //getObject allows to find by id or uuid. Id doint both
        $workspace = $this->om->getObject(['id' => $workspaceId], Workspace::class);
        $this->assertIsGranted('OPEN', $workspace);
        $this->forceWorkspaceLang($workspace, $request);
        $options = $workspace->getOptions();

        if (!is_null($options)) {
            $details = $options->getDetails();

            if (isset($details['use_workspace_opening_resource']) &&
                $details['use_workspace_opening_resource'] &&
                isset($details['workspace_opening_resource']) &&
                !empty($details['workspace_opening_resource'])
            ) {
                $resourceNode = $this->resourceManager->getById($details['workspace_opening_resource']);

                if (!is_null($resourceNode)) {
                    $this->session->set('isDesktop', false);
                    $route = $this->router->generate(
                        'claro_resource_show',
                        [
                            'id' => $resourceNode->getUuid(),
                            'type' => $resourceNode->getResourceType()->getName(),
                        ]
                    );

                    return new RedirectResponse($route);
                }
            } elseif (isset($details['opening_type']) && 'tool' === $details['opening_type'] && isset($details['opening_target'])) {
                $route = $this->router->generate(
                    'claro_workspace_open_tool',
                    [
                        'toolName' => $details['opening_target'],
                        'workspaceId' => $workspaceId,
                    ]
                );

                return new RedirectResponse($route);
            }
        }

        $tool = $this->workspaceManager->getFirstOpenableTool($workspace);
        //small hack for administrators otherwise they can't open it
        $toolName = $tool ? $tool->getName() : 'home';

        $route = $this->router->generate(
            'claro_workspace_open_tool',
            [
                'workspaceId' => $workspace->getId(),
                'toolName' => $toolName,
            ]
        );

        return new RedirectResponse($route);
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
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
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
        if (true === $isGranted) {
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
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
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
        if ('' === $wsSearch) {
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
            if ('ROLE_USURPATE_WORKSPACE_ROLE' === $role->getRole() || $role instanceof SwitchUserRole) {
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

    private function forceWorkspaceLang(Workspace $workspace, Request $request)
    {
        if ($workspace->getLang()) {
            $request->setLocale($workspace->getLang());
            //not sure if both lines are needed
            $this->translator->setLocale($workspace->getLang());
        }
    }
}

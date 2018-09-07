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
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\TransferManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This controller is able to:
 * - list/create/delete/show workspaces.
 * - return some users/groups list (ie: (un)registered users to a workspace).
 * - add/delete users/groups to a workspace.
 *
 * @EXT\Route("/workspaces", options={"expose" = true})
 */
class WorkspaceController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var RoleManager */
    private $roleManager;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var SessionInterface */
    private $session;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ToolManager */
    private $toolManager;
    /** @var TranslatorInterface */
    private $translator;
    /** @var Utilities */
    private $utils;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var ObjectManager */
    private $om;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var TransferManager */
    private $transferManager;

    /**
     * WorkspaceController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"        = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"      = @DI\Inject("event_dispatcher"),
     *     "resourceManager"      = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"          = @DI\Inject("claroline.manager.role_manager"),
     *     "router"               = @DI\Inject("router"),
     *     "session"              = @DI\Inject("session"),
     *     "tokenStorage"         = @DI\Inject("security.token_storage"),
     *     "toolManager"          = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"           = @DI\Inject("translator"),
     *     "utils"                = @DI\Inject("claroline.security.utilities"),
     *     "workspaceManager"     = @DI\Inject("claroline.manager.workspace_manager"),
     *     "parametersSerializer" = @DI\Inject("claroline.serializer.parameters"),
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager"),
     *     "transferManager"      = @DI\Inject("claroline.manager.transfer_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param EventDispatcherInterface      $eventDispatcher
     * @param ResourceManager               $resourceManager
     * @param RoleManager                   $roleManager
     * @param UrlGeneratorInterface         $router
     * @param SessionInterface              $session
     * @param TokenStorageInterface         $tokenStorage
     * @param ToolManager                   $toolManager
     * @param TranslatorInterface           $translator
     * @param Utilities                     $utils
     * @param WorkspaceManager              $workspaceManager
     * @param ParametersSerializer          $parametersSerializer
     * @param ObjectManager                 $om
     * @param TransferManager               $transferManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        Utilities $utils,
        WorkspaceManager $workspaceManager,
        ParametersSerializer $parametersSerializer,
        ObjectManager $om,
        TransferManager $transferManager
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->utils = $utils;
        $this->workspaceManager = $workspaceManager;
        $this->parametersSerializer = $parametersSerializer;
        $this->om = $om;
        $this->transferManager = $transferManager;
    }

    /**
     * @EXT\Route("/list", name="claro_workspace_list")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return array
     */
    public function listAction()
    {
        return ['parameters' => $this->parametersSerializer->serialize()];
    }

    /**
     * @EXT\Route("/list/currentuser", name="claro_workspace_by_user")
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return array
     */
    public function listByUserAction()
    {
        return ['parameters' => $this->parametersSerializer->serialize()];
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
     * Opens a tool.
     *
     * @EXT\Route("/{workspaceId}/open/tool/{toolName}", name="claro_workspace_open_tool")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
     * )
     *
     * @param string    $toolName
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return Response
     */
    public function openToolAction($toolName, Workspace $workspace, Request $request)
    {
        $this->assertIsGranted($toolName, $workspace);
        $this->forceWorkspaceLang($workspace, $request);

        /** @var DisplayToolEvent $event */
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
     * @EXT\Route("/{workspaceId}/open", name="claro_workspace_open")
     *
     * @param int     $workspaceId - the id or uuid of the WS to open
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function openAction($workspaceId, Request $request)
    {
        /** @var Workspace $workspace */
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

                    return new RedirectResponse(
                        $this->router->generate('claro_resource_show', [
                            'id' => $resourceNode->getUuid(),
                            'type' => $resourceNode->getResourceType()->getName(),
                        ])
                    );
                }
            } elseif (isset($details['opening_type']) && 'tool' === $details['opening_type'] && isset($details['opening_target'])) {
                return new RedirectResponse(
                    $this->router->generate('claro_workspace_open_tool', [
                        'toolName' => $details['opening_target'],
                        'workspaceId' => $workspaceId,
                    ])
                );
            }
        }

        $tool = $this->workspaceManager->getFirstOpenableTool($workspace);
        //small hack for administrators otherwise they can't open it
        $toolName = $tool ? $tool->getName() : 'home';

        return new RedirectResponse(
            $this->router->generate('claro_workspace_open_tool', [
                'workspaceId' => $workspace->getId(),
                'toolName' => $toolName,
            ])
        );
    }

    /**
     * Adds a workspace to the favourite list.
     *
     * @EXT\Route("/{workspaceId}/update/favourite", name="claro_workspace_update_favourite")
     * @EXT\Method("POST")
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

        $resultWorkspace = null;
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
     * @EXT\Route("/{workspace}/export", name="claro_workspace_export")
     *
     * @param Workspace $workspace
     *
     * @return StreamedResponse
     */
    public function exportAction(Workspace $workspace)
    {
        $archive = $this->transferManager->export($workspace);

        $fileName = $workspace->getCode().'.zip';
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
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        return $response->send();
    }

    private function isUsurpator(TokenInterface $token = null)
    {
        if ($token) {
            foreach ($token->getRoles() as $role) {
                if ('ROLE_USURPATE_WORKSPACE_ROLE' === $role->getRole() || $role instanceof SwitchUserRole) {
                    return true;
                }
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

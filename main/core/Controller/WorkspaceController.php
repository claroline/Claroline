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
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/workspaces", options={"expose" = true})
 */
class WorkspaceController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ToolManager */
    private $toolManager;
    /** @var TranslatorInterface */
    private $translator;
    /** @var Utilities */
    private $utils;
    /** @var WorkspaceManager */
    private $manager;
    /** @var WorkspaceRestrictionsManager */
    private $restrictionsManager;

    /**
     * WorkspaceController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"     = @DI\Inject("event_dispatcher"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"          = @DI\Inject("translator"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "manager"             = @DI\Inject("claroline.manager.workspace_manager"),
     *     "restrictionsManager" = @DI\Inject("claroline.manager.workspace_restrictions")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param EventDispatcherInterface      $eventDispatcher
     * @param TokenStorageInterface         $tokenStorage
     * @param ToolManager                   $toolManager
     * @param TranslatorInterface           $translator
     * @param Utilities                     $utils
     * @param WorkspaceManager              $manager
     * @param WorkspaceRestrictionsManager  $restrictionsManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        Utilities $utils,
        WorkspaceManager $manager,
        WorkspaceRestrictionsManager $restrictionsManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->utils = $utils;
        $this->manager = $manager;
        $this->restrictionsManager = $restrictionsManager;
    }

    /**
     * @EXT\Route("/{workspaceId}", name="claro_workspace_open")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param int     $workspaceId - the id or uuid of the WS to open
     * @param User    $user
     * @param Request $request
     *
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function openAction($workspaceId, User $user = null, Request $request)
    {
        /** @var Workspace $workspace */
        $workspace = $this->om->getObject(['id' => $workspaceId], Workspace::class);
        if (!$workspace) {
            throw new NotFoundHttpException('Workspace not found');
        }

        // adds missing tools in the opened workspace
        // it's done before the rights check, in case user should have access to a missing tool
        $this->toolManager->addMissingWorkspaceTools($workspace);

        // switch to the workspace locale if needed
        $this->forceWorkspaceLang($workspace, $request);

        $accessErrors = $this->restrictionsManager->getErrors($workspace, $user);
        if (empty($accessErrors) || $this->manager->isManager($workspace, $this->tokenStorage->getToken())) {
            // Log workspace opening
            $this->eventDispatcher->dispatch('log', new LogWorkspaceEnterEvent($workspace));

            // Add workspace to recent workspaces if user is not Usurped
            if ($user && !$this->manager->isImpersonated($this->tokenStorage->getToken())) {
                $this->manager->addRecentWorkspaceForUser($user, $workspace);
            }

            // Get the list of enabled workspace tool
            if ($this->manager->isManager($workspace, $this->tokenStorage->getToken())) {
                // gets all available tools
                $orderedTools = $this->toolManager->getOrderedToolsByWorkspace($workspace);
            } else {
                // gets accessible tools by user
                $currentRoles = $this->utils->getRoles($this->tokenStorage->getToken());
                $orderedTools = $this->toolManager->getOrderedToolsByWorkspaceAndRoles($workspace, $currentRoles);
            }

            return new JsonResponse([
                'impersonated' => $this->manager->isImpersonated($this->tokenStorage->getToken()),
                // append access restrictions to the loaded data if any
                // to let the manager knows that other users can not enter the workspace
                'accessErrors' => $accessErrors,
                'userProgression' => null,
                'tools' => array_values(array_map(function (OrderedTool $orderedTool) { // todo : create a serializer
                    return [
                        'icon' => $orderedTool->getTool()->getClass(),
                        'name' => $orderedTool->getTool()->getName(),
                    ];
                }, $orderedTools)),
            ]);
        }

        return new JsonResponse($accessErrors, 403);
    }

    /**
     * Opens a tool.
     *
     * @EXT\Route("/{id}/tool/{toolName}", name="claro_workspace_open_tool")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "id", "strictId" = true},
     *      converter="strict_id"
     * )
     *
     * @param Workspace $workspace
     * @param string    $toolName
     *
     * @return Response
     */
    public function openToolAction(Workspace $workspace, $toolName)
    {
        $tool = $this->toolManager->getToolByName($toolName);
        if (!$tool) {
            throw new NotFoundHttpException('Tool not found');
        }

        if (!$this->authorization->isGranted($toolName, $workspace)) {
            throw new AccessDeniedException();
        }

        /** @var DisplayToolEvent $event */
        $event = $this->eventDispatcher->dispatch('open_tool_workspace_'.$toolName, new DisplayToolEvent($workspace));

        $this->eventDispatcher->dispatch('log', new LogWorkspaceToolReadEvent($workspace, $toolName));

        return new JsonResponse($event->getData());
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

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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogDesktopToolReadEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * User desktop.
 *
 * @EXT\Route("/desktop", options={"expose"=true})
 */
class DesktopController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ToolManager */
    private $toolManager;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /**
     * DesktopController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"  = @DI\Inject("event_dispatcher"),
     *     "serializer"       = @DI\Inject("claroline.api.serializer"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param EventDispatcherInterface      $eventDispatcher
     * @param SerializerProvider            $serializer
     * @param ToolManager                   $toolManager
     * @param WorkspaceManager              $workspaceManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EventDispatcherInterface $eventDispatcher,
        SerializerProvider $serializer,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * Opens the desktop.
     *
     * @EXT\Route("/", name="claro_desktop_open")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function openAction(User $currentUser = null)
    {
        // TODO : manage anonymous. This will break like this imo but they need to have access to tools opened to them.
        if (empty($currentUser)) {
            throw new AccessDeniedException();
        }

        $tools = $this->toolManager->getDisplayedDesktopOrderedTools($currentUser);
        if (0 === count($tools)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse([
            'userProgression' => null,
            'tools' => array_values(array_map(function (Tool $orderedTool) {
                return [
                    'icon' => $orderedTool->getClass(),
                    'name' => $orderedTool->getName(),
                ];
            }, $tools)),
        ]);
    }

    /**
     * Opens a tool.
     *
     * @EXT\Route("/tool/{toolName}", name="claro_desktop_open_tool")
     *
     * @param string $toolName
     *
     * @return Response
     */
    public function openToolAction($toolName)
    {
        $tool = $this->toolManager->getToolByName($toolName);
        if (!$tool) {
            throw new NotFoundHttpException('Tool not found');
        }

        if (!$this->authorization->isGranted('OPEN', $tool)) {
            throw new AccessDeniedException();
        }

        /** @var DisplayToolEvent $event */
        $event = $this->eventDispatcher->dispatch('open_tool_desktop_'.$toolName, new DisplayToolEvent());

        $this->eventDispatcher->dispatch('log', new LogDesktopToolReadEvent($toolName));

        return new JsonResponse($event->getData());
    }

    /**
     * Gets the current user history.
     *
     * @EXT\Route("/history", name="claro_desktop_history_get")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $currentUser
     *
     * @return JsonResponse
     */
    public function getHistoryAction(User $currentUser = null)
    {
        $workspaces = [];
        if ($currentUser instanceof User) {
            $workspaces = $this->workspaceManager->getRecentWorkspaceForUser($currentUser, $currentUser->getRoles());
        }

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
        }, $workspaces));
    }
}

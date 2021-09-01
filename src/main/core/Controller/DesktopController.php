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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Tool\AbstractTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * User desktop.
 *
 * @Route("/desktop", options={"expose"=true})
 */
class DesktopController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ToolManager */
    private $toolManager;

    /** @var StrictDispatcher */
    private $strictDispatcher;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $config,
        SerializerProvider $serializer,
        ToolManager $toolManager,
        StrictDispatcher $strictDispatcher
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->toolManager = $toolManager;
        $this->strictDispatcher = $strictDispatcher;
    }

    /**
     * Opens the desktop.
     *
     * @Route("/", name="claro_desktop_open")
     */
    public function openAction(): JsonResponse
    {
        /** @var GenericDataEvent $event */
        $event = $this->strictDispatcher->dispatch('desktop.open', GenericDataEvent::class);

        return new JsonResponse(array_merge($event->getResponse() ?? [], [
            'userProgression' => null,
            // get all enabled tools for the desktop, even those inaccessible to the current user
            // this will allow the ui to know if a user try to access a closed tool or an non existent one.
            'tools' => array_values(array_map(function (OrderedTool $orderedTool) {
                return $this->serializer->serialize($orderedTool, [Options::SERIALIZE_MINIMAL]);
            }, $this->toolManager->getOrderedToolsByDesktop())),
            'shortcuts' => $this->config->getParameter('desktop.shortcuts') ?? [],
        ]));
    }

    /**
     * Opens a tool.
     *
     * @Route("/tool/{toolName}", name="claro_desktop_open_tool")
     */
    public function openToolAction(string $toolName): JsonResponse
    {
        $orderedTool = $this->toolManager->getOrderedTool($toolName, Tool::DESKTOP);
        if (!$orderedTool) {
            throw new NotFoundHttpException(sprintf('Tool "%s" not found', $toolName));
        }

        if (!$this->authorization->isGranted('OPEN', $orderedTool)) {
            throw new AccessDeniedException();
        }

        $currentUser = $this->tokenStorage->getToken()->getUser();
        $eventParams = [
            null,
            $currentUser instanceof User ? $currentUser : null,
            AbstractTool::DESKTOP,
            $toolName,
        ];

        $this->strictDispatcher->dispatch(
            ToolEvents::TOOL_OPEN,
            OpenToolEvent::class,
            $eventParams
        );

        /** @var OpenToolEvent $event */
        $event = $this->strictDispatcher->dispatch(
            'open_tool_desktop_'.$toolName,
            OpenToolEvent::class,
            $eventParams
        );

        return new JsonResponse(array_merge($event->getData(), [
            'data' => $this->serializer->serialize($orderedTool),
        ]));
    }

    /**
     * Lists desktop tools accessible by the current user.
     *
     * @Route("/tools", name="claro_desktop_tools")
     */
    public function listToolsAction(): JsonResponse
    {
        $orderedTools = $this->toolManager->getOrderedToolsByDesktop($this->tokenStorage->getToken()->getRoleNames());

        return new JsonResponse(array_values(array_map(function (OrderedTool $orderedTool) {
            return $this->serializer->serialize($orderedTool, [Options::SERIALIZE_MINIMAL]);
        }, $orderedTools)));
    }
}

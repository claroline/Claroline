<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\PlannedNotificationBundle\Manager\PlannedNotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class PlannedNotificationListener
{
    /** @var PlannedNotificationManager */
    private $manager;
    /** @var HttpKernelInterface */
    private $httpKernel;
    /** @var Request */
    private $request;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "manager"      = @DI\Inject("claroline.manager.planned_notification_manager"),
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param PlannedNotificationManager $manager
     * @param HttpKernelInterface        $httpKernel
     * @param RequestStack               $requestStack
     * @param TokenStorageInterface      $tokenStorage
     */
    public function __construct(
        PlannedNotificationManager $manager,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager = $manager;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @DI\Observe("open_tool_workspace_claroline_planned_notification_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceToolOpen(DisplayToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolinePlannedNotificationBundle:PlannedNotificationTool:toolOpen';
        $params['workspace'] = $event->getWorkspace()->getId();
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("log", priority=1)
     *
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        if ($event instanceof LogRoleSubscribeEvent) {
            $role = $event->getRole();
            $workspace = $role->getWorkspace();

            if (!empty($workspace)) {
                $this->manager->generateScheduledTasks(
                    $event->getActionKey(),
                    $event->getReceiver(),
                    $workspace,
                    $event->getReceiverGroup(),
                    $role
                );
            }
        } elseif ($event instanceof LogWorkspaceEnterEvent) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ('anon.' !== $user) {
                $this->manager->generateScheduledTasks($event->getAction(), $user, $event->getWorkspace());
            }
        }
    }
}

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
use Claroline\PlannedNotificationBundle\Manager\PlannedNotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

    /**
     * @DI\InjectParams({
     *     "manager"      = @DI\Inject("claroline.manager.planned_notification_manager"),
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack" = @DI\Inject("request_stack")
     * })
     *
     * @param PlannedNotificationManager $manager
     * @param HttpKernelInterface        $httpKernel
     * @param RequestStack               $requestStack
     */
    public function __construct(
        PlannedNotificationManager $manager,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack
    ) {
        $this->manager = $manager;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
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
     * @DI\Observe("log")
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
                    $workspace,
                    $event->getActionKey(),
                    $event->getReceiver(),
                    $event->getReceiverGroup(),
                    $role
                );
            }
        }
    }
}

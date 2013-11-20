<?php

namespace Innova\PathBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;

class ToolListener extends ContainerAware
{
     /**
     * @DI\Observe("open_path")
     */
    public function onPathOpen(OpenResourceEvent $event)
    {   
        $pathId = $event->getResource()->getResourceNode()->getId();
        $workspaceId = $event->getResource()->getResourceNode()->getWorkspace()->getId();
        
        $subRequest = $this->container->get('request')->duplicate( array(), array('pathId' => $pathId, 'workspaceId' => $workspaceId), array("_controller" => 'InnovaPathBundle:Path:showPath'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        $id = $event->getWorkspace()->getId();
        $subRequest = $this->container->get('request')->duplicate(array('id' => $id), array(), array("_controller" => 'innova.controller.path:fromWorkspaceAction'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}

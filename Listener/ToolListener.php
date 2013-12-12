<?php

namespace Innova\PathBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;

class ToolListener extends ContainerAware
{
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        $id = $event->getWorkspace()->getId();
        $subRequest = $this->container->get('request')->duplicate(array('id' => $id), array(), array("_controller" => 'innova.controller.path:fromWorkspaceAction'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }

   
    public function onPathOpen(OpenResourceEvent $event)
    {
        $path = $event->getResource();
	    $route = $this->container
	        ->get('router')
	        ->generate(
	            'innova_path_player_index',
	            array(
	            	'workspaceId' => $path->getResourceNode()->getWorkspace()->getId(),
	            	'pathId' => $path->getId(),
	            	'stepId' => $path->getRootStep()->getId()
	            )
	        );
	    $event->setResponse(new RedirectResponse($route));
	    $event->stopPropagation();
    }
}

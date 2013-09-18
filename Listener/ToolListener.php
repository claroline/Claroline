<?php

namespace Innova\PathBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ToolListener extends ContainerAware
{
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {

        $id = $event->getWorkspace()->getId();
        $subRequest = $this->container->get('request')->duplicate(array('id'=>$id), array(), array("_controller" => 'InnovaPathBundle:Path:fromWorkspace'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }

    public function onDesktopOpen(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate(array(), array(), array("_controller" => 'InnovaPathBundle:Path:fromDesktop'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST); 
        $event->setContent($response->getContent());
    }

}
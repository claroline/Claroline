<?php

namespace  Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;

class CoreLogListener extends ContainerAware
{
    public function onWorkspaceDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent($this->renderForWorkspace($event->getWorkspace()));
        $event->stopPropagation();
    }

    public function onDesktopDisplay(DisplayWidgetEvent $event)
    {
        $event->setContent($this->renderForDesktop());
        $event->stopPropagation();
    }

    private function renderForWorkspace($workspace)
    {
        $logs = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findLastLogs($this->container->get('security.context')->getToken()->getUser(), $workspace);

        return $this->container
            ->get('templating')
            ->render('ClarolineCoreBundle:Log:view_list.html.twig', array('logs' => $logs));
    }

    private function renderForDesktop()
    {
        $logs = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Logger\Log')
            ->findLastLogs($this->container->get('security.context')->getToken()->getUser());

        return $this->container
            ->get('templating')
            ->render('ClarolineCoreBundle:Log:view_list.html.twig', array('logs' => $logs));
    }
}


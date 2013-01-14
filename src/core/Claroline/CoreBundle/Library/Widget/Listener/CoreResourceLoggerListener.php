<?php

namespace Claroline\CoreBundle\Library\Widget\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;

class CoreResourceLoggerListener extends ContainerAware
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
            ->getRepository('Claroline\CoreBundle\Entity\Logger\ResourceLogger')->getLastLogs($this->container->get('security.context')->getToken()->getUser(), $workspace);

        return $this->container->get('templating')->render('ClarolineCoreBundle:Widget:resource_events.html.twig', array('logs' => $logs));
    }

    private function renderForDesktop()
    {
        $logs = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Logger\ResourceLogger')->getLastLogs($this->container->get('security.context')->getToken()->getUser());
        
        return $this->container->get('templating')->render('ClarolineCoreBundle:Widget:resource_events.html.twig', array('logs' => $logs));
    }
}


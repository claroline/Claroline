<?php

namespace Claroline\CoreBundle\Library\Widget\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;

class CoreResourceLoggerListener extends ContainerAware
{
    public function onDisplay(DisplayWidgetEvent $event)
    {
        if($event->getWorkspace()!== null){
            $event->setContent($this->renderForWorkspace($event->getWorkspace()));
            $event->stopPropagation();
            return;
        } else {
            $event->setContent($this->renderForDashboard());
        }
    }

    private function renderForWorkspace($workspace)
    {
        return('not implemented yet');
    }

    private function renderForDashboard()
    {
        $logs = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Logger\ResourceLogger')->getLastLogs($this->container->get('security.context')->getToken()->getUser());

        return $this->container->get('templating')->render('ClarolineCoreBundle:Widget:resource_events.html.twig', array('logs' => $logs));
    }
}


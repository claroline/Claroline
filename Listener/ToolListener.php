<?php

namespace Innova\PathBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class ToolListener extends ContainerAware
{    
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        // Retrieve data
        $workspace = $event->getWorkspace();
        $paths = $this->container->get('innova_path.manager.path')->findAllFromWorkspace($workspace);
        
        // Build response content
        $content = $this->container->get('templating')->render(
            'InnovaPathBundle::index.html.twig',
            array(
                'workspace' => $workspace,
                'paths' => $paths,
            )
        );
        
        // Send content to display to dispatcher through event
        $event->setContent($content);
        $event->stopPropagation();
        
        return $this;
    }
}

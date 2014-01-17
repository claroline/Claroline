<?php

namespace Innova\PathBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Symfony\Component\Templating\EngineInterface;
use Innova\PathBundle\Manager\PathManager;

class ToolListener
{
    /**
     * Template engine
     * @var \Symfony\Component\Templating\EngineInterface $templating
     */
    protected $templating;
    
    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathManager $pathManager
     */
    protected $pathManager;
    
    /**
     * Class constructor
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    public function __construct(
        EngineInterface $templating, 
        PathManager     $pathManager)
    {
        $this->templating = $templating;
        $this->pathManager = $pathManager;
    }
    
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        // Retrieve data
        $workspace = $event->getWorkspace();
        $paths = $this->pathManager->findAllFromWorkspace($workspace);
        
        // Build response content
        $content = $this->templating->render(
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

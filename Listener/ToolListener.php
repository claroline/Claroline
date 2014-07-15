<?php

namespace Innova\PathBundle\Listener;

use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Manager\PathManager;
use Symfony\Bundle\TwigBundle\TwigEngine;

use Claroline\CoreBundle\Event\DisplayToolEvent;

class ToolListener
{
    /**
     * Template engine
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $templating;

    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    private $pathManager;

    /**
     * Class constructor
     * @param \Symfony\Bundle\TwigBundle\TwigEngine  $templating
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    public function __construct(
        TwigEngine  $templating,
        PathManager $pathManager)
    {
        $this->templating = $templating;
        $this->pathManager = $pathManager;
    }

    /**
     * List paths of the Workspace on Tool open
     * @param DisplayToolEvent $event
     * @return $this
     */
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        // Retrieve data
        $paths = $this->pathManager->findAccessibleByUser($event->getWorkspace());

        // Build response content
        $content = $this->templating->render(
            'InnovaPathBundle::index.html.twig',
            array (
                'canCreate' => $this->pathManager->isAllow('CREATE', new Path()),
                'workspace' => $event->getWorkspace(),
                'paths' => $paths,
            )
        );
        
        // Send content to display to dispatcher through event
        $event->setContent($content);
        $event->stopPropagation();
        
        return $this;
    }
}

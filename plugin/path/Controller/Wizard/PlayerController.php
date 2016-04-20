<?php

namespace Innova\PathBundle\Controller\Wizard;

use Innova\PathBundle\Manager\PathManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;

/**
 * Player controller.
 *
 * @author Innovalangues <contact@innovalangues.net>
 * 
 * @Route(
 *      "player",
 *      name    = "innova_path_player",
 *      service = "innova_path.controller.path_player"
 * )
 */
class PlayerController
{
    /**
     * Object manager.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Path manager.
     *
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Class constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param \Innova\PathBundle\Manager\PathManager     $pathManager
     */
    public function __construct(
        ObjectManager $objectManager,
        PathManager   $pathManager)
    {
        $this->om = $objectManager;
        $this->pathManager = $pathManager;
    }

    /**
     * Display path player.
     *
     * @param \Innova\PathBundle\Entity\Path\Path $path
     *
     * @return array
     *
     * @Route(
     *      "/{id}",
     *      name     = "innova_path_player_wizard",
     *      defaults = { "stepId" = null },
     *      options  = { "expose" = true }
     * )
     * @Template("InnovaPathBundle:Wizard:player.html.twig")
     */
    public function displayAction(Path $path)
    {
        // Check User credentials
        $this->pathManager->checkAccess('OPEN', $path);

        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array(
            '_resource' => $path,
            'workspace' => $path->getWorkspace(),
            'userProgression' => $this->pathManager->getUserProgression($path),
            'resourceIcons' => $resourceIcons,
            'editEnabled' => $this->pathManager->isAllow('EDIT', $path),
        );
    }
}

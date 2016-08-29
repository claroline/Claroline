<?php

namespace Innova\PathBundle\Controller\Wizard;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Player controller.
 *
 * @author Innovalangues <contact@innovalangues.net>
 *
 * @Route(
 *      "player",
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
     * @var UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Class constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param \Innova\PathBundle\Manager\PathManager     $pathManager
     * @param UserProgressionManager                     $userProgressionManager
     */
    public function __construct(
        ObjectManager $objectManager,
        PathManager   $pathManager,
        UserProgressionManager $userProgressionManager)
    {
        $this->om = $objectManager;
        $this->pathManager = $pathManager;
        $this->userProgressionManager = $userProgressionManager;
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
     *      options  = { "expose" = true }
     * )
     * @Template("InnovaPathBundle:Wizard:player.html.twig")
     */
    public function displayAction(Path $path)
    {
        // Check User credentials
        $this->pathManager->checkAccess('OPEN', $path);

        return [
            '_resource' => $path,
            'workspace' => $path->getWorkspace(),
            'userProgression' => $this->pathManager->getUserProgression($path),
            'editEnabled' => $this->pathManager->isAllow('EDIT', $path),
            'totalProgression' => $this->userProgressionManager->calculateUserProgressionInPath($path),
        ];
    }
}

<?php

namespace Innova\PathBundle\Controller\Wizard;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Doctrine\Common\Persistence\ObjectManager;

use Innova\PathBundle\Entity\Path\Path;

/**
 * Player controller
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
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->om = $objectManager;
    }

    /**
     * Display path player
     * @param  \Innova\PathBundle\Entity\Path\Path $path
     * @return array
     *
     * @Route(
     *      "/{id}",
     *      name     = "innova_path_player_wizard",
     *      defaults = { "stepId" = null },
     *      options  = { "expose" = true }
     * )
     * @Method("GET")
     * @Template("InnovaPathBundle:Wizard:player.html.twig")
     */
    public function displayAction(Path $path)
    {
        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array (
            '_resource' => $path,
            'resourceIcons' => $resourceIcons,
        );
    }
}

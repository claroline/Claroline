<?php

namespace Innova\PathBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Manager\PathManager;
// Controller dependencies
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class ManageResultsController.
 *
 * @Route(
 *      "/manage",
 *      name    = "innova_path_manageresults",
 *      service = "innova_path.controller.manageresults"
 * )
 */
class ManageResultsController
{
    /**
     * Current path manager.
     *
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Object manager.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Class constructor.
     *
     * @param \Innova\PathBundle\Manager\PathManager     $pathManager
     * @param \Doctrine\Common\Persistence\ObjectManager $om
     */
    public function __construct(
        PathManager             $pathManager,
        ObjectManager           $objectManager)
    {
        $this->pathManager = $pathManager;
        $this->om = $objectManager;
    }

    /**
     * Display dashboard for path of users.
     *
     * @Route(
     *     "/userpath/{id}",
     *     name         = "innova_path_manage_results",
     *     requirements = {"id" = "\d+"},
     *     options      = {"expose" = true}
     * )
     * @Method("GET")
     * @ParamConverter("path", class="InnovaPathBundle:Path\Path", options={"id" = "id"})
     * @Template("InnovaPathBundle::manageResults.html.twig")
     */
    public function displayStepUnlockAction(Path $path)
    {
        //prevent direct access
        $this->pathManager->checkAccess('EDIT', $path);

        $data = [];
        $workspace = $path->getWorkspace();

        //retrieve users having access to the WS
        //TODO Optimize
        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findUsersByWorkspace($workspace);
        $userdata = [];
        //for all users in the WS
        foreach ($users as $user) {
            //get their progression
            $userdata[] = [
                'user' => $user,
                'progression' => $this->pathManager->getUserProgression($path, $user),
                'locked' => $this->pathManager->getPathLockedProgression($path),
            ];
        }
        $data = [
            'path' => $path,
            'userdata' => $userdata,
        ];

        return [
            '_resource' => $path,
            'workspace' => $workspace,
            'data' => $data,
        ];
    }
}

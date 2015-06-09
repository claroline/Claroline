<?php

namespace Innova\PathBundle\Controller;

use Innova\PathBundle\Manager\UserProgressionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Innova\PathBundle\Entity\Step;

/**
 * Class UserProgressionController
 *
 * @Route(
 *      "/progression",
 *      name    = "innova_path_progression",
 *      service = "innova_path.controller.user_progression"
 * )
 */
class UserProgressionController
{
    /**
     * User Progression manager
     * @var \Innova\PathBundle\Manager\UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Class constructor
     * @param \Innova\PathBundle\Manager\UserProgressionManager $userProgressionManager
     */
    public function __construct(UserProgressionManager $userProgressionManager)
    {
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Log a new action from User (mark the the  step as to do)
     * @param \Innova\PathBundle\Entity\Step $step
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/",
     *     name    = "innova_path_progression_create",
     *     options = { "expose" = true }
     * )
     * @Method("POST")
     */
    public function createAction(Step $step)
    {
        $progression = $this->userProgressionManager->create($step);

        return new JsonResponse($progression);
    }

    /**
     * Update progression of a User
     * @param \Innova\PathBundle\Entity\Step $step
     * @param string $status
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/",
     *     name         = "innova_path_progression_update",
     *     options      = { "expose" = true }
     * )
     * @Method("PUT")
     */
    public function updateAction(Step $step, $status)
    {
        return new JsonResponse(array (

        ));
    }
}

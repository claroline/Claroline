<?php

namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserProgressionController.
 *
 * @Route(
 *     "/step/{id}/progression",
 *     service      = "innova_path.controller.user_progression",
 *     requirements = {"id" = "\d+"},
 *     options      = { "expose" = true }
 * )
 * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
 */
class UserProgressionController
{
    /**
     * User Progression manager.
     *
     * @var \Innova\PathBundle\Manager\UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Class constructor.
     *
     * @param \Innova\PathBundle\Manager\UserProgressionManager $userProgressionManager
     */
    public function __construct(UserProgressionManager $userProgressionManager)
    {
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Log a new action from User (mark the the  step as to do).
     *
     * @param User                           $user
     * @param \Innova\PathBundle\Entity\Step $step
     * @param Request                        $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "",
     *     name = "innova_path_progression_create"
     * )
     * @Method("POST")
     */
    public function createAction(User $user, Step $step, Request $request)
    {
        $status = $request->get('user_progression_status');
        $authorized = $request->get('user_progression_authorized');

        $progression = $this->userProgressionManager->create($step, $user, $status, $authorized);

        return new JsonResponse(['progression' => $progression]);
    }

    /**
     * Update progression of a User.
     *
     * @param User                           $user
     * @param \Innova\PathBundle\Entity\Step $step
     * @param Request                        $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "",
     *     name = "innova_path_progression_update"
     * )
     * @Method("PUT")
     */
    public function updateAction(User $user, Step $step, Request $request)
    {
        $status = $request->get('user_progression_status');
        $authorized = $request->get('user_progression_authorized');

        $progression = $this->userProgressionManager->update($step, $user, $status, $authorized);

        return new JsonResponse(['progression' => $progression]);
    }
}

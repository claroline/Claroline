<?php

namespace Innova\PathBundle\Controller;

use Innova\PathBundle\Manager\UserProgressionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\Step;

/**
 * Class UserProgressionController
 *
 * @Route(
 *      "/progression/{userId}/{stepId}",
 *      name         = "innova_path_progression",
 *      requirements = { "userId" = "\d+" },
 *      service      = "innova_path.controller.user_progression"
 * )
 * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"id" = "userId"})
 * @ParamConverter("step", class="InnovaPathBundle:Step",    options={"id" = "stepId"})
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
     * Log a new action from User (the first he can do is "see the Step")
     * @param \Innova\PathBundle\Entity\Step $step
     * @param \Claroline\CoreBundle\Entity\User $user
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/",
     *     name         = "innova_path_progression_create",
     *     requirements = { "id" = "\d+" },
     *     options      = { "expose" = true }
     * )
     * @Method("POST")
     */
    public function createAction(Step $step, User $user)
    {
        return new JsonResponse(array (

        ));
    }

    /**
     * Update progression of a User
     * @param \Innova\PathBundle\Entity\Step $step
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string $status
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/",
     *     name         = "innova_path_progression_update",
     *     requirements = { "stepId" = "\d+" },
     *     options      = { "expose" = true }
     * )
     * @Method("PUT")
     */
    public function updateAction(Step $step, User $user, $status)
    {
        return new JsonResponse(array (

        ));
    }
}

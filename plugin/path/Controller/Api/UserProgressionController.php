<?php

namespace Innova\PathBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/step/{id}/progression", options={"expose"=true})
 * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
 */
class UserProgressionController
{
    /**
     * @var UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * UserProgressionController constructor.
     *
     * @DI\InjectParams({
     *     "userProgressionManager" = @DI\Inject("innova_path.manager.user_progression")
     * })
     *
     * @param UserProgressionManager $userProgressionManager
     */
    public function __construct(UserProgressionManager $userProgressionManager)
    {
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Log a new action from User (mark the the  step as to do).
     *
     * @EXT\Route("", name="innova_path_progression_create")
     * @EXT\Method("POST")
     *
     * @param User    $user
     * @param Step    $step
     * @param Request $request
     *
     * @return JsonResponse
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
     * @EXT\Route("", name="innova_path_progression_update")
     * @EXT\Method("PUT")
     *
     * @param User    $user
     * @param Step    $step
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(User $user, Step $step, Request $request)
    {
        $status = $request->get('user_progression_status');
        $authorized = $request->get('user_progression_authorized');

        $progression = $this->userProgressionManager->update($step, $user, $status, $authorized);

        return new JsonResponse(['progression' => $progression]);
    }
}

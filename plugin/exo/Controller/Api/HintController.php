<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Manager\HintManager;

/**
 * Hint Controller.
 *
 * @EXT\Route(
 *     "/papers/{paperId}/hints/{id}",
 *     options={"expose"=true},
 *     defaults={"_format": "json"}
 * )
 * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
 * @EXT\ParamConverter("hint", class="UJMExoBundle:Hint")
 */
class HintController
{
    /**
     * @var HintManager
     */
    private $hintManager;

    /**
     * PaperController constructor.
     *
     * @DI\InjectParams({
     *     "hintManager" = @DI\Inject("ujm.exo.hint_manager")
     * })
     *
     * @param HintManager $hintManager
     */
    public function __construct(
        HintManager $hintManager)
    {
        $this->hintManager = $hintManager;
    }

    /**
     * Returns the value of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     * @EXT\Route(
     *     "",
     *     name="exercise_hint_show"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Paper $paper
     * @param Hint  $hint
     * @param User  $user
     *
     * @return JsonResponse
     */
    public function showHintAction(Paper $paper, Hint $hint, User $user = null)
    {
        $this->assertHasPaperAccess($paper, $user);

        if (!$this->hintManager->hasHint($paper, $hint)) {
            return new JsonResponse('Hint and paper are not related', 422);
        }

        return new JsonResponse($this->hintManager->viewHint($paper, $hint));
    }

    /**
     * Checks whether a User has access to a Paper
     * ATTENTION : As is, anonymous have access to all the other anonymous Papers !!!
     *
     * @param Paper     $paper
     * @param User|null $user
     */
    private function assertHasPaperAccess(Paper $paper, User $user = null)
    {
        if ($paper->getEnd() || $user !== $paper->getUser()) {
            throw new AccessDeniedHttpException();
        }
    }
}

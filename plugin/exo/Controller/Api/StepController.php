<?php

namespace UJM\ExoBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\ExerciseManager;
use UJM\ExoBundle\Manager\StepManager;

/**
 * Exercise Controller.
 *
 * @EXT\Route(
 *     "/exercises/{exerciseId}",
 *     options={"expose"=true},
 *     defaults={"_format": "json"}
 * )
 * @EXT\Method("GET")
 */
class StepController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var ExerciseManager
     */
    private $exerciseManager;

    /**
     * @var StepManager
     */
    private $stepManager;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager $exerciseManager
     * @param StepManager $stepManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        StepManager $stepManager
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->stepManager = $stepManager;
    }

    /**
     * Add a Step to the Exercise.
     *
     * @EXT\Route(
     *     "/{id}/step",
     *     name="exercise_step_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "id"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function addAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $step = $this->exerciseManager->addStep($exercise);

        return new JsonResponse($this->stepManager->exportStep($step));
    }

    /**
     * @EXT\Route(
     *     "/reorder",
     *     name="exercise_step_reorder",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "id"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function reorderAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);


        return new JsonResponse([]);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedHttpException();
        }
    }
}

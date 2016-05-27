<?php

namespace UJM\ExoBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;
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
 * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "id"}})
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
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager" = @DI\Inject("ujm.exo.exercise_manager"),
     *     "stepManager" = @DI\Inject("ujm.exo.step_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param StepManager                   $stepManager
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
     *     "/steps",
     *     name="exercise_step_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
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
     * Delete a Step from the Exercise.
     *
     * @EXT\Route(
     *     "/steps/{id}",
     *     name="exercise_step_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     *
     * @param Exercise $exercise
     * @param Step     $step
     *
     * @return JsonResponse
     */
    public function deleteStepAction(Exercise $exercise, Step $step)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->exerciseManager->deleteStep($exercise, $step);

        // Return updated list of steps
        return new JsonResponse($this->exerciseManager->exportSteps($exercise, false));
    }

    /**
     * Reorder the Steps of an Exercise.
     *
     * @EXT\Route(
     *     "/steps/reorder",
     *     name="exercise_step_reorder",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "id"}})
     *
     * @param Exercise $exercise
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function reorderAction(Exercise $exercise, Request $request)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $dataRaw = $request->getContent();
        if (empty($dataRaw)) {
            return new JsonResponse([
                'message' => 'No data sent.',
            ], 422);
        }

        $order = json_decode($dataRaw);
        if (!is_array($order)) {
            return new JsonResponse([
                'message' => 'Invalid data sent. Expected an array of Step IDs.',
            ], 422);
        }

        $errors = $this->exerciseManager->reorderSteps($exercise, $order);
        if (count($errors) !== 0) {
            return new JsonResponse($errors, 422);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Reorder the Questions of a Step.
     *
     * @EXT\Route(
     *     "/steps/{id}/questions/reorder",
     *     name="exercise_question_reorder",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "id"}})
     
     * @param Exercise $exercise
     * @param Step     $step
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function reorderQuestionsAction(Exercise $exercise, Step $step, Request $request)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $dataRaw = $request->getContent();
        if (empty($dataRaw)) {
            return new JsonResponse([
                'message' => 'No data sent.',
            ], 422);
        }

        $order = json_decode($dataRaw);
        if (!is_array($order)) {
            return new JsonResponse([
                'message' => 'Invalid data sent. Expected an array of Question IDs.',
            ], 422);
        }

        $errors = $this->stepManager->reorderQuestions($step, $order);
        if (count($errors) !== 0) {
            return new JsonResponse($errors, 422);
        }

        return new JsonResponse(null, 204);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedHttpException();
        }
    }
}

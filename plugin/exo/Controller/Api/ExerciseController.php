<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Exercise API Controller exposes REST API.
 *
 * @EXT\Route("/exercises", options={"expose"=true})
 */
class ExerciseController extends AbstractController
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
     * ExerciseController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager" = @DI\Inject("ujm_exo.manager.exercise")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
    }

    /**
     * Gets the full representation of an exercise (including solutions) in a JSON format.
     *
     * @EXT\Route("/{id}", name="exercise_get")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function getAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        return new JsonResponse(
            $this->exerciseManager->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS])
        );
    }

    /**
     * Updates an Exercise.
     *
     * @EXT\Route("/{id}", name="exercise_update")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     * @EXT\Method("PUT")
     *
     * @param Exercise $exercise
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function updateAction(Exercise $exercise, Request $request)
    {
        $this->assertHasPermission('EDIT', $exercise);

        $errors = [];

        $data = $this->decodeRequestData($request);

        if (null === $data) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update exercise
            try {
                $this->exerciseManager->update($exercise, $data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }

        // Exercise updated
        return new JsonResponse(null, 204);
    }

    /**
     * Publishes an exercise.
     *
     * @EXT\Route("/{id}/publish", name="exercise_publish")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function publishAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->exerciseManager->publish($exercise);

        return new JsonResponse(null, 204);
    }

    /**
     * Unpublishes an exercise.
     *
     * @EXT\Route("/{id}/unpublish", name="exercise_unpublish")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function unpublishAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $this->exerciseManager->unpublish($exercise);

        return new JsonResponse(null, 204);
    }

    /**
     * Get docimology data.
     *
     * @EXT\Route("/{id}/docimology", name="exercise_docimology")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function docimologyAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        return new JsonResponse([
                'exercise' => $this->exerciseManager->serialize($exercise, [Transfer::MINIMAL]),
                'statistics' => $this->exerciseManager->getStatistics($exercise, 100),
        ]);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}

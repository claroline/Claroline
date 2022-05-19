<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * @Route("/exercises")
 *
 * @todo : use Crud
 */
class ExerciseController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ExerciseManager */
    private $exerciseManager;

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
     * @Route("/{id}", name="exercise_get", methods={"GET"})
     * @EXT\ParamConverter("exercise", class="UJM\ExoBundle\Entity\Exercise", options={"mapping": {"id": "uuid"}})
     */
    public function getAction(Exercise $exercise): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $exercise->getResourceNode(), [], true);

        return new JsonResponse(
            $this->exerciseManager->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS])
        );
    }

    /**
     * Updates an Exercise.
     *
     * @Route("/{id}", name="exercise_update", methods={"PUT"})
     * @EXT\ParamConverter("exercise", class="UJM\ExoBundle\Entity\Exercise", options={"mapping": {"id": "uuid"}})
     */
    public function updateAction(Exercise $exercise, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $exercise->getResourceNode(), [], true);

        $errors = [];

        $data = $this->decodeRequest($request);

        if (null === $data) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update exercise
            try {
                $this->exerciseManager->update($exercise, $data);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (!empty($errors)) {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }

        // Exercise updated
        return new JsonResponse(
            $this->exerciseManager->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS])
        );
    }
}

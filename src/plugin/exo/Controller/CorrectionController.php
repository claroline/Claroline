<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\CorrectionManager;

/**
 * Correction API controller permits to a quiz creator to save scores and feedback
 * for answers to questions with manual correction.
 *
 * @EXT\ParamConverter("exercise", class="UJM\ExoBundle\Entity\Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
#[Route(path: '/exercises/{exerciseId}/correction')]
class CorrectionController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly CorrectionManager $correctionManager
    ) {
    }

    /**
     * Lists all questions with `manual` score rule that have answers to correct.
     */
    #[Route(path: '', name: 'exercise_correction_questions', methods: ['GET'])]
    public function listQuestionsToCorrectAction(Exercise $exercise): JsonResponse
    {
        $toCorrect = $this->isAdmin($exercise) ? $this->correctionManager->getToCorrect($exercise) : [];

        return new JsonResponse($toCorrect);
    }

    /**
     * Saves score & feedback for a bulk of answers.
     */
    #[Route(path: '/{questionId}', name: 'exercise_correction_save', methods: ['PUT'])]
    public function saveAction(Exercise $exercise, Request $request): JsonResponse
    {
        $this->isAdmin($exercise);

        $data = $this->decodeRequest($request);

        $errors = [];
        if (null === $data) {
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to save submitted correction
            try {
                $this->correctionManager->save($data);
            } catch (InvalidDataException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Correction saved
            return new JsonResponse(null, 204);
        }

        // Invalid data received
        return new JsonResponse($errors, 422);
    }

    private function isAdmin(Exercise $exercise): bool
    {
        if (!$this->authorization->isGranted('MANAGE_PAPERS', $exercise->getResourceNode())) {
            throw new AccessDeniedException();
        }

        return true;
    }
}

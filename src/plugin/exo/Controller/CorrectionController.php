<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
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
 * @Route("/exercises/{exerciseId}/correction")
 * @EXT\ParamConverter("exercise", class="UJM\ExoBundle\Entity\Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
class CorrectionController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var CorrectionManager */
    private $correctionManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CorrectionManager $correctionManager)
    {
        $this->authorization = $authorization;
        $this->correctionManager = $correctionManager;
    }

    /**
     * Lists all questions with `manual` score rule that have answers to correct.
     *
     * @Route("", name="exercise_correction_questions", methods={"GET"})
     */
    public function listQuestionsToCorrectAction(Exercise $exercise): JsonResponse
    {
        $toCorrect = $this->isAdmin($exercise) ? $this->correctionManager->getToCorrect($exercise) : [];

        return new JsonResponse($toCorrect);
    }

    /**
     * Saves score & feedback for a bulk of answers.
     *
     * @Route("/{questionId}", name="exercise_correction_save", methods={"PUT"})
     */
    public function saveAction(Exercise $exercise, Request $request): JsonResponse
    {
        if ($this->isAdmin($exercise)) {
            $data = $this->decodeRequest($request);

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
            } else {
                // Invalid data received
                return new JsonResponse($errors, 422);
            }
        }
    }

    private function isAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted('MANAGE_PAPERS', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        return true;
    }
}

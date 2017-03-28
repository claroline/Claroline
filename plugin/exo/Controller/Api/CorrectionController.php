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
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\CorrectionManager;

/**
 * Correction API controller permits to a quiz creator to save scores and feedback
 * for answers to questions with manual correction.
 *
 * @EXT\Route("/exercises/{exerciseId}/correction", options={"expose"=true})
 * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
class CorrectionController extends AbstractController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var CorrectionManager
     */
    private $correctionManager;

    /**
     * CorrectionController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"     = @DI\Inject("security.authorization_checker"),
     *     "correctionManager" = @DI\Inject("ujm_exo.manager.correction")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param CorrectionManager             $correctionManager
     */
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
     * @EXT\Route("", name="exercise_correction_questions")
     * @EXT\Method("GET")
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function listQuestionsToCorrectAction(Exercise $exercise)
    {
        if ($this->isAdmin($exercise)) {
            return new JsonResponse(
                $this->correctionManager->getToCorrect($exercise)
            );
        }
    }

    /**
     * Saves score & feedback for a bulk of answers.
     *
     * @EXT\Route("/{questionId}", name="exercise_correction_save")
     * @EXT\Method("PUT")
     *
     * @param Exercise $exercise
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function saveAction(Exercise $exercise, Request $request)
    {
        if ($this->isAdmin($exercise)) {
            $data = $this->decodeRequestData($request);

            if (null === $data) {
                $errors[] = [
                    'path' => '',
                    'message' => 'Invalid JSON data',
                ];
            } else {
                // Try to save submitted correction
                try {
                    $this->correctionManager->save($data);
                } catch (ValidationException $e) {
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

        if (!$this->authorization->isGranted('ADMINISTRATE', $collection) && !$this->authorization->isGranted('MANAGE_PAPERS', $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        return true;
    }
}

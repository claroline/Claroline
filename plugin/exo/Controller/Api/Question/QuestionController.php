<?php

namespace UJM\ExoBundle\Controller\Api\Question;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Controller\Api\AbstractController;
use UJM\ExoBundle\Entity\Question\Question;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Question\QuestionManager;

/**
 * Question Controller exposes REST API.
 *
 * @EXT\Route("/questions", options={"expose"=true})
 */
class QuestionController extends AbstractController
{
    /**
     * @var QuestionManager
     */
    private $questionManager;

    /**
     * QuestionController constructor.
     *
     * @DI\InjectParams({
     *     "questionManager" = @DI\Inject("ujm_exo.manager.question")
     * })
     *
     * @param QuestionManager $questionManager
     */
    public function __construct(QuestionManager $questionManager)
    {
        $this->questionManager = $questionManager;
    }

    /**
     * Searches for questions.
     *
     * @EXT\Route("/search", name="question_search")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(User $user, Request $request)
    {
        $searchParams = $this->decodeRequestData($request);

        return new JsonResponse(
            $this->questionManager->search($user, $searchParams->filters)
        );
    }

    /**
     * Creates a new Question.
     *
     * @EXT\Route("", name="question_create")
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $errors = [];
        $question = null;

        $data = $this->decodeRequestData($request);
        if (empty($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update question with data
            try {
                $question = $this->questionManager->create($data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Question updated
            return new JsonResponse(
                $this->questionManager->export($question, [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META])
            );
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Updates a Question.
     *
     * @EXT\Route("/{id}", name="question_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("question", class="UJMExoBundle:Question\Question", options={"mapping": {"id": "uuid"}})
     *
     * @param Question $question
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function updateAction(Question $question, Request $request)
    {
        $errors = [];

        $data = $this->decodeRequestData($request);
        if (empty($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            // Try to update question with data
            try {
                $question = $this->questionManager->update($question, $data);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        if (empty($errors)) {
            // Question updated
            return new JsonResponse(
                $this->questionManager->export($question, [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META])
            );
        } else {
            // Invalid data received
            return new JsonResponse($errors, 422);
        }
    }

    /**
     * Deletes a Question.
     *
     * @EXT\Route("/{id}", name="question_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request, User $user)
    {
        $errors = [];

        $data = $this->decodeRequestData($request);
        if (empty($data) || !is_array($data)) {
            // Invalid or empty JSON data received
            $errors[] = [
                'path' => '',
                'message' => 'Invalid JSON data',
            ];
        } else {
            try {
                $this->questionManager->delete($data, $user);
            } catch (ValidationException $e) {
                $errors = $e->getErrors();
            }
        }

        return new JsonResponse(null, 204);
    }
}

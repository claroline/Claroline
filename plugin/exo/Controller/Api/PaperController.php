<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepQuestion;
use UJM\ExoBundle\Manager\PaperManager;
use UJM\ExoBundle\Manager\QuestionManager;
use UJM\ExoBundle\Manager\StepManager;

/**
 * Paper Controller.
 *
 * @EXT\Route(
 *     requirements={"id"="\d+"},
 *     options={"expose"=true},
 *     defaults={"_format": "json"}
 * )
 * @EXT\Method("GET")
 */
class PaperController
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var StepManager
     */
    private $stepManager;

    /**
     * @var QuestionManager
     */
    private $questionManager;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * PaperController constructor.
     *
     * @DI\InjectParams({
     *     "objectManager"   = @DI\Inject("claroline.persistence.object_manager"),
     *     "stepManager"     = @DI\Inject("ujm.exo.step_manager"),
     *     "questionManager" = @DI\Inject("ujm.exo.question_manager"),
     *     "paperManager"    = @DI\Inject("ujm.exo.paper_manager")
     * })
     *
     * @param ObjectManager   $objectManager
     * @param StepManager     $stepManager
     * @param QuestionManager $questionManager
     * @param PaperManager    $paperManager
     */
    public function __construct(
        ObjectManager   $objectManager,
        StepManager     $stepManager,
        QuestionManager $questionManager,
        PaperManager    $paperManager)
    {
        $this->om = $objectManager;
        $this->stepManager = $stepManager;
        $this->questionManager = $questionManager;
        $this->paperManager = $paperManager;
    }

    /**
     * Returns the value of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     * @EXT\Route("/papers/{paperId}/hints/{hintId}", name="exercise_hint")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("hint", class="UJMExoBundle:Hint", options={"mapping": {"hintId": "id"}})
     *
     * @param User  $user
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return JsonResponse
     */
    public function showHintAction(User $user, Paper $paper, Hint $hint)
    {
        $this->assertHasPaperAccess($user, $paper);

        if (!$this->paperManager->hasHint($paper, $hint)) {
            return new JsonResponse('Hint and paper are not related', 422);
        }

        return new JsonResponse($this->paperManager->viewHint($paper, $hint));
    }

    /**
     * Records an answer to an exercise question.
     *
     * @EXT\Route(
     *     "/papers/{paperId}/questions/{questionId}",
     *     name="exercise_submit_question",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     *
     * @EXT\ParamConverter("user",     converter="current_user")
     * @EXT\ParamConverter("paper",    class="UJMExoBundle:Paper",    options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("question", class="UJMExoBundle:Question", options={"mapping": {"questionId": "id"}})
     *
     * @param Paper    $paper
     * @param Question $question
     * @param User     $user
     * @param Request  $request
     *
     * @return JsonResponse
     */
    public function submitQuestionAction(Paper $paper, Question $question, User $user, Request $request)
    {
        $this->assertHasPaperAccess($user, $paper);

        // Get submitted answers from Request
        $data = $request->request->get('data');

        $errors = $this->questionManager->validateAnswerFormat($question, $data);
        if (count($errors) !== 0) {
            return new JsonResponse($errors, 422);
        }

        $this->paperManager->recordAnswer($paper, $question, $data, $request->getClientIp());

        if (Exercise::TYPE_FORMATIVE === $paper->getExercise()->getType()) {
            // For formative, export solution and score for immediate feedback
            $answers = $this->questionManager->exportQuestionAnswers($question);
            $score = $this->questionManager->exportQuestionScore($question, $paper);

            return new JsonResponse(['question' => $answers, 'score' => $score], 200);
        } else {
            return new JsonResponse('', 204);
        }
    }

    /**
     * Records an answer to an exercise step.
     *
     * @EXT\Route(
     *     "/papers/{paperId}/steps/{stepId}",
     *     name="exercise_submit_step",
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     *
     * @EXT\ParamConverter("user",  converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("step",  class="UJMExoBundle:Step",  options={"mapping": {"stepId": "id"}})
     *
     * @param Paper   $paper
     * @param Step    $step
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function submitStepAction(Paper $paper, Step $step, User $user, Request $request)
    {
        $this->assertHasPaperAccess($user, $paper);

        // Get submitted answers from Request
        $data = $request->request->get('data');

        /** @var StepQuestion $stepQuestion */
        foreach ($step->getStepQuestions() as $stepQuestion) {
            /** @var Question $question */
            $question = $stepQuestion->getQuestion();

            // Get question data from Request
            $questionData = !isset($data[$question->getId()]) ? null : $data[$question->getId()];

            $errors = $this->questionManager->validateAnswerFormat($question, $questionData);
            if (count($errors) !== 0) {
                return new JsonResponse($errors, 422);
            }

            $this->paperManager->recordAnswer($paper, $question, $questionData, $request->getClientIp());
        }

        if (Exercise::TYPE_FORMATIVE === $paper->getExercise()->getType()) {
            // For formative, export solution and score for immediate feedback
            $answers = [];

            /** @var StepQuestion $stepQuestion */
            foreach ($step->getStepQuestions() as $stepQuestion) {
                /** @var Question $question */
                $question = $stepQuestion->getQuestion();

                $questionAnswers = $this->questionManager->exportQuestionAnswers($question);
                $questionScore = $this->questionManager->exportQuestionScore($question, $paper);

                $answers[] = [
                    'question' => $questionAnswers,
                    'score' => $questionScore,
                ];
            }

            return new JsonResponse($answers, 200);
        } else {
            return new JsonResponse('', 204);
        }
    }

    /**
     * Marks a paper as finished.
     *
     * @EXT\Route("/papers/{id}/end", name="exercise_finish_paper")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User  $user
     * @param Paper $paper
     *
     * @return JsonResponse
     */
    public function finishPaperAction(User $user, Paper $paper)
    {
        if ($user !== $paper->getUser()) {
            throw new AccessDeniedHttpException();
        }

        $this->paperManager->finishPaper($paper);

        return new JsonResponse('', 204);
    }

    /**
     * Saves the score of an open long answer.
     *
     * @EXT\Route("/papers/{paperId}/question/{questionId}/score/{score}", name="exercise_save_open_score")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("paper",    class="UJMExoBundle:Paper",    options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("question", class="UJMExoBundle:Question", options={"mapping": {"questionId": "id"}})
     *
     * @param Question $question
     * @param Paper    $paper
     * @param int      $score
     *
     * @return JsonResponse
     */
    public function saveOpenLongScore(Question $question, Paper $paper, $score)
    {
        $this->paperManager->recordOpenScore($question, $paper, $score);

        return new JsonResponse('', 204);
    }

    private function assertHasPaperAccess(User $user, Paper $paper)
    {
        if ($paper->getEnd() || $user !== $paper->getUser()) {
            throw new AccessDeniedHttpException();
        }
    }
}

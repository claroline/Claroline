<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Manager\ExerciseManager;
use UJM\ExoBundle\Manager\PaperManager;
use UJM\ExoBundle\Manager\QuestionManager;

/**
 * @EXT\Route(requirements={"id"="\d+"}, options={"expose"=true}, defaults={"_format": "json"})
 * @EXT\Method("GET")
 */
class ExerciseController
{
    private $authorization;
    private $exerciseManager;
    private $questionManager;
    private $paperManager;

    /**
     * @DI\InjectParams({
     *     "authorization"      = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager"    = @DI\Inject("ujm.exo.exercise_manager"),
     *     "questionManager"    = @DI\Inject("ujm.exo.question_manager"),
     *     "paperManager"       = @DI\Inject("ujm.exo.paper_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param QuestionManager               $questionManager
     * @param PaperManager                  $paperManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        QuestionManager $questionManager,
        PaperManager $paperManager
    )
    {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->questionManager = $questionManager;
        $this->paperManager = $paperManager;
    }

    /**
     * Exports the full representation of an exercise (including solutions)
     * in a JSON format.
     *
     * @EXT\Route("/exercises/{id}", name="exercise_get")
     *
     * @param Exercise $exercise
     * @return JsonResponse
     */
    public function exportAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        return new JsonResponse($this->exerciseManager->exportExercise($exercise));
    }

    /**
     * Exports the minimal representation of an exercise (id + meta)
     * in a JSON format.
     *
     * @EXT\Route("/exercises/{id}/minimal", name="exercise_get_minimal")
     *
     * @param Exercise $exercise
     * @return JsonResponse
     */
    public function minimalExportAction(Exercise $exercise)
    {

        $this->assertHasPermission('OPEN', $exercise);

        return new JsonResponse($this->exerciseManager->exportExerciseMinimal($exercise));
    }

    /**
     *
     * Opens an exercise, creating a new paper or re-using an unfinished one.
     * Also check that max attempts are not reached if needed
     *
     * @EXT\Route("/exercises/{id}/attempts", name="exercise_new_attempt")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User      $user
     * @param Exercise  $exercise
     * @return JsonResponse
     */
    public function attemptAction(User $user, Exercise $exercise)
    {
        $this->assertHasPermission('OPEN', $exercise);
        
        // if not admin of the resource check if exercise max attempts is reached
        if (!$this->isAdmin($exercise)) {

            $max = $exercise->getMaxAttempts();
            $nbFinishedPapers = $this->paperManager->countUserFinishedPapers($exercise, $user);
            
            if($max > 0 && $nbFinishedPapers >= $max){
                throw new AccessDeniedHttpException('max attempts reached');
            }
        }
        $data = $this->paperManager->openPaper($exercise, $user, false);
        return new JsonResponse($data);
    }

    /**
     * Records an answer to an exercise question.
     *
     * @EXT\Route("/papers/{paperId}/questions/{questionId}", name="exercise_submit_answer")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("question", class="UJMExoBundle:Question", options={"mapping": {"questionId": "id"}})
     *
     * @param User      $user
     * @param Request   $request
     * @param Paper     $paper
     * @param Question  $question
     * @return JsonResponse
     */
    public function submitAnswerAction(User $user, Request $request, Paper $paper, Question $question)
    {
        $this->assertHasPaperAccess($user, $paper);

        $data = $request->request->get('data');

        $errors = $this->questionManager->validateAnswerFormat($question, $data);

        if (count($errors) !== 0) {
            return new JsonResponse($errors, 422);
        }

        $this->paperManager->recordAnswer($paper, $question, $data, $request->getClientIp());

        return new JsonResponse('', 204);
    }

    /**
     * Returns the value of a question hint, and records the fact that it has
     * been consulted within the context of a given paper.
     *
     * @EXT\Route("/papers/{paperId}/hints/{hintId}", name="exercice_hint")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("hint", class="UJMExoBundle:Hint", options={"mapping": {"hintId": "id"}})
     *
     * @param User  $user
     * @param Paper $paper
     * @param Hint  $hint
     * @return JsonResponse
     */
    public function hintAction(User $user, Paper $paper, Hint $hint)
    {
        $this->assertHasPaperAccess($user, $paper);

        if (!$this->paperManager->hasHint($paper, $hint)) {
            return new JsonResponse('Hint and paper are not related', 422);
        }

        return new JsonResponse($this->paperManager->viewHint($paper, $hint));
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
     * Returns all the papers associated with an exercise for the current user.
     *
     * @EXT\Route("/exercises/{id}/papers", name="exercise_papers")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User      $user
     * @param Exercise  $exercise
     * @return JsonResponse
     */
    public function papersAction(User $user, Exercise $exercise)
    {        
        if($this->isAdmin($exercise)){          
            return new JsonResponse($this->paperManager->exportExercisePapers($exercise));
        }
        return new JsonResponse($this->paperManager->exportUserPapers($exercise, $user));
    }

    /**
     * Returns the number of finished paper for a given user and exercise
     *
     * @EXT\Route("/exercises/{id}/papers/count", name="exercise_papers_count")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User      $user
     * @param Exercise  $exercise
     * @return JsonResponse
     */
    public function countFinishedPaperAction(User $user, Exercise $exercise)
    {
        return new JsonResponse($this->paperManager->countUserFinishedPapers($exercise, $user));
    }

    /**
     * Return a question solutions, global feedback, choices / proposals and for each proposal the feedback 
     * @EXT\Route("/question/{id}", name="get_question_solutions")
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function getQuestionSolutions(User $user, Question $question)
    {
        $data = $this->questionManager->exportQuestionAnswers($question);
        return new JsonResponse($data);
    }

    /**
     * Returns one paper.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @EXT\Route("/exercises/{exerciseId}/papers/{paperId}", name="exercise_paper")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Paper", options={"mapping": {"paperId": "id"}})
     * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "id"}})
     *
     * @param User      $user
     * @param Exercise  $exercise
     * @param Paper     $paper
     * @return JsonResponse
     */
    public function paperAction(User $user, Exercise $exercise, Paper $paper)
    {
        return new JsonResponse($this->paperManager->exportUserPaper($paper, $exercise));
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedHttpException();
        }
    }

    private function isAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->authorization->isGranted('ADMINISTRATE', $collection);
    }

    private function assertHasPaperAccess(User $user, Paper $paper)
    {
        if ($paper->getEnd() || $user !== $paper->getUser()) {
            throw new AccessDeniedHttpException();
        }
    }
}

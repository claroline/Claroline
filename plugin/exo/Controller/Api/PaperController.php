<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Paper Controller.
 * Manages the submitted papers to an exercise.
 *
 * @EXT\Route("exercises/{exerciseId}/papers", options={"expose"=true})
 * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
class PaperController extends AbstractController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * @var ExerciseManager
     */
    private $exerciseManager;

    /**
     * PaperController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "paperManager"    = @DI\Inject("ujm_exo.manager.paper"),
     *     "exerciseManager" = @DI\Inject("ujm_exo.manager.exercise")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param PaperManager                  $paperManager
     * @param EntityManager                 $em
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PaperManager $paperManager,
        ExerciseManager $exerciseManager
    ) {
        $this->authorization = $authorization;
        $this->paperManager = $paperManager;
        $this->exerciseManager = $exerciseManager;
    }

    /**
     * Returns all the papers associated with an exercise.
     * Administrators get the papers of all users, others get only theirs.
     *
     * @EXT\Route("", name="exercise_papers")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return JsonResponse
     */
    public function listAction(Exercise $exercise, User $user)
    {
        $this->assertHasPermission('OPEN', $exercise);

        return new JsonResponse(
            $this->paperManager->serializeExercisePapers($exercise, $this->isAdmin($exercise) ? null : $user)
        );
    }

    /**
     * Returns one paper.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @EXT\Route("/{id}", name="exercise_export_paper")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param Exercise $exercise
     * @param Paper    $paper
     * @param User     $user
     *
     * @return JsonResponse
     */
    public function getAction(Exercise $exercise, Paper $paper, User $user)
    {
        $this->assertHasPermission('OPEN', $exercise);

        if (!$this->isAdmin($paper->getExercise()) && $paper->getUser() !== $user) {
            // Only administrator or the User attached can see a Paper
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->paperManager->serialize($paper));
    }

    /**
     * Deletes all the papers associated with an exercise.
     *
     * @EXT\Route("", name="ujm_exercise_delete_papers")
     * @EXT\Method("DELETE")
     *
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function deleteAllAction(Exercise $exercise)
    {
        if (!$this->isAdmin($exercise)) {
            // Only administrator or Paper Managers can delete Papers
            throw new AccessDeniedException();
        }

        try {
            $this->paperManager->deleteAll($exercise);
        } catch (ValidationException $e) {
            return new JsonResponse($e->getErrors(), 422);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Deletes a paper from an exercise.
     *
     * @EXT\Route("/{id}", name="ujm_exercise_delete_paper")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("paper", class="UJMExoBundle:Attempt\Paper", options={"mapping": {"id": "uuid"}})
     *
     * @param Paper $paper
     *
     * @return JsonResponse
     */
    public function deleteAction(Paper $paper)
    {
        if (!$this->isAdmin($paper->getExercise())) {
            // Only administrator or Paper Managers can delete a Paper
            throw new AccessDeniedException();
        }

        try {
            $this->paperManager->delete($paper);
        } catch (ValidationException $e) {
            return new JsonResponse($e->getErrors(), 422);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Exports papers into a CSV file.
     *
     * @EXT\Route("/export/csv", name="exercise_papers_export")
     * @EXT\Method("GET")
     *
     * @param Exercise $exercise
     *
     * @return StreamedResponse
     */
    public function exportCsvAction(Exercise $exercise)
    {
        if (!$this->isAdmin($exercise)) {
            // Only administrator or Paper Managers can export Papers
            throw new AccessDeniedException();
        }

        return new StreamedResponse(function () use ($exercise) {
            $this->exerciseManager->exportPapersToCsv($exercise);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
    }

    /**
     * Checks whether the current User has the administration rights on the Exercise.
     *
     * @param Exercise $exercise
     *
     * @return bool
     */
    private function isAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->authorization->isGranted('ADMINISTRATE', $collection) || $this->authorization->isGranted('MANAGE_PAPERS', $collection);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}

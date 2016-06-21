<?php

namespace UJM\ExoBundle\Controller\Api;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\ExerciseManager;
use UJM\ExoBundle\Manager\PaperManager;
use UJM\ExoBundle\Manager\QuestionManager;
use UJM\ExoBundle\Services\classes\PaperService;

/**
 * Exercise Controller.
 *
 * @EXT\Route(
 *     requirements={"id"="\d+"},
 *     options={"expose"=true},
 *     defaults={"_format": "json"}
 * )
 * @EXT\Method("GET")
 */
class ExerciseController
{
    private $om;
    private $authorization;
    private $exerciseManager;
    private $questionManager;
    private $paperManager;
    private $paperService;

    /**
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization"      = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager"    = @DI\Inject("ujm.exo.exercise_manager"),
     *     "questionManager"    = @DI\Inject("ujm.exo.question_manager"),
     *     "paperManager"       = @DI\Inject("ujm.exo.paper_manager"),
     *     "paperService"       = @DI\Inject("ujm.exo_paper")
     * })
     *
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param QuestionManager               $questionManager
     * @param PaperManager                  $paperManager
     * @param PaperService                  $paperService
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        QuestionManager $questionManager,
        PaperManager $paperManager,
        PaperService $paperService
    ) {
        $this->om = $om;
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->questionManager = $questionManager;
        $this->paperManager = $paperManager;
        $this->paperService = $paperService;
    }

    /**
     * Exports the full representation of an exercise (including solutions)
     * in a JSON format.
     *
     * @EXT\Route("/exercises/{id}", name="exercise_get")
     *
     * @param Exercise $exercise
     *
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
     *
     * @return JsonResponse
     */
    public function minimalExportAction(Exercise $exercise)
    {
        $this->assertHasPermission('OPEN', $exercise);

        return new JsonResponse($this->exerciseManager->exportExerciseMinimal($exercise));
    }

    /**
     * Opens an exercise, creating a new paper or re-using an unfinished one.
     * Also check that max attempts are not reached if needed.
     *
     * @EXT\Route("/exercises/{id}/attempts", name="exercise_new_attempt")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User     $user
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function attemptAction(User $user, Exercise $exercise)
    {
        $this->assertHasPermission('OPEN', $exercise);

        // if not admin of the resource check if exercise max attempts is reached
        if (!$this->isAdmin($exercise)) {
            $max = $exercise->getMaxAttempts();
            $nbFinishedPapers = $this->paperManager->countUserFinishedPapers($exercise, $user);

            if ($max > 0 && $nbFinishedPapers >= $max) {
                throw new AccessDeniedHttpException('max attempts reached');
            }
        }

        return new JsonResponse($this->paperManager->openPaper($exercise, $user));
    }

    /**
     * Returns all the papers associated with an exercise for the current user.
     *
     * @EXT\Route("/exercises/{id}/papers", name="exercise_papers")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User     $user
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function papersAction(User $user, Exercise $exercise)
    {
        $this->assertHasPermission('OPEN', $exercise);

        if ($this->isAdmin($exercise)) {
            return new JsonResponse($this->paperManager->exportExercisePapers($exercise));
        }

        return new JsonResponse($this->paperManager->exportExercisePapers($exercise, $user));
    }

    /**
     * Exports papers into a CSV format.
     *
     * @EXT\Route("/exercises/{id}/papers/export", name="exercise_papers_export")
     *
     * @param Exercise $exercise
     *
     * @return Response
     */
    public function papersExportAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        $iterableResult = $this->om->getRepository('UJMExoBundle:Paper')
            ->getExerciseAllPapersIterator($exercise->getId());

        $handle = fopen('php://memory', 'r+');
        while (false !== ($row = $iterableResult->next())) {
            $rowCSV = array();
            $infosPaper = $this->paperService->getInfosPaper($row[0]);
            $score = $infosPaper['scorePaper'] / $infosPaper['maxExoScore'];
            $score = $score * 20;

            $rowCSV[] = $row[0]->getUser()->getLastName().'-'.$row[0]->getUser()->getFirstName();
            $rowCSV[] = $row[0]->getNumPaper();
            $rowCSV[] = $row[0]->getStart()->format('Y-m-d H:i:s');
            if ($row[0]->getEnd()) {
                $rowCSV[] = $row[0]->getEnd()->format('Y-m-d H:i:s');
            } else {
                $rowCSV[] = $this->get('translator')->trans('no_finish', array(), 'ujm_exo');
            }
            $rowCSV[] = $row[0]->getInterupt();
            $rowCSV[] = $this->paperService->roundUpDown($score);

            fputcsv($handle, $rowCSV);
            $this->om->detach($row[0]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
    }

    /**
     * Returns the number of finished paper for a given user and exercise.
     *
     * @EXT\Route("/exercises/{id}/papers/count", name="exercise_papers_count")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User     $user
     * @param Exercise $exercise
     *
     * @return JsonResponse
     */
    public function countFinishedPaperAction(User $user, Exercise $exercise)
    {
        return new JsonResponse($this->paperManager->countUserFinishedPapers($exercise, $user));
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
}

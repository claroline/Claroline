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
use UJM\ExoBundle\Repository\PaperRepository;

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
     * PaperController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "paperManager"    = @DI\Inject("ujm_exo.manager.paper")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param PaperManager                  $paperManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PaperManager $paperManager)
    {
        $this->authorization = $authorization;
        $this->paperManager = $paperManager;
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
            $this->paperManager->exportExercisePapers($exercise, $this->isAdmin($exercise) ? null : $user)
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

        return new JsonResponse($this->paperManager->export($paper));
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
        $this->assertHasPermission('ADMINISTRATE', $exercise);

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
        $this->assertHasPermission('ADMINISTRATE', $paper->getExercise());

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
     * @EXT\Route("/export", name="exercise_papers_export")
     * @EXT\Method("GET")
     *
     * @param Exercise $exercise
     *
     * @return StreamedResponse
     */
    public function exportCsvAction(Exercise $exercise)
    {
        $this->assertHasPermission('ADMINISTRATE', $exercise);

        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');

        $papers = $repo->findBy([
            'exercise' => $exercise,
        ]);

        return new StreamedResponse(function () use ($papers) {
            $handle = fopen('php://output', 'w+');

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                fputcsv($handle, [
                    $paper->getUser()->getFirstName().'-'.$paper->getUser()->getLastName(),
                    $paper->getNumber(),
                    $paper->getStart()->format('Y-m-d H:i:s'),
                    $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : '',
                    $paper->isInterrupted(),
                    $this->paperManager->calculateScore($paper, 20),
                ], ';');
            }

            fclose($handle);
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

        return $this->authorization->isGranted('ADMINISTRATE', $collection);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}

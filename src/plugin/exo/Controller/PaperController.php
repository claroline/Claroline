<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Paper Controller.
 * Manages the submitted papers to an exercise.
 *
 * @EXT\ParamConverter("exercise", class="UJM\ExoBundle\Entity\Exercise", options={"mapping": {"exerciseId": "uuid"}})
 */
#[Route(path: 'exercises/{exerciseId}/papers')]
class PaperController
{
    use RequestDecoderTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly PaperManager $paperManager,
        private readonly ExerciseManager $exerciseManager
    ) {
    }

    /**
     * Returns all the papers associated with an exercise.
     * Administrators get the papers of all users, others get only theirs.
     *
     * @EXT\ParamConverter("user", converter="current_user")
     */
    #[Route(path: '', name: 'exercise_paper_list', methods: ['GET'])]
    public function listAction(Exercise $exercise, User $user, Request $request): JsonResponse
    {
        $this->assertHasPermission('OPEN', $exercise);

        $params = $request->query->all();

        $params['hiddenFilters'] = [];
        $params['hiddenFilters']['exercise'] = $exercise->getId();

        if (!$this->isAdmin($exercise)) {
            $params['hiddenFilters']['user'] = $user->getUuid();
        }

        $results = $this->finder->searchEntities(Paper::class, $params);

        return new JsonResponse(
            array_merge($results, [
                'data' => array_map(function (Paper $paper) {
                    return $this->paperManager->serialize($paper, [Transfer::MINIMAL]);
                }, $results['data']),
            ])
        );
    }

    /**
     * Returns one paper.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @EXT\ParamConverter("paper", class="UJM\ExoBundle\Entity\Attempt\Paper", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    #[Route(path: '/{id}', name: 'exercise_paper_get', methods: ['GET'])]
    public function getAction(Exercise $exercise, Paper $paper, User $user): JsonResponse
    {
        $this->assertHasPermission('OPEN', $exercise);

        if (!$this->isAdmin($paper->getExercise()) && ($paper->getUser() !== $user || !$this->paperManager->isSolutionAvailable($exercise, $paper))) {
            // Only administrator or the User attached can see a Paper
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->paperManager->serialize($paper));
    }

    /**
     * Deletes some papers associated with an exercise.
     */
    #[Route(path: '', name: 'ujm_exercise_delete_papers', methods: ['DELETE'])]
    public function deleteAction(Exercise $exercise, Request $request): JsonResponse
    {
        $this->assertHasPermission('MANAGE_PAPERS', $exercise);

        $papers = $this->decodeIdsString($request, Paper::class);
        $this->paperManager->delete($papers);

        return new JsonResponse(null, 204);
    }

    /**
     * Exports papers into a CSV file.
     */
    #[Route(path: '/export/csv', name: 'exercise_papers_export', methods: ['GET'])]
    public function exportCsvAction(Exercise $exercise): StreamedResponse
    {
        $this->assertHasPermission('MANAGE_PAPERS', $exercise);

        return new StreamedResponse(function () use ($exercise): void {
            $this->exerciseManager->exportPapersToCsv($exercise);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
    }

    /**
     * Exports papers into a json file.
     */
    #[Route(path: '/export/json', name: 'exercise_papers_export_json', methods: ['GET'])]
    public function exportJsonAction(Exercise $exercise): StreamedResponse
    {
        if (!$this->isAdmin($exercise)) {
            // Only administrator or Paper Managers can export Papers
            throw new AccessDeniedException();
        }

        $response = new StreamedResponse(function () use ($exercise): void {
            $data = $this->paperManager->serializeExercisePapers($exercise);
            $handle = fopen('php://output', 'w+');
            fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="statistics.json"');

        return $response;
    }

    /**
     * Exports papers into a csv file.
     */
    #[Route(path: '/export/papers/csv', name: 'exercise_papers_export_csv', methods: ['GET'])]
    public function exportCsvAnswersAction(Exercise $exercise): StreamedResponse
    {
        if (!$this->isAdmin($exercise)) {
            // Only administrator or Paper Managers can export Papers
            throw new AccessDeniedException();
        }

        return new StreamedResponse(function () use ($exercise): void {
            $this->exerciseManager->exportResultsToCsv($exercise);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.preg_replace('/[^A-Za-z0-9_\-]/', '_', $exercise->getResourceNode()->getName()).'.csv"',
        ]);
    }

    /**
     * Checks whether the current User has the administration rights on the Exercise.
     */
    private function isAdmin(Exercise $exercise): bool
    {
        return $this->authorization->isGranted('ADMINISTRATE', $exercise->getResourceNode())
            || $this->authorization->isGranted('MANAGE_PAPERS', $exercise->getResourceNode());
    }

    private function assertHasPermission($permission, Exercise $exercise): void
    {
        if (!$this->authorization->isGranted($permission, $exercise->getResourceNode())) {
            throw new AccessDeniedException();
        }
    }
}

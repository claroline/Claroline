<?php

namespace UJM\ExoBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Paper Controller.
 * Manages the submitted papers to an exercise.
 */
#[Route(path: '/exercises')]
class PaperController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly PaperManager $paperManager,
        private readonly ExerciseManager $exerciseManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns all the papers associated with an exercise.
     * Administrators get the papers of all users, others get only theirs.
     *
     * @deprecated use the standard ResourceAttempt list
     */
    #[Route(path: '/{exerciseId}/papers', name: 'exercise_paper_list', methods: ['GET'])]
    public function listAction(
        #[MapEntity(mapping: ['exerciseId' => 'uuid'])]
        Exercise $exercise,
        #[CurrentUser]
        ?User $user,
        Request $request
    ): JsonResponse {
        $this->assertHasPermission('OPEN', $exercise);
        if (!$user) {
            throw new AccessDeniedException();
        }

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
     * @deprecated use the standard ResourceAttempt list
     */
    #[Route(path: '/{exerciseId}/papers/{id}', name: 'exercise_paper_get', methods: ['GET'])]
    public function getAction(
        #[MapEntity(mapping: ['exerciseId' => 'uuid'])] Exercise $exercise,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Paper $paper, #[CurrentUser] ?User $user
    ): JsonResponse {
        $this->assertHasPermission('OPEN', $exercise);

        if (!$this->isAdmin($paper->getExercise()) && ($paper->getUser() !== $user || !$this->paperManager->isSolutionAvailable($exercise, $paper))) {
            // Only administrator or the User attached can see a Paper
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->paperManager->serialize($paper));
    }

    #[Route(path: '/attempt/{attemptId}', name: 'exercise_attempt_get', methods: ['GET'])]
    public function getAttemptAction(
        #[MapEntity(mapping: ['attemptId' => 'uuid'])]
        ResourceAttempt $attempt
    ): JsonResponse {
        $this->checkPermission('OPEN', $attempt, [], true);

        $paper = null;
        if (!empty($attempt->getData())) {
            $attemptData = $attempt->getData();
            if (!empty($attemptData['paper']) && !empty($attemptData['paper']['id'])) {
                $paper = $this->om->getRepository(Paper::class)->find($attemptData['paper']['id']);
            }
        }

        if (empty($paper)) {
            throw new NotFoundHttpException(sprintf('Cannot find attempt details for id : %s', $attempt->getUuid()));
        }

        return new JsonResponse($this->paperManager->serialize($paper));
    }

    /**
     * Deletes some papers associated with an exercise.
     *
     * @deprecated use the standard ResourceAttempt list
     */
    #[Route(path: '/{exerciseId}/papers', name: 'ujm_exercise_delete_papers', methods: ['DELETE'])]
    public function deleteAction(#[MapEntity(mapping: ['exerciseId' => 'uuid'])] Exercise $exercise, Request $request): JsonResponse
    {
        $this->assertHasPermission('FOLLOW', $exercise);

        $ids = $this->decodeRequest($request);
        $papers = $this->om->getRepository(Paper::class)->findBy(['uuid' => $ids]);

        $this->paperManager->delete($papers);

        return new JsonResponse(null, 204);
    }

    /**
     * Exports papers into a csv file.
     */
    #[Route(path: '/{quizId}/papers/export/papers/csv', name: 'exercise_papers_export_csv', methods: ['GET'])]
    public function exportCsvAnswersAction(
        #[MapEntity(mapping: ['quizId' => 'uuid'])]
        ResourceNode $resourceNode
    ): StreamedResponse {
        $this->checkPermission('FOLLOW', $resourceNode, [], true);

        $exercise = $this->om->getRepository(Exercise::class)->findOneBy(['resourceNode' => $resourceNode]);

        return new StreamedResponse(function () use ($exercise): void {
            $this->exerciseManager->exportResultsToCsv($exercise);
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toFilename($resourceNode->getName()).'.csv',
        ]);
    }

    /**
     * Checks whether the current User has the administration rights on the Exercise.
     */
    private function isAdmin(Exercise $exercise): bool
    {
        return $this->authorization->isGranted('ADMINISTRATE', $exercise->getResourceNode())
            || $this->authorization->isGranted('FOLLOW', $exercise->getResourceNode());
    }

    private function assertHasPermission($permission, Exercise $exercise): void
    {
        if (!$this->authorization->isGranted($permission, $exercise->getResourceNode())) {
            throw new AccessDeniedException();
        }
    }
}

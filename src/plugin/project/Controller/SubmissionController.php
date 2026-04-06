<?php

namespace Claroline\ProjectBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ProjectBundle\Entity\Milestone;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;
use Claroline\ProjectBundle\Manager\SubmissionManager;
use Claroline\ProjectBundle\Serializer\SubmissionSerializer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/project')]
class SubmissionController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly SubmissionManager $submissionManager,
        private readonly SubmissionSerializer $submissionSerializer
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Lists submissions for a project (trainer view).
     */
    #[Route(path: '/{id}/submissions', name: 'claro_project_submissions', methods: ['GET'])]
    public function listAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $project->getResourceNode(), [], true);

        $data = $this->finder->search(Submission::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['project' => $project->getUuid()]]
        ));

        return new JsonResponse($data);
    }

    /**
     * Returns the current user's submissions for a project.
     */
    #[Route(path: '/{id}/my-submission', name: 'claro_project_my_submission', methods: ['GET'])]
    public function mySubmissionAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project,
        #[CurrentUser]
        User $user
    ): JsonResponse {
        $this->checkPermission('OPEN', $project->getResourceNode(), [], true);

        $submissions = $this->om->getRepository(Submission::class)->findBy([
            'project' => $project,
            'user' => $user,
        ]);

        return new JsonResponse(array_map(function (Submission $submission) {
            return $this->submissionSerializer->serialize($submission);
        }, $submissions));
    }

    /**
     * Returns the computed deadline for the current user.
     */
    #[Route(path: '/{id}/my-deadline', name: 'claro_project_my_deadline', methods: ['GET'])]
    public function myDeadlineAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project,
        #[CurrentUser]
        User $user
    ): JsonResponse {
        $this->checkPermission('OPEN', $project->getResourceNode(), [], true);

        $deadline = $this->submissionManager->getUserDeadline($project, $user);

        return new JsonResponse([
            'deadline' => DateNormalizer::normalize($deadline),
        ]);
    }

    /**
     * Creates a submission for the current user.
     */
    #[Route(path: '/{id}/submission', name: 'claro_project_submission_create', methods: ['POST'])]
    public function createAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project,
        #[CurrentUser]
        User $user,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $project->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);
        $submission = $this->submissionManager->createSubmission($project, $user, $data);

        return new JsonResponse($this->submissionSerializer->serialize($submission), 201);
    }

    /**
     * Creates a submission for a specific milestone.
     */
    #[Route(path: '/{id}/milestone/{milestoneId}/submission', name: 'claro_project_milestone_submission_create', methods: ['POST'])]
    public function createMilestoneSubmissionAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project,
        #[MapEntity(mapping: ['milestoneId' => 'uuid'])]
        Milestone $milestone,
        #[CurrentUser]
        User $user,
        Request $request
    ): JsonResponse {
        $this->checkPermission('OPEN', $project->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);
        $submission = $this->submissionManager->createSubmission($project, $user, $data, $milestone);

        return new JsonResponse($this->submissionSerializer->serialize($submission), 201);
    }
}

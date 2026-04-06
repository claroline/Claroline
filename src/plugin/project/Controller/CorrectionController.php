<?php

namespace Claroline\ProjectBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;
use Claroline\ProjectBundle\Manager\CorrectionManager;
use Claroline\ProjectBundle\Serializer\CorrectionSerializer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/project')]
class CorrectionController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly CorrectionManager $correctionManager,
        private readonly CorrectionSerializer $correctionSerializer
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns correction details.
     */
    #[Route(path: '/correction/{id}', name: 'claro_project_correction_fetch', methods: ['GET'])]
    public function getAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Correction $correction
    ): JsonResponse {
        $this->checkPermission('OPEN', $correction->getProject()->getResourceNode(), [], true);

        return new JsonResponse($this->correctionSerializer->serialize($correction));
    }

    /**
     * Saves a correction for a submission.
     */
    #[Route(path: '/submission/{id}/correction', name: 'claro_project_correction_save', methods: ['POST'])]
    public function saveAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Submission $submission,
        #[CurrentUser]
        User $user,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $submission->getProject()->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);
        $correction = $this->correctionManager->saveCorrection($submission, $user, $data);

        return new JsonResponse($this->correctionSerializer->serialize($correction));
    }

    /**
     * Saves a direct grade correction (no submission required).
     */
    #[Route(path: '/{id}/user/{userId}/correction', name: 'claro_project_direct_correction_save', methods: ['POST'])]
    public function saveDirectAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project,
        #[MapEntity(mapping: ['userId' => 'uuid'])]
        User $student,
        #[CurrentUser]
        User $corrector,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $project->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);
        $correction = $this->correctionManager->saveDirectCorrection($project, $student, $corrector, $data);

        return new JsonResponse($this->correctionSerializer->serialize($correction));
    }

    /**
     * Publishes a correction (makes it visible to the learner).
     */
    #[Route(path: '/correction/{id}/submit', name: 'claro_project_correction_submit', methods: ['PUT'])]
    public function submitAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Correction $correction
    ): JsonResponse {
        $this->checkPermission('EDIT', $correction->getProject()->getResourceNode(), [], true);

        $correction = $this->correctionManager->submitCorrection($correction);

        return new JsonResponse($this->correctionSerializer->serialize($correction));
    }

    /**
     * Withdraws a published correction (hides it from the learner).
     */
    #[Route(path: '/correction/{id}/withdraw', name: 'claro_project_correction_withdraw', methods: ['PUT'])]
    public function withdrawAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Correction $correction
    ): JsonResponse {
        $this->checkPermission('EDIT', $correction->getProject()->getResourceNode(), [], true);

        $correction = $this->correctionManager->withdrawCorrection($correction);

        return new JsonResponse($this->correctionSerializer->serialize($correction));
    }
}

<?php

namespace Claroline\ProjectBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Manager\PdfManager;
use Claroline\ProjectBundle\Manager\ProjectManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/project')]
class ProjectController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly ProjectManager $projectManager,
        private readonly PdfManager $pdfManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns statistics for the trainer dashboard.
     */
    #[Route(path: '/{id}/stats', name: 'claro_project_stats', methods: ['GET'])]
    public function statsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project
    ): JsonResponse {
        $this->checkPermission('EDIT', $project->getResourceNode(), [], true);

        return new JsonResponse($this->projectManager->getStats($project));
    }

    /**
     * Exports a correction as a PDF grading grid.
     */
    #[Route(path: '/correction/{id}/pdf', name: 'claro_project_correction_pdf', methods: ['GET'])]
    public function correctionPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Correction $correction
    ): StreamedResponse {
        $this->checkPermission('EDIT', $correction->getProject()->getResourceNode(), [], true);

        $fileName = TextNormalizer::toKey($correction->getProject()->getResourceNode()->getName());
        if ($correction->getUser()) {
            $fileName .= '-' . TextNormalizer::toKey(
                $correction->getUser()->getFirstName() . '-' . $correction->getUser()->getLastName()
            );
        }

        return new StreamedResponse(function () use ($correction): void {
            echo $this->pdfManager->generateCorrectionPdf($correction);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . $fileName . '.pdf',
        ]);
    }

    /**
     * Exports a summary PDF with all corrections for a project.
     */
    #[Route(path: '/{id}/pdf', name: 'claro_project_summary_pdf', methods: ['GET'])]
    public function summaryPdfAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project
    ): StreamedResponse {
        $this->checkPermission('EDIT', $project->getResourceNode(), [], true);

        $corrections = $this->om->getRepository(Correction::class)->findBy([
            'project' => $project,
            'status' => Correction::STATUS_SUBMITTED,
        ]);

        $fileName = TextNormalizer::toKey($project->getResourceNode()->getName()) . '-summary';

        return new StreamedResponse(function () use ($project, $corrections): void {
            echo $this->pdfManager->generateProjectSummaryPdf($project, $corrections);
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . $fileName . '.pdf',
        ]);
    }
}

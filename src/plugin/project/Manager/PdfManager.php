<?php

namespace Claroline\ProjectBundle\Manager;

use Claroline\AppBundle\Manager\PdfManager as BasePdfManager;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;
use Twig\Environment;

/**
 * Generates PDF exports for project corrections (grading grid, learner summary).
 */
class PdfManager
{
    public function __construct(
        private readonly BasePdfManager $basePdfManager,
        private readonly Environment $templating
    ) {
    }

    /**
     * Generates a PDF grading grid for a correction.
     * Includes: project info, submission details, score/criteria breakdown, comment.
     */
    public function generateCorrectionPdf(Correction $correction): ?string
    {
        $project = $correction->getProject();
        $submission = $correction->getSubmission();
        $user = $correction->getUser();

        $html = $this->templating->render('@ClarolineProject/pdf/correction.html.twig', [
            'project' => $project,
            'correction' => $correction,
            'submission' => $submission,
            'user' => $user,
        ]);

        $title = $project->getResourceNode()->getName();
        if ($user) {
            $title .= ' - ' . $user->getFirstName() . ' ' . $user->getLastName();
        }

        return $this->basePdfManager->fromHtml($html, $title);
    }

    /**
     * Generates a PDF summary for all submissions/corrections of a project.
     * Includes: stats overview + per-learner grading details.
     */
    public function generateProjectSummaryPdf(Project $project, array $corrections): ?string
    {
        $html = $this->templating->render('@ClarolineProject/pdf/summary.html.twig', [
            'project' => $project,
            'corrections' => $corrections,
        ]);

        $title = $project->getResourceNode()->getName() . ' - Résumé';

        return $this->basePdfManager->fromHtml($html, $title);
    }
}

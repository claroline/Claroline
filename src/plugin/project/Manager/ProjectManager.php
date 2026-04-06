<?php

namespace Claroline\ProjectBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;

class ProjectManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    /**
     * Computes statistics for the trainer dashboard.
     */
    public function getStats(Project $project): array
    {
        $submissionRepo = $this->om->getRepository(Submission::class);
        $correctionRepo = $this->om->getRepository(Correction::class);

        // Count distinct users who submitted
        $submissions = $submissionRepo->findBy(['project' => $project]);
        $submittedUserIds = [];
        foreach ($submissions as $submission) {
            if ($submission->getUser()) {
                $submittedUserIds[$submission->getUser()->getId()] = true;
            }
        }
        $nbSubmitted = count($submittedUserIds);

        // Count corrections with status "submitted"
        $corrections = $correctionRepo->findBy([
            'project' => $project,
            'status' => Correction::STATUS_SUBMITTED,
        ]);

        $nbCorrected = 0;
        $totalScore = 0;
        $nbPassed = 0;
        $correctedUserIds = [];

        foreach ($corrections as $correction) {
            if ($correction->getUser() && !isset($correctedUserIds[$correction->getUser()->getId()])) {
                $correctedUserIds[$correction->getUser()->getId()] = true;
                $nbCorrected++;

                if (null !== $correction->getScore()) {
                    $totalScore += $correction->getScore();
                    $scoreMax = $project->getScoreMax();
                    if ($scoreMax > 0 && ($correction->getScore() / $scoreMax) >= 0.5) {
                        $nbPassed++;
                    }
                }
            }
        }

        $averageScore = $nbCorrected > 0 ? $totalScore / $nbCorrected : null;
        $successRate = $nbCorrected > 0 ? ($nbPassed / $nbCorrected) * 100 : null;

        return [
            'nbSubmitted' => $nbSubmitted,
            'nbCorrected' => $nbCorrected,
            'averageScore' => $averageScore,
            'successRate' => $successRate,
            'scoreMax' => $project->getScoreMax(),
        ];
    }
}

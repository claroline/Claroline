<?php

namespace Claroline\ProjectBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ProjectBundle\Entity\Correction;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;
use Claroline\ProjectBundle\Serializer\CorrectionSerializer;

class CorrectionManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly CorrectionSerializer $correctionSerializer,
        private readonly EvaluationManager $evaluationManager
    ) {
    }

    /**
     * Saves a correction (create or update) for a submission.
     */
    public function saveCorrection(Submission $submission, User $corrector, array $data): Correction
    {
        $correction = null;

        if (isset($data['id'])) {
            $correction = $this->om->getRepository(Correction::class)->findOneBy(['uuid' => $data['id']]);
        }

        if (!$correction) {
            $correction = new Correction();
            $correction->setProject($submission->getProject());
            $correction->setSubmission($submission);
            $correction->setUser($submission->getUser());
            $correction->setCorrector($corrector);
            if ($submission->getMilestone()) {
                $correction->setMilestone($submission->getMilestone());
            }
        }

        $this->correctionSerializer->deserialize($data, $correction);
        $correction->setLastEditionDate(new \DateTime());

        // Compute total score for rubric mode
        if (Project::GRADING_MODE_RUBRIC === $submission->getProject()->getGradingMode()) {
            $total = 0;
            foreach ($correction->getCriteriaScores() as $criteriaScore) {
                $total += $criteriaScore->getScore();
            }
            $correction->setScore($total);
        }

        $this->om->persist($correction);
        $this->om->flush();

        return $correction;
    }

    /**
     * Saves a direct grade correction (no submission required).
     */
    public function saveDirectCorrection(Project $project, User $student, User $corrector, array $data): Correction
    {
        $correction = $this->om->getRepository(Correction::class)->findOneBy([
            'project' => $project,
            'user' => $student,
            'submission' => null,
        ]);

        if (!$correction) {
            $correction = new Correction();
            $correction->setProject($project);
            $correction->setUser($student);
            $correction->setCorrector($corrector);
        }

        $this->correctionSerializer->deserialize($data, $correction);
        $correction->setLastEditionDate(new \DateTime());

        // Compute total score for rubric mode
        if (Project::GRADING_MODE_RUBRIC === $project->getGradingMode()) {
            $total = 0;
            foreach ($correction->getCriteriaScores() as $criteriaScore) {
                $total += $criteriaScore->getScore();
            }
            $correction->setScore($total);
        }

        $this->om->persist($correction);
        $this->om->flush();

        return $correction;
    }

    /**
     * Publishes a correction (makes it visible to the learner) and updates the evaluation.
     */
    public function submitCorrection(Correction $correction): Correction
    {
        $correction->setStatus(Correction::STATUS_SUBMITTED);
        $correction->setSubmissionDate(new \DateTime());
        $correction->setLastEditionDate(new \DateTime());

        $this->om->persist($correction);
        $this->om->flush();

        // Update the Claroline evaluation system
        if ($correction->getUser() && $correction->getProject()) {
            $this->evaluationManager->updateUserEvaluation($correction);
        }

        return $correction;
    }

    /**
     * Withdraws a published correction (hides it from the learner).
     */
    public function withdrawCorrection(Correction $correction): Correction
    {
        $correction->setStatus(Correction::STATUS_IN_PROGRESS);
        $correction->setSubmissionDate(null);
        $correction->setLastEditionDate(new \DateTime());

        $this->om->persist($correction);
        $this->om->flush();

        return $correction;
    }
}

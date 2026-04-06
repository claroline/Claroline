<?php

namespace Claroline\ProjectBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ProjectBundle\Entity\Milestone;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Entity\Submission;

class SubmissionManager
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    /**
     * Creates or updates a submission for a learner.
     */
    public function createSubmission(Project $project, User $user, array $data, ?Milestone $milestone = null): Submission
    {
        // Check if a submission already exists for this user/project/milestone
        $criteria = [
            'project' => $project,
            'user' => $user,
        ];
        if ($milestone) {
            $criteria['milestone'] = $milestone;
        }

        $submission = $this->om->getRepository(Submission::class)->findOneBy($criteria);

        if (!$submission) {
            $submission = new Submission();
            $submission->setProject($project);
            $submission->setUser($user);
            $submission->setMilestone($milestone);
        }

        $submission->setSubmittedDate(new \DateTime());

        if (isset($data['contentType'])) {
            $submission->setContentType($data['contentType']);
        }
        if (array_key_exists('textContent', $data)) {
            $submission->setTextContent($data['textContent']);
        }
        if (array_key_exists('fileData', $data)) {
            $submission->setFileData($data['fileData']);
        }
        if (array_key_exists('urlContent', $data)) {
            $submission->setUrlContent($data['urlContent']);
        }

        $this->om->persist($submission);
        $this->om->flush();

        return $submission;
    }

    /**
     * Records the first access date for a user on a project (for relative deadlines).
     */
    public function recordFirstAccess(Project $project, User $user): void
    {
        if (Project::DEADLINE_TYPE_RELATIVE !== $project->getDeadlineType()) {
            return;
        }

        // Check if we already have a submission with firstAccessDate
        $submission = $this->om->getRepository(Submission::class)->findOneBy([
            'project' => $project,
            'user' => $user,
        ]);

        if ($submission && $submission->getFirstAccessDate()) {
            return;
        }

        if (!$submission) {
            $submission = new Submission();
            $submission->setProject($project);
            $submission->setUser($user);
        }

        $submission->setFirstAccessDate(new \DateTime());
        $this->om->persist($submission);
        $this->om->flush();
    }

    /**
     * Computes the effective deadline for a user on a project.
     */
    public function getUserDeadline(Project $project, User $user): ?\DateTimeInterface
    {
        switch ($project->getDeadlineType()) {
            case Project::DEADLINE_TYPE_FIXED:
                return $project->getDeadlineDate();

            case Project::DEADLINE_TYPE_RELATIVE:
                $submission = $this->om->getRepository(Submission::class)->findOneBy([
                    'project' => $project,
                    'user' => $user,
                ]);

                if ($submission && $submission->getFirstAccessDate() && $project->getDeadlineDays()) {
                    $deadline = clone $submission->getFirstAccessDate();
                    $deadline->modify('+' . $project->getDeadlineDays() . ' days');

                    return $deadline;
                }

                return null;

            default:
                return null;
        }
    }
}

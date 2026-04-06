<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ProjectBundle\Entity\EvaluationCriteria;
use Claroline\ProjectBundle\Entity\Milestone;
use Claroline\ProjectBundle\Entity\Project;

class ProjectSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly MilestoneSerializer $milestoneSerializer,
        private readonly EvaluationCriteriaSerializer $criteriaSerializer,
        private readonly ObjectManager $om
    ) {
    }

    public function getName(): string
    {
        return 'claroline_project';
    }

    public function getClass(): string
    {
        return Project::class;
    }

    public function serialize(Project $project): array
    {
        return [
            'id' => $project->getUuid(),
            'instruction' => $project->getInstruction(),
            'parameters' => [
                'submissionType' => $project->getSubmissionType(),
                'allowedFileTypes' => $project->getAllowedFileTypes(),
                'gradingMode' => $project->getGradingMode(),
                'scoreMax' => $project->getScoreMax(),
                'estimatedDuration' => $project->getEstimatedDuration(),
                'criteria' => array_map(function (EvaluationCriteria $criteria) {
                    return $this->criteriaSerializer->serialize($criteria);
                }, $project->getCriteria()),
                'milestones' => array_map(function (Milestone $milestone) {
                    return $this->milestoneSerializer->serialize($milestone);
                }, $project->getMilestones()),
            ],
            'template' => $project->getTemplateFile(),
            'deadline' => [
                'type' => $project->getDeadlineType(),
                'date' => DateNormalizer::normalize($project->getDeadlineDate()),
                'days' => $project->getDeadlineDays(),
            ],
        ];
    }

    public function deserialize(array $data, Project $project): Project
    {
        $this->sipe('instruction', 'setInstruction', $data, $project);
        $this->sipe('template', 'setTemplateFile', $data, $project);

        if (isset($data['parameters'])) {
            $this->sipe('parameters.submissionType', 'setSubmissionType', $data, $project);
            $this->sipe('parameters.allowedFileTypes', 'setAllowedFileTypes', $data, $project);
            $this->sipe('parameters.gradingMode', 'setGradingMode', $data, $project);
            $this->sipe('parameters.estimatedDuration', 'setEstimatedDuration', $data, $project);

            if (isset($data['parameters']['criteria'])) {
                $this->deserializeCriteria($project, $data['parameters']['criteria']);
            }

            if (isset($data['parameters']['milestones'])) {
                $this->deserializeMilestones($project, $data['parameters']['milestones']);
            }

            // In rubric mode, scoreMax is computed from criteria
            if (Project::GRADING_MODE_RUBRIC === $project->getGradingMode()) {
                $total = 0;
                foreach ($project->getCriteria() as $criterion) {
                    $total += $criterion->getScoreMax();
                }
                $project->setScoreMax($total);
            } else {
                $this->sipe('parameters.scoreMax', 'setScoreMax', $data, $project);
            }
        }

        if (isset($data['deadline'])) {
            if (isset($data['deadline']['type'])) {
                $project->setDeadlineType($data['deadline']['type']);
            }
            if (array_key_exists('date', $data['deadline'])) {
                $project->setDeadlineDate(DateNormalizer::denormalize($data['deadline']['date']));
            }
            if (array_key_exists('days', $data['deadline'])) {
                $project->setDeadlineDays($data['deadline']['days']);
            }
        }

        return $project;
    }

    private function deserializeCriteria(Project $project, array $criteriaData): void
    {
        $oldCriteria = $project->getCriteria();
        $newUuids = [];

        foreach ($criteriaData as $criterionData) {
            $criterion = $this->criteriaSerializer->deserialize($criterionData);
            $project->addCriterion($criterion);
            $newUuids[] = $criterion->getUuid();
        }

        foreach ($oldCriteria as $criterion) {
            if (!in_array($criterion->getUuid(), $newUuids)) {
                $project->removeCriterion($criterion);
                $this->om->remove($criterion);
            }
        }
    }

    private function deserializeMilestones(Project $project, array $milestonesData): void
    {
        $oldMilestones = $project->getMilestones();
        $newUuids = [];

        foreach ($milestonesData as $milestoneData) {
            $milestone = $this->milestoneSerializer->deserialize($milestoneData);
            $project->addMilestone($milestone);
            $newUuids[] = $milestone->getUuid();
        }

        foreach ($oldMilestones as $milestone) {
            if (!in_array($milestone->getUuid(), $newUuids)) {
                $project->removeMilestone($milestone);
                $this->om->remove($milestone);
            }
        }
    }
}

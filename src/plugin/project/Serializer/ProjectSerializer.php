<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ProjectBundle\Entity\Project;

class ProjectSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function getClass(): string
    {
        return Project::class;
    }

    public function getName(): string
    {
        return 'claroline_project';
    }

    public function serialize(Project $project, array $options = []): array
    {
        return [
            'id' => $project->getUuid(),
            'instruction' => $project->getInstruction(),
            'expectedFormat' => $project->getExpectedFormat(),
            'evaluationType' => $project->getEvaluationType(),
            'dropStartDate' => $project->getDropStartDate()?->format('Y-m-d\TH:i:s'),
            'dropEndDate' => $project->getDropEndDate()?->format('Y-m-d\TH:i:s'),
            'estimatedDuration' => $project->getEstimatedDuration(),
            'allowFileUpload' => $project->isAllowFileUpload(),
            'allowRichText' => $project->isAllowRichText(),
            'allowUrl' => $project->isAllowUrl(),
            'allowMedia' => $project->isAllowMedia(),
            'groupMode' => $project->isGroupMode(),
            'iterativeMode' => $project->isIterativeMode(),
            'templateFile' => $project->getTemplateFile(),
        ];
    }

    public function deserialize(array $data, Project $project, array $options = []): Project
    {
        $this->sipe('instruction', 'setInstruction', $data, $project);
        $this->sipe('expectedFormat', 'setExpectedFormat', $data, $project);
        $this->sipe('evaluationType', 'setEvaluationType', $data, $project);
        $this->sipe('estimatedDuration', 'setEstimatedDuration', $data, $project);
        $this->sipe('allowFileUpload', 'setAllowFileUpload', $data, $project);
        $this->sipe('allowRichText', 'setAllowRichText', $data, $project);
        $this->sipe('allowUrl', 'setAllowUrl', $data, $project);
        $this->sipe('allowMedia', 'setAllowMedia', $data, $project);
        $this->sipe('groupMode', 'setGroupMode', $data, $project);
        $this->sipe('iterativeMode', 'setIterativeMode', $data, $project);
        $this->sipe('templateFile', 'setTemplateFile', $data, $project);

        if (isset($data['dropStartDate'])) {
            $project->setDropStartDate(new \DateTime($data['dropStartDate']));
        }

        if (isset($data['dropEndDate'])) {
            $project->setDropEndDate(new \DateTime($data['dropEndDate']));
        }

        return $project;
    }
}

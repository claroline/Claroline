<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;

class PathSerializer
{
    use SerializerTrait;

    /** @var StepSerializer */
    private $stepSerializer;

    public function __construct(
        StepSerializer $stepSerializer
    ) {
        $this->stepSerializer = $stepSerializer;
    }

    public function getSchema(): string
    {
        return '#/plugin/path/path.json';
    }

    public function getName(): string
    {
        return 'path';
    }

    public function serialize(Path $path, array $options = []): array
    {
        return [
            'id' => $path->getUuid(),
            'meta' => [
                'description' => $path->getDescription(),
                'endMessage' => $path->getEndMessage(),
            ],
            'display' => [
                'showOverview' => $path->getShowOverview(),
                'showEndPage' => $path->getShowEndPage(),
                'numbering' => $path->getNumbering() ? $path->getNumbering() : 'none',
                'manualProgressionAllowed' => $path->isManualProgressionAllowed(),
                'showScore' => $path->getShowScore(),
            ],
            'opening' => [
                'secondaryResources' => $path->getSecondaryResourcesTarget(),
            ],
            'steps' => array_values(array_map(function (Step $step) use ($options) {
                return $this->stepSerializer->serialize($step, $options);
            }, $path->getRootSteps())),
            'score' => [
                'success' => $path->getSuccessScore(),
                'total' => $path->getScoreTotal(),
            ],
        ];
    }

    public function deserialize(array $data, Path $path, array $options = []): Path
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $path);
        } else {
            $path->refreshUuid();
        }

        $this->sipe('meta.description', 'setDescription', $data, $path);
        $this->sipe('meta.endMessage', 'setEndMessage', $data, $path);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $path);
        $this->sipe('display.showEndPage', 'setShowEndPage', $data, $path);
        $this->sipe('display.numbering', 'setNumbering', $data, $path);
        $this->sipe('display.manualProgressionAllowed', 'setManualProgressionAllowed', $data, $path);
        $this->sipe('display.showScore', 'setShowScore', $data, $path);

        $this->sipe('opening.secondaryResources', 'setSecondaryResourcesTarget', $data, $path);

        $this->sipe('score.success', 'setSuccessScore', $data, $path);
        $this->sipe('score.total', 'setScoreTotal', $data, $path);

        if (isset($data['steps'])) {
            $this->deserializeSteps($data['steps'] ?? [], $path, $options);
        }

        return $path;
    }

    private function deserializeSteps(array $stepsData, Path $path, array $options = []): void
    {
        $ids = [];

        // updates steps
        foreach ($stepsData as $stepIndex => $stepData) {
            if ($stepData['id']) {
                $step = $path->getStep($stepData['id']);
            }

            if (empty($step)) {
                $step = new Step();
            }

            $step->setPath($path);
            $step->setOrder($stepIndex);

            $this->stepSerializer->deserialize($step, $stepData, $options);
            $ids[] = $step->getUuid();
        }

        // removes steps which no longer exists
        $currentSteps = $path->getRootSteps();
        foreach ($currentSteps as $currentStep) {
            if (!in_array($currentStep->getUuid(), $ids)) {
                $currentStep->setPath(null);
            }
        }
    }
}

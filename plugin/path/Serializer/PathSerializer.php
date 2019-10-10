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

    /**
     * PathSerializer constructor.
     *
     * @param StepSerializer $stepSerializer
     */
    public function __construct(
        StepSerializer $stepSerializer
    ) {
        $this->stepSerializer = $stepSerializer;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/path/path.json';
    }

    /**
     * @param Path  $path
     * @param array $options
     *
     * @return array
     */
    public function serialize(Path $path, array $options = [])
    {
        return [
            'id' => $path->getUuid(),
            'display' => [
                'description' => $path->getDescription(),
                'showOverview' => $path->getShowOverview(),
                'numbering' => $path->getNumbering() ? $path->getNumbering() : 'none',
                'manualProgressionAllowed' => $path->isManualProgressionAllowed(),
                'showScore' => $path->getShowScore(),
            ],
            'opening' => [
                'secondaryResources' => $path->getSecondaryResourcesTarget(),
            ],
            'steps' => array_map(function (Step $step) use ($options) {
                return $this->stepSerializer->serialize($step, $options);
            }, $path->getRootSteps()),
            'score' => [
                'success' => $path->getSuccessScore(),
                'total' => $path->getScoreTotal(),
            ],
        ];
    }

    /**
     * @param array $data
     * @param Path  $path
     * @param array $options
     *
     * @return Path
     */
    public function deserialize($data, Path $path, array $options = [])
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $path);
        } else {
            $path->refreshUuid();
        }

        $this->sipe('display.description', 'setDescription', $data, $path);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $path);
        $this->sipe('display.numbering', 'setNumbering', $data, $path);
        $this->sipe('display.manualProgressionAllowed', 'setManualProgressionAllowed', $data, $path);
        $this->sipe('display.showScore', 'setShowScore', $data, $path);

        $this->sipe('opening.secondaryResources', 'setSecondaryResourcesTarget', $data, $path);

        $this->sipe('score.success', 'setSuccessScore', $data, $path);
        $this->sipe('score.total', 'setScoreTotal', $data, $path);

        if (isset($data['steps'])) {
            $this->deserializeSteps($data['steps'], $path, $options);
        }

        return $path;
    }

    /**
     * @param array $stepsData
     * @param Path  $path
     * @param array $options
     */
    private function deserializeSteps($stepsData, Path $path, array $options = [])
    {
        /** @var Step[] $currentSteps */
        $currentSteps = $path->getRootSteps();
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
        foreach ($currentSteps as $currentStep) {
            if (!in_array($currentStep->getUuid(), $ids)) {
                $currentStep->setPath(null);
                $currentStep->setParent(null);
            }
        }
    }
}

<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;

class PathSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;
    /** @var StepSerializer */
    private $stepSerializer;

    private $resourceNodeRepo;

    public function __construct(
        ObjectManager $om,
        ResourceNodeSerializer $resourceSerializer,
        StepSerializer $stepSerializer
    ) {
        $this->om = $om;
        $this->resourceNodeSerializer = $resourceSerializer;
        $this->stepSerializer = $stepSerializer;

        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
    }

    public function getClass(): string
    {
        return Path::class;
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
            'display' => [
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
            'evaluation' => [
                'successMessage' => $path->getSuccessMessage(),
                'failureMessage' => $path->getFailureMessage(),
            ],
            'overview' => [
                'display' => $path->getShowOverview(),
                'message' => $path->getOverviewMessage(),
                'resource' => $path->getOverviewResource() ? $this->resourceNodeSerializer->serialize($path->getOverviewResource(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            ],
            'end' => [
                'display' => $path->getShowEndPage(),
                'message' => $path->getEndMessage(),
                'navigation' => $path->hasEndNavigation(),
                'back' => [
                    'type' => $path->getEndBackType(),
                    'label' => $path->getEndBackLabel(),
                    'target' => $path->getEndBackTarget() ? $this->resourceNodeSerializer->serialize($path->getEndBackTarget(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
                ],
                'workspaceCertificates' => $path->getShowWorkspaceCertificates(),
            ],
        ];
    }

    public function deserialize(array $data, Path $path, array $options = []): Path
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $path);
        } else {
            $path->refreshUuid();
        }

        $this->sipe('display.numbering', 'setNumbering', $data, $path);
        $this->sipe('display.manualProgressionAllowed', 'setManualProgressionAllowed', $data, $path);
        $this->sipe('display.showScore', 'setShowScore', $data, $path);

        $this->sipe('opening.secondaryResources', 'setSecondaryResourcesTarget', $data, $path);

        $this->sipe('score.success', 'setSuccessScore', $data, $path);
        $this->sipe('score.total', 'setScoreTotal', $data, $path);

        $this->sipe('evaluation.successMessage', 'setSuccessMessage', $data, $path);
        $this->sipe('evaluation.failureMessage', 'setFailureMessage', $data, $path);

        if (!empty($data['overview'])) {
            $this->sipe('overview.display', 'setShowOverview', $data, $path);
            $this->sipe('overview.message', 'setOverviewMessage', $data, $path);
            if (array_key_exists('resource', $data['overview'])) {
                $overviewResource = null;
                if (!empty($data['overview']['resource'])) {
                    $overviewResource = $this->resourceNodeRepo->findOneBy(['uuid' => $data['overview']['resource']['id']]);
                }

                $path->setOverviewResource($overviewResource);
            }
        }

        if (!empty($data['end'])) {
            $this->sipe('end.display', 'setShowEndPage', $data, $path);
            $this->sipe('end.message', 'setEndMessage', $data, $path);
            $this->sipe('end.navigation', 'setEndNavigation', $data, $path);
            $this->sipe('end.workspaceCertificates', 'setShowWorkspaceCertificates', $data, $path);

            if (!empty($data['end']['back'])) {
                $this->sipe('end.back.type', 'setEndBackType', $data, $path);
                $this->sipe('end.back.label', 'setEndBackLabel', $data, $path);

                if (array_key_exists('target', $data['end']['back'])) {
                    $targetResource = null;
                    if (!empty($data['end']['back']['target'])) {
                        $targetResource = $this->resourceNodeRepo->findOneBy(['uuid' => $data['end']['back']['target']['id']]);
                    }

                    $path->setEndBackTarget($targetResource);
                }
            }
        }

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

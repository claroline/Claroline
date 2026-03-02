<?php

namespace Claroline\EvaluationBundle\Serializer\Sequence;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\EvaluationBundle\Entity\Sequence\SecondaryResource;
use Claroline\EvaluationBundle\Entity\Sequence\Step;
use Doctrine\Persistence\ObjectRepository;

class StepSerializer
{
    use SerializerTrait;

    private ResourceNodeRepository $resourceNodeRepo;
    private ObjectRepository $stepRepo;
    private ObjectRepository $secondaryResourceRepo;

    public function __construct(
        ObjectManager $om,
        private readonly ResourceNodeSerializer $resourceNodeSerializer
    ) {
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->stepRepo = $om->getRepository(Step::class);
        $this->secondaryResourceRepo = $om->getRepository(SecondaryResource::class);
    }

    public function getClass(): string
    {
        return Step::class;
    }

    public function getSchema(): string
    {
        return '#/main/evaluation/sequence/step.json';
    }

    public function getName(): string
    {
        return 'sequence_step';
    }

    public function serialize(Step $step, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $step->getUuid(),
                'title' => $step->getTitle(),
                'slug' => $step->getSlug(),
                'poster' => $step->getPoster(),
            ];
        }

        return [
            'id' => $step->getUuid(),
            'title' => $step->getTitle(),
            'slug' => $step->getSlug(),
            'poster' => $step->getPoster(),
            'description' => $step->getDescription(),
            'primaryResource' => $step->getResource() ? $this->resourceNodeSerializer->serialize($step->getResource(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'objective' => $step->getObjective(),
            'secondaryResources' => array_map(function (SecondaryResource $secondaryResource) {
                return $this->resourceNodeSerializer->serialize($secondaryResource->getResource(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $step->getSecondaryResources()->toArray()),
            'display' => [
                'numbering' => $step->getNumbering(),
            ],
            'evaluation' => [
                'required' => $step->isRequired(),
                'scored' => $step->isScored(),
            ],
            'children' => array_values(array_map(function (Step $child) use ($options) {
                return $this->serialize($child, $options);
            }, $step->getChildren()->toArray())),
        ];
    }

    public function deserialize(Step $step, array $data, array $options = []): Step
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $step);
        } else {
            $step->refreshUuid();
        }

        $this->sipe('title', 'setTitle', $data, $step);
        $this->sipe('slug', 'setSlug', $data, $step);
        $this->sipe('description', 'setDescription', $data, $step);
        $this->sipe('poster', 'setPoster', $data, $step);
        $this->sipe('required', 'setRequired', $data, $step);
        $this->sipe('scored', 'setScored', $data, $step);
        $this->sipe('display.numbering', 'setNumbering', $data, $step);

        $this->sipe('objective', 'setObjective', $data, $step);

        $this->sipe('evaluation.required', 'setRequired', $data, $step);
        $this->sipe('evaluation.scored', 'setScored', $data, $step);

        // Set primary resource
        /** @var ResourceNode $resource */
        $resource = isset($data['primaryResource']['id']) ?
            $this->resourceNodeRepo->findOneBy(['uuid' => $data['primaryResource']['id']]) :
            null;
        $step->setResource($resource);

        // Set secondary resources
        if (isset($data['secondaryResources'])) {
            $step->emptySecondaryResources();

            foreach ($data['secondaryResources'] as $index => $resourceData) {
                /** @var ResourceNode $resource */
                $resource = $this->resourceNodeRepo->findOneBy(['uuid' => $resourceData['id']]);
                if (!empty($resource)) {
                    $secondaryResource = new SecondaryResource();
                    $secondaryResource->setOrder($index);
                    $secondaryResource->setResource($resource);

                    $step->addSecondaryResource($secondaryResource);
                }
            }
        }

        // Set children steps
        if (isset($data['children'])) {
            /** @var Step[] $currentChildren */
            $currentChildren = $step->getChildren()->toArray();
            $ids = [];

            // updates steps
            foreach ($data['children'] as $childIndex => $childData) {
                if ($childData['id']) {
                    // I need to get the step from the path to have access to all the steps
                    // to manage steps moving
                    $child = $step->getSequence()->getStep($childData['id']);
                }

                if (empty($child)) {
                    $child = new Step();
                }

                $child->setOrder($childIndex);
                $step->addChild($child);

                $this->deserialize($child, $childData, $options);
                $ids[] = $child->getUuid();
            }

            // removes steps which no longer exist
            foreach ($currentChildren as $currentStep) {
                if (!in_array($currentStep->getUuid(), $ids)) {
                    $step->removeChild($currentStep);
                }
            }
        }

        return $step;
    }
}

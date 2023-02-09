<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Innova\PathBundle\Entity\SecondaryResource;
use Innova\PathBundle\Entity\Step;

class StepSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;

    private $resourceNodeRepo;
    private $stepRepo;
    private $secondaryResourceRepo;

    public function __construct(
        ObjectManager $om,
        ResourceNodeSerializer $resourceSerializer
    ) {
        $this->om = $om;
        $this->resourceNodeSerializer = $resourceSerializer;

        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->stepRepo = $om->getRepository(Step::class);
        $this->secondaryResourceRepo = $om->getRepository(SecondaryResource::class);
    }

    public function getSchema(): string
    {
        return '#/plugin/path/step.json';
    }

    public function getName(): string
    {
        return 'path_step';
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
            'showResourceHeader' => $step->getShowResourceHeader(),
            'secondaryResources' => array_map(function (SecondaryResource $secondaryResource) {
                return $this->resourceNodeSerializer->serialize($secondaryResource->getResource(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $step->getSecondaryResources()->toArray()),
            'display' => [
                'numbering' => $step->getNumbering(),
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
        $this->sipe('display.numbering', 'setNumbering', $data, $step);

        // Set primary resource
        /** @var ResourceNode $resource */
        $resource = isset($data['primaryResource']['id']) ?
            $this->resourceNodeRepo->findOneBy(['uuid' => $data['primaryResource']['id']]) :
            null;
        $step->setResource($resource);

        if (isset($data['showResourceHeader'])) {
            $step->setShowResourceHeader($data['showResourceHeader']);
        }

        // Set secondary resources
        if (isset($data['secondaryResources'])) {
            $step->emptySecondaryResources();

            foreach ($data['secondaryResources'] as $index => $resourceData) {
                $secondaryResource = new SecondaryResource();
                $secondaryResource->setOrder($index);

                /** @var ResourceNode $resource */
                $resource = $this->resourceNodeRepo->findOneBy(['uuid' => $resourceData['id']]);
                $secondaryResource->setResource($resource);

                $step->addSecondaryResource($secondaryResource);
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
                    // I need to get step from path to have access to all the steps in order
                    // to manage steps moving
                    $child = $step->getPath()->getStep($childData['id']);
                }

                if (empty($child)) {
                    $child = new Step();
                }

                $child->setOrder($childIndex);
                $step->addChild($child);

                $this->deserialize($child, $childData, $options);
                $ids[] = $child->getUuid();
            }

            // removes steps which no longer exists
            foreach ($currentChildren as $currentStep) {
                if (!in_array($currentStep->getUuid(), $ids)) {
                    $step->removeChild($currentStep);
                }
            }
        }

        return $step;
    }
}

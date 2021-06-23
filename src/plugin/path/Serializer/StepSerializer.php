<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Innova\PathBundle\Entity\SecondaryResource;
use Innova\PathBundle\Entity\Step;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StepSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $resourceNodeRepo;
    private $stepRepo;
    private $secondaryResourceRepo;
    private $userProgressionRepo;

    public function __construct(
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        ResourceNodeSerializer $resourceSerializer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->resourceNodeSerializer = $resourceSerializer;
        $this->tokenStorage = $tokenStorage;

        $this->resourceNodeRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->stepRepo = $om->getRepository('Innova\PathBundle\Entity\Step');
        $this->secondaryResourceRepo = $om->getRepository('Innova\PathBundle\Entity\SecondaryResource');
        $this->userProgressionRepo = $om->getRepository('Innova\PathBundle\Entity\UserProgression');
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
        $poster = null;
        if (!empty($step->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\File\PublicFile')
                ->findOneBy(['url' => $step->getPoster()]);

            if ($file) {
                $poster = $this->fileSerializer->serialize($file);
            }
        }

        if (in_array(Options::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $step->getUuid(),
                'title' => $step->getTitle(),
                'slug' => $step->getSlug(),
                'poster' => $poster,
            ];
        }

        return [
            'id' => $step->getUuid(),
            'title' => $step->getTitle(),
            'slug' => $step->getSlug(),
            'poster' => $poster,
            'description' => $step->getDescription(),
            'primaryResource' => $step->getResource() ? $this->resourceNodeSerializer->serialize($step->getResource()) : null,
            'showResourceHeader' => $step->getShowResourceHeader(),
            'secondaryResources' => array_map(function (SecondaryResource $secondaryResource) {
                return $this->resourceNodeSerializer->serialize($secondaryResource->getResource(), [Options::SERIALIZE_MINIMAL]);
            }, $step->getSecondaryResources()->toArray()),
            'display' => [
                'numbering' => $step->getNumbering(),
            ],
            'children' => array_values(array_map(function (Step $child) use ($options) {
                return $this->serialize($child, $options);
            }, $step->getChildren()->toArray())),
            'userProgression' => $this->serializeUserProgression($step),
            'evaluated' => $step->isEvaluated(),
        ];
    }

    public function deserialize(Step $step, array $data, array $options = []): Step
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $step);
        } else {
            $step->refreshUuid();
        }

        $this->sipe('title', 'setTitle', $data, $step);
        $this->sipe('slug', 'setSlug', $data, $step);
        $this->sipe('description', 'setDescription', $data, $step);
        $this->sipe('poster.url', 'setPoster', $data, $step);
        $this->sipe('display.numbering', 'setNumbering', $data, $step);

        /* Set primary resource */
        $resource = isset($data['primaryResource']['id']) ?
            $this->resourceNodeRepo->findOneBy(['uuid' => $data['primaryResource']['id']]) :
            null;
        $step->setResource($resource);

        $evaluated = isset($data['evaluated']) && $step->getResource() ? $data['evaluated'] : false;
        $step->setEvaluated($evaluated);

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

    private function serializeUserProgression(Step $step): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $userProgression = $user instanceof User ?
            $this->userProgressionRepo->findOneBy(['step' => $step, 'user' => $user]) :
            null;

        return [
            'status' => empty($userProgression) ? 'unseen' : $userProgression->getStatus(),
        ];
    }
}

<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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

    /**
     * PathSerializer constructor.
     *
     * @param ObjectManager          $om
     * @param PublicFileSerializer   $fileSerializer
     * @param ResourceNodeSerializer $resourceSerializer
     * @param TokenStorageInterface  $tokenStorage
     */
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

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/path/step.json';
    }

    public function getName()
    {
        return 'path_step';
    }

    /**
     * @param Step  $step
     * @param array $options
     *
     * @return array
     */
    public function serialize(Step $step, array $options = [])
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
            'children' => array_map(function (Step $child) use ($options) {
                return $this->serialize($child, $options);
            }, $step->getChildren()->toArray()),
            'userProgression' => $this->serializeUserProgression($step),
            'evaluated' => $step->isEvaluated(),
        ];
    }

    /**
     * @param Step  $step
     * @param array $data
     * @param array $options
     *
     * @return Step
     */
    public function deserialize(Step $step, $data, array $options = [])
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

        if (isset($data['display'])) {
            if (isset($data['display']['numbering'])) {
                $step->setNumbering($data['display']['numbering']);
            }
            if (isset($data['display']['height'])) {
                $step->setActivityHeight($data['display']['height']);
            }
        }

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
                    $currentStep->setPath(null);
                    $step->removeChild($currentStep);
                }
            }
        }

        return $step;
    }

    /**
     * @param Step $step
     *
     * @return array
     */
    private function serializeUserProgression(Step $step)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $userProgression = 'anon.' !== $user ?
            $this->userProgressionRepo->findOneBy(['step' => $step, 'user' => $user]) :
            null;
        $data = [
            'status' => empty($userProgression) ? 'unseen' : $userProgression->getStatus(),
        ];

        return $data;
    }
}

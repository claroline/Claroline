<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Innova\PathBundle\Entity\InheritedResource;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\SecondaryResource;
use Innova\PathBundle\Entity\Step;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.path")
 * @DI\Tag("claroline.serializer")
 */
class PathSerializer
{
    use SerializerTrait;

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
    private $inheritedResourceRepo;
    private $userProgressionRepo;

    /**
     * PathSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileSerializer"     = @DI\Inject("claroline.serializer.public_file"),
     *     "resourceSerializer" = @DI\Inject("claroline.serializer.resource_node"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage")
     * })
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
        $this->inheritedResourceRepo = $om->getRepository('Innova\PathBundle\Entity\InheritedResource');
        $this->userProgressionRepo = $om->getRepository('Innova\PathBundle\Entity\UserProgression');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/path/path.json';
    }

    /**
     * @param Path $path
     *
     * @return array
     */
    public function serialize(Path $path)
    {
        return [
            'id' => $path->getUuid(),
            'display' => [
                'description' => $path->getDescription(),
                'showOverview' => $path->getShowOverview(),
                'showSummary' => $path->getShowSummary(),
                'openSummary' => $path->isSummaryDisplayed(),
                'numbering' => $path->getNumbering() ? $path->getNumbering() : 'none',
                'manualProgressionAllowed' => $path->isManualProgressionAllowed(),
            ],
            'steps' => array_map(function (Step $step) {
                return $this->serializeStep($step);
            }, $path->getRootSteps()),
        ];
    }

    /**
     * @param array $data
     * @param Path  $path
     *
     * @return Path
     */
    public function deserialize($data, Path $path)
    {
        $path->setUuid($data['id']);

        $this->sipe('display.description', 'setDescription', $data, $path);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $path);
        $this->sipe('display.showSummary', 'setShowSummary', $data, $path);
        $this->sipe('display.openSummary', 'setSummaryDisplayed', $data, $path);
        $this->sipe('display.numbering', 'setNumbering', $data, $path);
        $this->sipe('display.manualProgressionAllowed', 'setManualProgressionAllowed', $data, $path);

        if (isset($data['steps'])) {
            $this->deserializeSteps($data['steps'], $path);
        }

        return $path;
    }

    /**
     * @param Step $step
     *
     * @return array
     */
    private function serializeStep(Step $step)
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

        return [
            'id' => $step->getUuid(),
            'title' => $step->getTitle(),
            'description' => $step->getDescription(),
            'poster' => $poster,
            'primaryResource' => $step->getResource() ? $this->resourceNodeSerializer->serialize($step->getResource()) : null,
            'secondaryResources' => array_map(function (SecondaryResource $secondaryResource) {
                return $this->serializeSecondaryResource($secondaryResource);
            }, $step->getSecondaryResources()->toArray()),
            'inheritedResources' => array_map(function (InheritedResource $inheritedResource) {
                return $this->serializeInheritedResource($inheritedResource);
            }, $step->getInheritedResources()->toArray()),
            'display' => [
                'numbering' => $step->getNumbering(),
                'height' => $step->getActivityHeight(),
            ],
            'children' => array_map(function (Step $child) {
                return $this->serializeStep($child);
            }, $step->getChildren()->toArray()),
            'userProgression' => $this->serializeUserProgression($step),
        ];
    }

    /**
     * @param SecondaryResource $secondaryResource
     *
     * @return array
     */
    private function serializeSecondaryResource(SecondaryResource $secondaryResource)
    {
        return [
            'id' => $secondaryResource->getUuid(),
            'inheritanceEnabled' => $secondaryResource->isInheritanceEnabled(),
            'resource' => $this->resourceNodeSerializer->serialize($secondaryResource->getResource()),
        ];
    }

    /**
     * @param InheritedResource $inheritedResource
     *
     * @return array
     */
    private function serializeInheritedResource(InheritedResource $inheritedResource)
    {
        return [
            'id' => $inheritedResource->getUuid(),
            'resource' => $this->resourceNodeSerializer->serialize($inheritedResource->getResource()),
            'lvl' => $inheritedResource->getLvl(),
            'sourceUuid' => $inheritedResource->getSourceUuid(),
        ];
    }

    /**
     * @param Step $step
     *
     * @return array
     */
    private function serializeUserProgression(Step $step)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $userProgression = $user !== 'anon.' ?
            $this->userProgressionRepo->findOneBy(['step' => $step, 'user' => $user]) :
            null;
        $data = [
            'status' => empty($userProgression) ? 'unseen' : $userProgression->getStatus(),
        ];

        return $data;
    }

    /**
     * @param array $stepsData
     * @param Path  $path
     */
    private function deserializeSteps($stepsData, Path $path)
    {
        /** @var Step[] $oldSteps */
        $oldSteps = $path->getSteps()->toArray();
        $newStepsUuids = [];
        $path->emptySteps();
        $order = 0;

        foreach ($stepsData as $stepData) {
            $step = $this->deserializeStep($stepData, $newStepsUuids, ['path' => $path, 'order' => $order]);
            $path->addStep($step);
            ++$order;
        }
        /* Removes previous steps that are not used anymore */
        foreach ($oldSteps as $step) {
            if (!in_array($step->getUuid(), $newStepsUuids)) {
                $this->om->remove($step);
            }
        }
    }

    /**
     * @param array $data
     * @param array $newStepsUuids
     * @param array $options
     *
     * @return Step
     */
    private function deserializeStep($data, array &$newStepsUuids = [], array $options = [])
    {
        $newStepsUuids[] = $data['id'];
        $step = $this->stepRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($step)) {
            $step = new Step();
            $step->setUuid($data['id']);
        }
        if (isset($data['title'])) {
            $step->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $step->setDescription($data['description']);
        }
        if (isset($data['poster'])) {
            $step->setPoster($data['poster']['url']);
        }

        if (isset($data['display']) && isset($data['display']['numbering'])) {
            $step->setNumbering($data['display']['numbering']);
        }
        if (isset($data['display']) && isset($data['display']['height'])) {
            $step->setActivityHeight($data['display']['height']);
        }

        /* Set primary resource */
        $resource = isset($data['primaryResource']['id']) ?
            $this->resourceNodeRepo->findOneBy(['uuid' => $data['primaryResource']['id']]) :
            null;
        $step->setResource($resource);

        if (isset($options['path'])) {
            $step->setPath($options['path']);
        }
        if (isset($options['order'])) {
            $step->setOrder($options['order']);
        }
        if (isset($options['parent'])) {
            $step->setParent($options['parent']);
            $step->setLvl($options['parent']->getLvl() + 1);
        } else {
            $step->setLvl(0);
        }

        /* Set children steps */
        $step->emptyChildren();

        if (isset($data['children'])) {
            $order = 0;

            foreach ($data['children'] as $childData) {
                $childOptions = [
                    'path' => $options['path'],
                    'parent' => $step,
                    'order' => $order,
                ];
                $child = $this->deserializeStep($childData, $newStepsUuids, $childOptions);
                $step->addChild($child);
                ++$order;
            }
        }

        /* Set secondary resources */
        $oldSecondaryResources = $step->getSecondaryResources()->toArray();
        $newSecondaryResourcesUuids = [];
        $step->emptySecondaryResources();

        if (isset($data['secondaryResources'])) {
            $order = 0;

            foreach ($data['secondaryResources'] as $resourceData) {
                $resourceOptions = ['order' => $order];
                $secondaryResource = $this->deserializeSecondaryResource(
                    $resourceData,
                    $newSecondaryResourcesUuids,
                    $resourceOptions
                );
                $step->addSecondaryResource($secondaryResource);
                ++$order;
            }
        }
        /* Removes previous secondary resources that are not used anymore */
        foreach ($oldSecondaryResources as $oldResource) {
            if (!in_array($oldResource->getUuid(), $newSecondaryResourcesUuids)) {
                $this->om->remove($oldResource);
            }
        }

        /* Set inherited resources */
        $oldInheritedResources = $step->getInheritedResources()->toArray();
        $newInheritedResourcesUuids = [];
        $step->emptyInheritedResources();

        if (isset($data['inheritedResources'])) {
            $order = 0;

            foreach ($data['inheritedResources'] as $resourceData) {
                $resourceOptions = ['order' => $order];
                $inheritedResource = $this->deserializeInheritedResource(
                    $resourceData,
                    $newInheritedResourcesUuids,
                    $resourceOptions
                );
                $step->addInheritedResource($inheritedResource);
                ++$order;
            }
        }
        /* Removes previous inherited resources that are not used anymore */
        foreach ($oldInheritedResources as $oldResource) {
            if (!in_array($oldResource->getUuid(), $newInheritedResourcesUuids)) {
                $this->om->remove($oldResource);
            }
        }

        return $step;
    }

    /**
     * @param array $data
     * @param array $newSecondaryResourcesUuids
     * @param array $options
     *
     * @return SecondaryResource
     */
    private function deserializeSecondaryResource($data, array &$newSecondaryResourcesUuids = [], array $options = [])
    {
        $newSecondaryResourcesUuids[] = $data['id'];
        $secondaryResource = $this->secondaryResourceRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($secondaryResource)) {
            $secondaryResource = new SecondaryResource();
            $secondaryResource->setUuid($data['id']);

            /** @var ResourceNode $resource */
            $resource = $this->resourceNodeRepo->findOneBy(['uuid' => $data['resource']['id']]);
            $secondaryResource->setResource($resource);
        }
        $secondaryResource->setInheritanceEnabled($data['inheritanceEnabled']);

        if (isset($options['order'])) {
            $secondaryResource->setOrder($options['order']);
        }

        return $secondaryResource;
    }

    /**
     * @param array $data
     * @param array $newInheritedResourcesUuids
     * @param array $options
     *
     * @return InheritedResource
     */
    private function deserializeInheritedResource($data, array &$newInheritedResourcesUuids = [], array $options = [])
    {
        $newInheritedResourcesUuids[] = $data['id'];
        $inheritedResource = $this->inheritedResourceRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($inheritedResource)) {
            $inheritedResource = new InheritedResource();
            $inheritedResource->setUuid($data['id']);
            $inheritedResource->setSourceUuid($data['sourceUuid']);

            /** @var ResourceNode $resource */
            $resource = $this->resourceNodeRepo->findOneBy(['uuid' => $data['resource']['id']]);
            $inheritedResource->setResource($resource);

            /* Set lvl */
            $lvl = isset($data['lvl']) ? $data['lvl'] : 0;
            $inheritedResource->setLvl($lvl);
        }
        if (isset($options['order'])) {
            $inheritedResource->setOrder($options['order']);
        }

        return $inheritedResource;
    }
}

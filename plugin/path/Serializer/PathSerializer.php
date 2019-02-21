<?php

namespace Innova\PathBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
                'openSummary' => $path->getOpenSummary(),
                'numbering' => $path->getNumbering() ? $path->getNumbering() : 'none',
                'manualProgressionAllowed' => $path->isManualProgressionAllowed(),
            ],
            'opening' => [
                'secondaryResources' => $path->getSecondaryResourcesTarget(),
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
        $this->sipe('display.openSummary', 'setOpenSummary', $data, $path);
        $this->sipe('display.numbering', 'setNumbering', $data, $path);
        $this->sipe('display.manualProgressionAllowed', 'setManualProgressionAllowed', $data, $path);

        $this->sipe('opening.secondaryResources', 'setSecondaryResourcesTarget', $data, $path);

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
            'showResourceHeader' => $step->getShowResourceHeader(),
            'secondaryResources' => array_map(function (SecondaryResource $secondaryResource) {
                return $this->resourceNodeSerializer->serialize($secondaryResource->getResource(), [Options::SERIALIZE_MINIMAL]);
            }, $step->getSecondaryResources()->toArray()),
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

    /**
     * @param array $stepsData
     * @param Path  $path
     */
    private function deserializeSteps($stepsData, Path $path)
    {
        $path->emptySteps();

        foreach ($stepsData as $stepIndex => $stepData) {
            $step = $this->deserializeStep($stepData, ['path' => $path, 'order' => $stepIndex]);
            $path->addStep($step);
        }
    }

    /**
     * @param array $data
     * @param array $options
     *
     * @return Step
     */
    private function deserializeStep($data, array $options = [])
    {
        $step = $this->stepRepo->findOneBy(['uuid' => $data['id']]); // TODO : don't call DB, retrieve it from $path

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

        if (isset($data['showResourceHeader'])) {
            $step->setShowResourceHeader($data['showResourceHeader']);
        }

        if (isset($options['path'])) {
            $step->setPath($options['path']);
        }
        if (isset($options['order'])) {
            $step->setOrder($options['order']);
        }
        if (isset($options['parent'])) {
            $step->setParent($options['parent']);
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
            $step->emptyChildren();

            foreach ($data['children'] as $childIndex => $childData) {
                $childOptions = [
                    'path' => $options['path'],
                    'parent' => $step,
                    'order' => $childIndex,
                ];
                $child = $this->deserializeStep($childData, $childOptions);
                $step->addChild($child);
            }
        }

        return $step;
    }
}

<?php

namespace Innova\PathBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Innova\PathBundle\Entity\InheritedResource;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\SecondaryResource;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Used to integrate Path to Claroline resource manager.
 *
 * @DI\Service("innova_path.listener.path")
 */
class PathListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TwigEngine */
    private $templating;

    /** @var TranslatorInterface */
    private $translator;

    /* @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var UserProgressionManager */
    private $userProgressionManager;

    /**
     * PathListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "templating"             = @DI\Inject("templating"),
     *     "translator"             = @DI\Inject("translator"),
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"             = @DI\Inject("claroline.api.serializer"),
     *     "resourceManager"        = @DI\Inject("claroline.manager.resource_manager"),
     *     "userProgressionManager" = @DI\Inject("innova_path.manager.user_progression")
     * })
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param TwigEngine             $templating
     * @param TranslatorInterface    $translator
     * @param ObjectManager          $om
     * @param SerializerProvider     $serializer
     * @param ResourceManager        $resourceManager
     * @param UserProgressionManager $userProgressionManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        TranslatorInterface $translator,
        ObjectManager $om,
        SerializerProvider $serializer,
        ResourceManager $resourceManager,
        UserProgressionManager $userProgressionManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Loads the Path resource.
     *
     * @DI\Observe("resource.innova_path.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Path $path */
        $path = $event->getResource();

        $event->setData([
            'path' => $this->serializer->serialize($path),
            'userEvaluation' => $this->serializer->serialize(
                $this->userProgressionManager->getUpdatedResourceUserEvaluation($path)
            ),
        ]);
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is opened.
     *
     * @DI\Observe("open_innova_path")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /** @var Path $path */
        $path = $event->getResource();

        $content = $this->templating->render(
            'InnovaPathBundle:path:open.html.twig', [
                '_resource' => $path,
                'path' => $this->serializer->serialize($path),
                'userEvaluation' => $this->serializer->serialize(
                    $this->userProgressionManager->getUpdatedResourceUserEvaluation($path)
                ),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is deleted.
     *
     * @DI\Observe("delete_innova_path")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is duplicated.
     *
     * @DI\Observe("copy_innova_path")
     *
     * @param CopyResourceEvent $event
     *
     * @throws \Exception
     */
    public function onCopy(CopyResourceEvent $event)
    {
        // Start the transaction. We'll copy every resource in one go that way.
        $this->om->startFlushSuite();

        $parent = $event->getParent();

        /** @var Path $pathToCopy */
        $pathToCopy = $event->getResource();
        $pathNodes = [];
        $nodesCopy = [];

        foreach ($pathToCopy->getSteps() as $step) {
            $this->retrieveStepNodes($step, $pathNodes);
        }
        if (count($pathNodes) > 0) {
            $resourcesCopyDir = $this->createResourcesCopyDirectory($parent, $pathToCopy->getResourceNode()->getName());
            // A forced flush is required for rights purpose on the copied resources
            $this->om->forceFlush();
            $nodesCopy = $this->copyResources($pathNodes, $resourcesCopyDir->getResourceNode());
        }

        // Create new Path
        $path = new Path();

        // Set up new Path properties
        $path->setName($pathToCopy->getName());
        $path->setDescription($pathToCopy->getDescription());
        $path->setShowOverview($pathToCopy->getShowOverview());
        $path->setShowSummary($pathToCopy->getShowSummary());
        $path->setOpenSummary($pathToCopy->getOpenSummary());
        $path->setNumbering($pathToCopy->getNumbering());
        $path->setStructure('');

        $stepsMapping = [];

        foreach ($pathToCopy->getRootSteps() as $step) {
            $this->copyStep($step, $path, $nodesCopy, $stepsMapping);
        }

        $this->om->persist($path);

        // End the transaction
        $this->om->endFlushSuite();
        $event->setCopy($path);

        $event->stopPropagation();
    }

    /**
     * Store in given array all resource nodes present in step.
     *
     * @param Step  $step
     * @param array $nodes
     */
    private function retrieveStepNodes(Step $step, array &$nodes)
    {
        $primaryResource = $step->getResource();
        $secondaryResources = $step->getSecondaryResources();

        if (!empty($primaryResource)) {
            $nodes[$primaryResource->getGuid()] = $primaryResource;
        }
        foreach ($secondaryResources as $secondaryResource) {
            $node = $secondaryResource->getResource();
            $nodes[$node->getGuid()] = $node;
        }
        foreach ($step->getChildren() as $child) {
            $this->retrieveStepNodes($child, $nodes);
        }
    }

    /**
     * Create directory to store copies of resources.
     *
     * @param ResourceNode $destination
     * @param string       $pathName
     *
     * @return AbstractResource
     */
    private function createResourcesCopyDirectory(ResourceNode $destination, $pathName)
    {
        // Get current User
        $user = $this->tokenStorage->getToken()->getUser();

        $resourcesDir = $this->resourceManager->createResource(
            'Claroline\CoreBundle\Entity\Resource\Directory',
            $pathName.' ('.$this->translator->trans('resources', [], 'platform').')'
        );

        return $this->resourceManager->create(
            $resourcesDir,
            $destination->getResourceType(),
            $user,
            $destination->getWorkspace(),
            $destination
        );
    }

    /**
     * Copy all given resources nodes.
     *
     * @param array        $resources
     * @param ResourceNode $newParent
     *
     * @return array $resourcesCopy
     */
    private function copyResources(array $resources, ResourceNode $newParent)
    {
        // Get current User
        $user = $this->tokenStorage->getToken()->getUser();

        $resourcesCopy = [];
        foreach ($resources as $resourceNode) {
            $copy = $this->resourceManager->copy($resourceNode, $newParent, $user);
            $resourcesCopy[$resourceNode->getGuid()] = $copy->getResourceNode();
        }

        return $resourcesCopy;
    }

    /**
     * Copy a step.
     *
     * @param Step  $step         The step to copy
     * @param Path  $pathCopy     The copied path
     * @param array $nodesCopy    The list of all copied resources nodes
     * @param array $stepsMapping The mapping between uuid of old steps and uuid of new ones. Useful for InheritedResources.
     * @param Step  $parent       The step parent of the copy
     */
    private function copyStep(Step $step, Path $pathCopy, array $nodesCopy, array &$stepsMapping, Step $parent = null)
    {
        $stepCopy = new Step();
        $stepCopy->setParent($parent);
        $stepCopy->setPath($pathCopy);
        $stepCopy->setTitle($step->getTitle());
        $stepCopy->setDescription($step->getDescription());
        $stepCopy->setPoster($step->getPoster());
        $stepCopy->setNumbering($step->getNumbering());
        $stepCopy->setLvl($step->getLvl());
        $stepCopy->setOrder($step->getOrder());

        $stepsMapping[$step->getUuid()] = $stepCopy->getUuid();

        $primaryResource = $step->getResource();

        if (!empty($primaryResource) && isset($nodesCopy[$primaryResource->getGuid()])) {
            $stepCopy->setResource($nodesCopy[$primaryResource->getGuid()]);
        }
        foreach ($step->getSecondaryResources() as $secondaryResource) {
            $resource = $secondaryResource->getResource();

            if (isset($nodesCopy[$resource->getGuid()])) {
                $secondaryResourceCopy = new SecondaryResource();
                $secondaryResourceCopy->setStep($stepCopy);
                $secondaryResourceCopy->setResource($nodesCopy[$resource->getGuid()]);
                $secondaryResourceCopy->setOrder($secondaryResource->getOrder());
                $secondaryResourceCopy->setInheritanceEnabled($secondaryResource->isInheritanceEnabled());
                $this->om->persist($secondaryResourceCopy);
            }
        }
        foreach ($step->getInheritedResources() as $inheritedResource) {
            $resource = $inheritedResource->getResource();

            if (isset($nodesCopy[$resource->getGuid()])) {
                $inheritedResourceCopy = new InheritedResource();
                $inheritedResourceCopy->setStep($stepCopy);
                $inheritedResourceCopy->setResource($nodesCopy[$resource->getGuid()]);
                $inheritedResourceCopy->setOrder($inheritedResource->getOrder());
                $inheritedResourceCopy->setLvl($inheritedResource->getLvl());

                if (isset($stepsMapping[$inheritedResource->getSourceUuid()])) {
                    $inheritedResource->setSourceUuid($stepsMapping[$inheritedResource->getSourceUuid()]);
                }
                $this->om->persist($inheritedResourceCopy);
            }
        }
        foreach ($step->getChildren() as $child) {
            $this->copyStep($child, $pathCopy, $nodesCopy, $stepsMapping, $stepCopy);
        }
        $this->om->persist($stepCopy);
    }
}

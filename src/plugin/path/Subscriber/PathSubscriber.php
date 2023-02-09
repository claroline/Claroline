<?php

namespace Innova\PathBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\EvaluationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Used to integrate Path to Claroline resource manager.
 */
class PathSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;
    /* @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var EvaluationManager */
    private $evaluationManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ObjectManager $om,
        Crud $crud,
        SerializerProvider $serializer,
        ResourceManager $resourceManager,
        EvaluationManager $evaluationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->om = $om;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->evaluationManager = $evaluationManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'resource.innova_path.load' => 'onLoad',
            'resource.innova_path.copy' => 'onCopy',
            EvaluationEvents::RESOURCE_EVALUATION => 'onEvaluation',
        ];
    }

    /**
     * Loads the Path resource.
     */
    public function onLoad(LoadResourceEvent $event): void
    {
        /** @var Path $path */
        $path = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        $evaluation = null;
        $resourceEvaluations = [];
        $currentAttempt = null;
        $stepsProgression = [];
        if ($user instanceof User) {
            // retrieve user progression
            $evaluation = $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($path, $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            );

            $currentAttempt = $this->serializer->serialize($this->evaluationManager->getCurrentAttempt($path, $user));

            $resourceEvaluations = array_map(function (ResourceUserEvaluation $resourceEvaluation) {
                return $this->serializer->serialize($resourceEvaluation);
            }, $this->evaluationManager->getRequiredEvaluations($path, $user));

            $stepsProgression = $this->evaluationManager->getStepsProgressionForUser($path, $user);
        }

        $event->setData([
            'path' => $this->serializer->serialize($path),
            'userEvaluation' => $evaluation,
            'resourceEvaluations' => $resourceEvaluations,
            'attempt' => $currentAttempt,
            'stepsProgression' => $stepsProgression,
        ]);
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is duplicated.
     */
    public function onCopy(CopyResourceEvent $event): void
    {
        // Start the transaction. We'll copy every resource in one go that way.
        $this->om->startFlushSuite();

        /** @var Path $path */
        $path = $event->getCopy();
        $pathNode = $path->getResourceNode();

        if ($path->hasResources()) {
            // create a directory to store copied resources
            $resourcesDirectory = $this->createResourcesCopyDirectory($pathNode->getParent(), $pathNode->getName());
            // A forced flush is required for rights propagation on the copied resources
            $this->om->forceFlush();

            $copiedResources = [];

            if (!empty($path->getOverviewResource())) {
                $copiedResources = $this->copyResource($path->getOverviewResource(), $resourcesDirectory->getResourceNode(), $copiedResources);

                // replace resource by the copy
                $path->setOverviewResource($copiedResources[$path->getOverviewResource()->getUuid()]);
            }

            // copy resources for all steps
            foreach ($path->getSteps() as $step) {
                if ($step->hasResources()) {
                    $copiedResources = $this->copyStepResources($step, $resourcesDirectory->getResourceNode(), $copiedResources);
                }
            }
        }

        $this->om->persist($path);

        // End the transaction
        $this->om->endFlushSuite();
        $event->setCopy($path);

        $event->stopPropagation();
    }

    /**
     * Fired when a Resource Evaluation with a score is created.
     * We will update progression for all paths using this resource.
     */
    public function onEvaluation(ResourceEvaluationEvent $event): void
    {
        $user = $event->getUser();
        $resourceNode = $event->getResourceNode();

        if ($resourceNode->isRequired()) {
            // check if the resource is linked to any path
            $paths = $this->om->getRepository(Path::class)->findByPrimaryResource($resourceNode);
            foreach ($paths as $path) {
                // update evaluations for all the path using the resource
                $this->evaluationManager->compute($path, $user);
            }
        }
    }

    /**
     * Create directory to store copies of resources.
     */
    private function createResourcesCopyDirectory(ResourceNode $destination, string $pathName): AbstractResource
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $resourcesDir = new Directory();
        $resourcesDir->setName($pathName.' ('.$this->translator->trans('resources', [], 'platform').')');

        return $this->resourceManager->create(
            $resourcesDir,
            $destination->getResourceType(),
            $user,
            $destination->getWorkspace(),
            $destination
        );
    }

    private function copyStepResources(Step $step, ResourceNode $destination, array $copiedResources = []): array
    {
        // copy primary resource
        if (!empty($step->getResource())) {
            $resourceNode = $step->getResource();

            $copiedResources = $this->copyResource($resourceNode, $destination, $copiedResources);

            // replace resource by the copy
            $step->setResource($copiedResources[$resourceNode->getUuid()]);
        }

        // copy secondary resources
        if (!empty($step->getSecondaryResources())) {
            foreach ($step->getSecondaryResources() as $secondaryResource) {
                $resourceNode = $secondaryResource->getResource();
                $copiedResources = $this->copyResource($resourceNode, $destination, $copiedResources);

                // replace resource by the copy
                $secondaryResource->setResource($copiedResources[$resourceNode->getUuid()]);
            }
        }

        return $copiedResources;
    }

    private function copyResource(ResourceNode $resourceNode, ResourceNode $destination, array $copiedResources): array
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!isset($copiedResources[$resourceNode->getUuid()])) {
            // resource not already copied, create a new copy
            $resourceCopy = $this->crud->copy($resourceNode, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['user' => $user, 'parent' => $destination]);

            if ($resourceCopy) {
                $copiedResources[$resourceNode->getUuid()] = $resourceCopy;
            }
        }

        return $copiedResources;
    }
}

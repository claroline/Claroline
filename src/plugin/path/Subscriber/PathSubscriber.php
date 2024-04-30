<?php

namespace Innova\PathBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\EvaluationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Used to integrate Path to Claroline resource manager.
 */
class PathSubscriber extends ResourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SerializerProvider $serializer,
        private readonly ResourceManager $resourceManager,
        private readonly EvaluationManager $evaluationManager
    ) {
    }

    public static function getName(): string
    {
        return 'innova_path';
    }

    public static function getSubscribedEvents(): array
    {
        return array_merge([], parent::getSubscribedEvents(), [
            EvaluationEvents::RESOURCE_EVALUATION => 'onEvaluation',
        ]);
    }

    /** @var Path $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $evaluation = null;
        $resourceEvaluations = [];
        $currentAttempt = null;
        $stepsProgression = [];
        if ($user instanceof User) {
            // retrieve user progression
            $evaluation = $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($resource, $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            );

            $currentAttempt = $this->serializer->serialize($this->evaluationManager->getCurrentAttempt($resource, $user));

            $resourceEvaluations = array_map(function (ResourceUserEvaluation $resourceEvaluation) {
                return $this->serializer->serialize($resourceEvaluation);
            }, $this->evaluationManager->getRequiredEvaluations($resource, $user));

            $stepsProgression = $this->evaluationManager->getStepsProgressionForUser($resource, $user);
        }

        return [
            'path' => $this->serializer->serialize($resource),
            'userEvaluation' => $evaluation,
            'resourceEvaluations' => $resourceEvaluations,
            'attempt' => $currentAttempt,
            'stepsProgression' => $stepsProgression,
        ];
    }

    /**
     * @param Path $original
     * @param Path $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->om->startFlushSuite();

        $pathNode = $copy->getResourceNode();
        if ($copy->hasResources()) {
            // create a directory to store copied resources
            $resourcesDirectory = $this->createResourcesCopyDirectory($pathNode->getParent(), $pathNode->getName());
            // A forced flush is required for rights propagation on the copied resources
            $this->om->forceFlush();

            $copiedResources = [];

            if (!empty($copy->getOverviewResource())) {
                $copiedResources = $this->copyResource($copy->getOverviewResource(), $resourcesDirectory->getResourceNode(), $copiedResources);

                // replace resource by the copy
                $copy->setOverviewResource($copiedResources[$copy->getOverviewResource()->getUuid()]);
            }

            // copy resources for all steps
            foreach ($copy->getSteps() as $step) {
                if ($step->hasResources()) {
                    $copiedResources = $this->copyStepResources($step, $resourcesDirectory->getResourceNode(), $copiedResources);
                }
            }
        }

        $this->om->persist($copy);

        $this->om->endFlushSuite();
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

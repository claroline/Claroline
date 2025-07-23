<?php

namespace Claroline\EvaluationBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\CodeNormalizer;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\Sequence\Step;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SequenceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Sequence::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Sequence::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, Sequence::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Sequence::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::PRE_COPY, Sequence::class) => 'preCopy',
            CrudEvents::getEventName(CrudEvents::POST_COPY, Sequence::class) => 'postCopy',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Sequence::class) => 'preDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();

        $sequence->setCreatedAt(new \DateTime());
        $sequence->setUpdatedAt(new \DateTime());

        // make sure the resource code is unique
        $sequenceCode = $this->om->getRepository(Sequence::class)->findNextUnique(
            'code',
            $sequence->getCode() ?? CodeNormalizer::normalize($sequence->getName())
        );
        $sequence->setCode($sequenceCode);

        // set the creator of the resource
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User) {
            $sequence->setCreator($user);
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();

        if ($sequence->getPoster()) {
            $this->fileManager->linkFile(Sequence::class, $sequence->getUuid(), $sequence->getPoster());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();

        $sequence->setUpdatedAt(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Sequence::class,
            $sequence->getUuid(),
            $sequence->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Sequence $sequence */
        $sequence = $event->getObject();

        if ($sequence->getPoster()) {
            $this->fileManager->unlinkFile(Sequence::class, $sequence->getUuid(), $sequence->getPoster());
        }
    }

    public function preCopy(CopyEvent $event): void
    {
        /** @var Sequence $newSequence */
        $newSequence = $event->getCopy();

        $newSequence->setCreatedAt(new \DateTime());
        $newSequence->setUpdatedAt(new \DateTime());

        // make sure the resource code is unique
        $sequenceCode = $this->om->getRepository(Sequence::class)->findNextUnique(
            'code',
            $newSequence->getCode() ?? CodeNormalizer::normalize($newSequence->getName())
        );
        $newSequence->setCode($sequenceCode);

        if (!empty($event->getExtra('workspace'))) {
            /** @var Workspace $newWorkspace */
            $newWorkspace = $event->getExtra('workspace');
            $newSequence->setWorkspace($newWorkspace);
        }

        // set the creator of the copy
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User) {
            $newSequence->setCreator($user);
        }
    }

    public function postCopy(CopyEvent $event): void
    {
        /** @var Sequence $newSequence */
        $newSequence = $event->getCopy();
        $options = $event->getOptions();

        if ($newSequence->getPoster()) {
            $this->fileManager->linkFile(Sequence::class, $newSequence->getUuid(), $newSequence->getPoster());
        }

        if (in_array('copyResources', $options) && $newSequence->hasResources()) {
            $parent = null;
            if (!empty($event->getExtra('workspace'))) {
                /** @var Workspace $newWorkspace */
                $newWorkspace = $event->getExtra('workspace');
                $parent = $this->om->getRepository(ResourceNode::class)->findOneBy([
                    'workspace' => $newWorkspace,
                    'parent' => null,
                ]);
            }

            $copiedResources = [];

            if (!empty($newSequence->getOverviewResource())) {
                $copiedResources = $this->copyResource($newSequence->getOverviewResource(), $parent, $copiedResources);

                // replace resource by the copy
                $newSequence->setOverviewResource($copiedResources[$newSequence->getOverviewResource()->getUuid()]);
            }

            // copy resources for all steps
            foreach ($newSequence->getSteps() as $step) {
                if ($step->hasResources()) {
                    $copiedResources = $this->copyStepResources($step, $parent, $copiedResources);
                }
            }
        }

        $this->om->persist($newSequence);
        $this->om->flush();
    }

    private function copyStepResources(Step $step, ?ResourceNode $parent = null, array $copiedResources = []): array
    {
        // copy primary resource
        if (!empty($step->getResource())) {
            $resourceNode = $step->getResource();

            $copiedResources = $this->copyResource($resourceNode, $parent, $copiedResources);

            // replace resource by the copy
            $step->setResource($copiedResources[$resourceNode->getUuid()]);
            $this->om->persist($step);
        }

        // copy secondary resources
        if (!empty($step->getSecondaryResources())) {
            foreach ($step->getSecondaryResources() as $secondaryResource) {
                $resourceNode = $secondaryResource->getResource();
                $copiedResources = $this->copyResource($resourceNode, $parent, $copiedResources);

                // replace resource by the copy
                $secondaryResource->setResource($copiedResources[$resourceNode->getUuid()]);
            }

            $this->om->persist($step);
        }

        return $copiedResources;
    }

    private function copyResource(ResourceNode $resourceNode, ?ResourceNode $parent = null, ?array $copiedResources = []): array
    {
        if (!isset($copiedResources[$resourceNode->getUuid()])) {
            // resource is not yet copied, create a new copy
            $resourceCopy = $this->crud->copy($resourceNode, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['parent' => $parent]);

            if ($resourceCopy) {
                $copiedResources[$resourceNode->getUuid()] = $resourceCopy;
            }
        }

        return $copiedResources;
    }
}

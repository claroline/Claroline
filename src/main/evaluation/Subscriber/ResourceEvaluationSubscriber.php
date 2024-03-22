<?php

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\UpdateResourceEvaluations;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ResourceEvaluationSubscriber implements EventSubscriberInterface
{
    private UserRepository $userRepo;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        ObjectManager $om,
        private readonly SecurityManager $securityManager,
        private readonly ResourceEvaluationManager $manager
    ) {
        $this->userRepo = $om->getRepository(User::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', ResourceNode::class) => 'createEvaluations',
            Crud::getEventName('update', 'post', ResourceNode::class) => 'updateEvaluations',
            ResourceEvents::OPEN => ['open', 10],
            Crud::getEventName('delete', 'pre', ResourceEvaluation::class) => 'updateNbAttempts',
        ];
    }

    public function createEvaluations(CreateEvent $event): void
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();

        if ($resourceNode->isRequired()) {
            $registeredUsers = $this->userRepo->findByWorkspaces([$resourceNode->getWorkspace()]);
            if (!empty($registeredUsers)) {
                $registeredUserIds = array_map(function (User $user) {
                    return $user->getId();
                }, $registeredUsers);

                $this->messageBus->dispatch(
                    new UpdateResourceEvaluations($resourceNode->getId(), $registeredUserIds, AbstractEvaluation::STATUS_TODO)
                );
            }
        }
    }

    public function updateEvaluations(UpdateEvent $event): void
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();
        $oldData = $event->getOldData();

        if (empty($oldData['evaluation']) || $resourceNode->isRequired() !== $oldData['evaluation']['required']) {
            $registeredUsers = $this->userRepo->findByWorkspaces([$resourceNode->getWorkspace()]);
            if (!empty($registeredUsers)) {
                $registeredUserIds = array_map(function (User $user) {
                    return $user->getId();
                }, $registeredUsers);

                if ($resourceNode->isRequired()) {
                    $this->messageBus->dispatch(
                        new UpdateResourceEvaluations($resourceNode->getId(), $registeredUserIds, AbstractEvaluation::STATUS_TODO)
                    );
                } else {
                    $this->messageBus->dispatch(
                        new UpdateResourceEvaluations($resourceNode->getId(), $registeredUserIds, AbstractEvaluation::STATUS_NOT_ATTEMPTED, false)
                    );
                }
            }
        }
    }

    public function open(LoadResourceEvent $event): void
    {
        // Update current user evaluation
        if ($this->securityManager->getCurrentUser()) {
            $this->manager->updateUserEvaluation(
                $event->getResourceNode(),
                $this->securityManager->getCurrentUser(),
                ['status' => AbstractEvaluation::STATUS_OPENED]
            );
        }
    }

    public function updateNbAttempts(DeleteEvent $event): void
    {
        /** @var ResourceEvaluation $resourceAttempt */
        $resourceAttempt = $event->getObject();

        $evaluation = $resourceAttempt->getResourceUserEvaluation();
        if ($evaluation) {
            $evaluation->setNbAttempts($evaluation->getNbAttempts() - 1);
        }
    }
}

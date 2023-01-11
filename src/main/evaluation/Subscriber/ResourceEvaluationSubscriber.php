<?php

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\UpdateResourceEvaluations;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ResourceEvaluationSubscriber implements EventSubscriberInterface
{
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var ResourceEvaluationManager */
    private $manager;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        MessageBusInterface $messageBus,
        ObjectManager $om,
        ResourceEvaluationManager $manager
    ) {
        $this->messageBus = $messageBus;
        $this->manager = $manager;

        $this->userRepo = $om->getRepository(User::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', ResourceNode::class) => 'createEvaluations',
            Crud::getEventName('update', 'post', ResourceNode::class) => 'updateEvaluations',
            ResourceEvents::RESOURCE_OPEN => ['open', 10],
        ];
    }

    public function createEvaluations(CreateEvent $event)
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

    public function updateEvaluations(UpdateEvent $event)
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $event->getObject();
        $oldData = $event->getOldData();

        if ((empty($oldData['evaluation']) || $resourceNode->isRequired() !== $oldData['evaluation']['required'])) {
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

    public function open(LoadResourceEvent $event)
    {
        // Update current user evaluation
        if ($event->getUser() instanceof User) {
            $this->manager->updateUserEvaluation(
                $event->getResourceNode(),
                $event->getUser(),
                ['status' => AbstractEvaluation::STATUS_OPENED]
            );
        }
    }
}

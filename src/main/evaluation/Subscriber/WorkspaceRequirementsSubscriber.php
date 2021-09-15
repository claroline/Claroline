<?php

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\EvaluationBundle\Messenger\Message\AddWorkspaceRequirements;
use Claroline\EvaluationBundle\Messenger\Message\RemoveWorkspaceRequirements;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WorkspaceRequirementsSubscriber implements EventSubscriberInterface
{
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var WorkspaceRepository */
    private $workspaceRepo;

    public function __construct(
        MessageBusInterface $messageBus,
        ObjectManager $om
    ) {
        $this->messageBus = $messageBus;

        $this->workspaceRepo = $om->getRepository(Workspace::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::ADD_ROLE => 'addRequirements',
            SecurityEvents::REMOVE_ROLE => 'removeRequirements',
        ];
    }

    public function addRequirements(AddRoleEvent $event)
    {
        $role = $event->getRole();

        // sets requirements for all the workspaces accessible by the role
        $workspaces = $this->workspaceRepo->findByRoles([$role->getName()]);
        foreach ($workspaces as $workspace) {
            $this->messageBus->dispatch(new AddWorkspaceRequirements($workspace, $role, $event->getUsers()));
        }
    }

    public function removeRequirements(RemoveRoleEvent $event)
    {
        $this->messageBus->dispatch(new RemoveWorkspaceRequirements($event->getRole(), $event->getUsers()));
    }
}

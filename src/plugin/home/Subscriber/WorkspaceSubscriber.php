<?php

namespace Claroline\HomeBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('delete', 'pre', Workspace::class) => 'preDelete',
        ];
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $tabs = $this->om->getRepository(HomeTab::class)->findBy([
            'contextId' => $workspace->getContextIdentifier(),
        ]);

        foreach ($tabs as $tab) {
            $this->crud->delete($tab);
        }
    }
}

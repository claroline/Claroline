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
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(ObjectManager $om, Crud $crud)
    {
        $this->om = $om;
        $this->crud = $crud;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('delete', 'pre', Workspace::class) => 'preDelete',
        ];
    }

    public function preDelete(DeleteEvent $event)
    {
        $workspace = $event->getObject();

        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['workspace' => $workspace]);
        foreach ($tabs as $tab) {
            $this->crud->delete($tab);
        }
    }
}

<?php

namespace Claroline\HomeBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HomeTabSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly Crud $crud
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, HomeTab::class) => 'deleteConfiguration',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Workspace::class) => 'deleteWorkspaceTabs',
        ];
    }

    public function deleteConfiguration(DeleteEvent $event): void
    {
        /** @var HomeTab $homeTab */
        $homeTab = $event->getObject();
        $parametersClass = $homeTab->getClass();

        // loads configuration entity for the current instance
        $typeParameters = $this->om
            ->getRepository($parametersClass)
            ->findOneBy(['tab' => $homeTab]);

        if ($typeParameters) {
            $this->om->remove($typeParameters);
        }
    }

    public function deleteWorkspaceTabs(DeleteEvent $event): void
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

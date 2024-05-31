<?php

namespace Claroline\OpenBadgeBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
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
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Workspace::class) => 'removeWorkspaceBadges',
        ];
    }

    public function removeWorkspaceBadges(DeleteEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $wsBadges = $this->om->getRepository(BadgeClass::class)->findBy(['workspace' => $workspace]);
        if (!empty($wsBadges)) {
            $this->crud->deleteBulk($wsBadges, [Crud::NO_PERMISSIONS]);
        }
    }
}

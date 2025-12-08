<?php

namespace Claroline\OpenBadgeBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
            CrudEvents::getEventName(CrudEvents::POST_PATCH, Workspace::class) => 'syncWorkspaceOrganizations',
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

    public function syncWorkspaceOrganizations(PatchEvent $event): void
    {
        $workspace = $event->getObject();
        $element = $event->getValue();
        if (!$element instanceof Organization) {
            return;
        }

        $this->om->startFlushSuite();
        $badges = $this->om->getRepository(BadgeClass::class)->findBy(['workspace' => $workspace]);
        foreach ($badges as $badge) {
            $this->crud->patch($badge, 'organization', $event->getAction(), [$element]);
        }
        $this->om->endFlushSuite();
    }
}

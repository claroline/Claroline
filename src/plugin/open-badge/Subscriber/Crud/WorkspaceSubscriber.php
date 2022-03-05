<?php

namespace Claroline\OpenBadgeBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(
        ObjectManager $om,
        Crud $crud
    ) {
        $this->om = $om;
        $this->crud = $crud;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('delete', 'pre', Workspace::class) => 'removeWorkspaceBadges',
        ];
    }

    public function removeWorkspaceBadges(DeleteEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $wsBadges = $this->om->getRepository(BadgeClass::class)->findBy(['workspace' => $workspace]);
        if (!empty($wsBadges)) {
            $this->crud->deleteBulk($wsBadges, [Crud::NO_PERMISSIONS]);
        }
    }
}

<?php

namespace Claroline\AgendaBundle\Listener;

use Claroline\AgendaBundle\Manager\AgendaManager;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var AgendaManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.agenda_manager")
     * })
     *
     * @param AgendaManager $manager
     */
    public function __construct(AgendaManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Event nodes
        $eventCount = $this->manager->replaceEventUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineAgendaBundle] updated Event count: $eventCount");

        // Replace user of EventInvitation nodes
        $eventInvitationCount = $this->manager->replaceEventInvitationUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineAgendaBundle] updated EventInvitation count: $eventInvitationCount");
    }
}

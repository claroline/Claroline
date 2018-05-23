<?php

namespace FormaLibre\PresenceBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use FormaLibre\PresenceBundle\Manager\PresenceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var PresenceManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("formalibre.manager.presence_manager")
     * })
     *
     * @param Manager $manager
     */
    public function __construct(PresenceManager $manager)
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
        // Replace UserTeacher of Presence nodes
        $presenceUTCount = $this->manager->replaceUserTeacher($event->getRemoved(), $event->getKept());
        $event->addMessage("[FormaLibrePresenceBundle] updated Teacher Presence count: $presenceUTCount");

        // Replace UserStudent of Presence nodes
        $presenceUSCount = $this->manager->replaceUserStudent($event->getRemoved(), $event->getKept());
        $event->addMessage("[FormaLibrePresenceBundle] updated Student Presence count: $presenceUSCount");
    }
}

<?php

namespace Claroline\CursusBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CursusBundle\Manager\CursusManager;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var CursusManager */
    private $cursusManager;

    /**
     * @param CursusManager $cursusManager
     */
    public function __construct(CursusManager $cursusManager)
    {
        $this->cursusManager = $cursusManager;
    }

    /**
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        //SessionEvent
        //SessionEventUser
        //CourseRegistrationQueue
        //Course
        //CourseSessionUser
        //CourseSession
        //CourseSessionsRegistrationQueue
        //CursusUser
        //SessionEventComment

        $event->addMessage('[ClarolineCursusBundle]');
    }
}

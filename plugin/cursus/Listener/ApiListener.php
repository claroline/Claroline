<?php

namespace Claroline\CursusBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var CursusManager */
    private $cursusManager;

    /**
     * @DI\InjectParams({
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager")
     * })
     *
     * @param CursusManager $cursusManager
     */
    public function __construct(CursusManager $cursusManager)
    {
        $this->cursusManager = $cursusManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
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

<?php

namespace Claroline\AuthenticationBundle\Listener\Oauth;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        //OauthUser

        $event->addMessage('[ClarolineAuthenticationBundle]');
    }
}

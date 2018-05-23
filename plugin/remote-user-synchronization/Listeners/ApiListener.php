<?php

namespace Claroline\RemoteUserSynchronizationBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\RemoteUserSynchronizationBundle\Manager\RemoteUserTokenManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var RemoteUserTokenManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.remote_user_token_manager")
     * })
     *
     * @param Manager $manager
     */
    public function __construct(RemoteUserTokenManager $manager)
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
        // Replace user of RemoteUserToken nodes
        $remoteUserTokenCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineRemoteUserSynchronizationBundle] updated RemoteUserToken count: $remoteUserTokenCount");
    }
}

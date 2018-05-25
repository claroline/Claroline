<?php

namespace Claroline\ChatBundle\Listener;

use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var ChatManager */
    private $chatManager;

    /**
     * @DI\InjectParams({
     *     "chatManager" = @DI\Inject("claroline.manager.chat_manager")
     * })
     *
     * @param ChatManager $chatManager
     */
    public function __construct(ChatManager $chatManager)
    {
        $this->chatManager = $chatManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of ChatUser nodes
        $chatUserCount = $this->chatManager->replaceChatUserUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineChatBundle] updated CasUser count: $chatUserCount");
    }
}

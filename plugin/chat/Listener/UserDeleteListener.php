<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Listener;

use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Event\DeleteUserEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class UserDeleteListener
{
    private $chatManager;
    private $om;

    /**
     * @DI\InjectParams({
     *     "chatManager" = @DI\Inject("claroline.manager.chat_manager"),
     *     "om"          = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ChatManager $chatManager, ObjectManager $om)
    {
        $this->chatManager = $chatManager;
        $this->om = $om;
    }

    /**
     * @DI\Observe("delete_user")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onUserDelete(DeleteUserEvent $event)
    {
        $user = $event->getUser();
        $chatUser = $this->chatManager->getChatUserByUser($user);
        if ($chatUser) {
            $this->chatManager->deleteChatUser($chatUser);
        }
    }
}

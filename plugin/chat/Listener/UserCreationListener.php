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
use Claroline\CoreBundle\Event\UserCreatedEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class UserCreationListener
{
    private $chatManager;

    /**
     * @DI\InjectParams({
     *     "chatManager" = @DI\Inject("claroline.manager.chat_manager")
     * })
     */
    public function __construct(ChatManager $chatManager)
    {
        $this->chatManager = $chatManager;
    }

    /**
     * @DI\Observe("user_created_event")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onUserCreated(UserCreatedEvent $event)
    {
        $user = $event->getUser();

        if ($this->chatManager->isConfigured()) {
            $this->chatManager->importUser($user);
        }
    }
}

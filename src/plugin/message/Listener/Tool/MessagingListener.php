<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Listener\Tool;

use Claroline\CoreBundle\Event\Tool\ConfigureToolEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\MessageBundle\Manager\ContactManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Messaging tool.
 */
class MessagingListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ContactManager */
    private $contactManager;

    /**
     * ContactsListener constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContactManager $contactManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->contactManager = $contactManager;
    }

    /**
     * Displays messaging on Desktop.
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }

    /**
     * Configures messaging on Desktop.
     */
    public function onConfigureDesktop(ConfigureToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}

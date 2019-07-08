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

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\MessageBundle\Manager\ContactManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Messaging tool.
 *
 * @DI\Service()
 */
class MessagingListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ContactManager */
    private $contactManager;

    /**
     * ContactsListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "contactManager" = @DI\Inject("claroline.manager.contact_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ContactManager        $contactManager
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
     *
     * @DI\Observe("open_tool_desktop_messaging")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->setData([
            'options' => $this->contactManager->getUserOptions(
                $this->tokenStorage->getToken()->getUser()
            ),
        ]);
        $event->stopPropagation();
    }
}

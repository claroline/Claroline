<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class MessageListener
{
    private $messageManager;
    private $securityContext;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "messageManager"  = @DI\Inject("claroline.manager.message_manager"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        MessageManager $messageManager,
        SecurityContextInterface $securityContext,
        TranslatorInterface $translator
    )
    {
        $this->messageManager = $messageManager;
        $this->securityContext = $securityContext;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure_desktop_tool_message")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarLeftMenuConfigureMessage(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();
        $tool = $event->getTool();

        if ($user !== 'anon.') {
            $countUnreadMessages = $this->messageManager->getNbUnreadMessages($user);
            $messageTitle = $this->translator->trans(
                'new_message_alert',
                array('%count%' => $countUnreadMessages),
                'platform'
            );
            $menu = $event->getMenu();
            $messageMenuLink = $menu->addChild(
                $this->translator->trans('messages', array(), 'platform'),
                array('route' => 'claro_message_list_received')
            )->setExtra('icon', 'fa fa-' . $tool->getClass())
            ->setExtra('title', $messageTitle);

            if ($countUnreadMessages > 0) {
                $messageMenuLink->setExtra('badge', $countUnreadMessages);
            }

            return $menu;
        }
    }

    /**
     * @DI\Observe("claroline_message_sending")
     *
     * @param Claroline\CoreBundle\Event\SendMessageEvent $event
     */
    public function onMessageSending(SendMessageEvent $event)
    {
        $receiver = $event->getReceiver();
        $sender = $event->getSender();
        $content = $event->getContent();
        $object = $event->getObject();
        $this->messageManager->sendMessageToAbstractRoleSubject(
            $receiver,
            $content,
            $object,
            $sender
        );
    }
}

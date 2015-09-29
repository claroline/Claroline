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
use Claroline\CoreBundle\Menu\ContactAdditionalActionEvent;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class MessageListener
{
    private $messageManager;
    private $router;
    private $tokenStorage;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "messageManager"  = @DI\Inject("claroline.manager.message_manager"),
     *     "router"          = @DI\Inject("router"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        MessageManager $messageManager,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    )
    {
        $this->messageManager = $messageManager;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure_desktop_tool_message")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onTopBarLeftMenuConfigureMessage(ConfigureMenuEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();
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
     * @DI\Observe("claroline_workspace_users_action")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onWorkspaceUsersConfigureMessage(ContactAdditionalActionEvent $event)
    {
        $user = $event->getUser();

            $menu = $event->getMenu();
            $messageMenuLink = $menu->addChild(
                $this->translator->trans('messages', array(), 'platform'),
                array('route' => 'claro_message_show')
            )
            ->setExtra('icon', 'fa fa-envelope')
            ->setExtra('qstring', 'userIds[]=' . $user->getId())
            ->setExtra('title', $this->translator->trans('message', array(), 'platform'));
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
        $withMail = $event->getWithMail();
        $this->messageManager->sendMessageToAbstractRoleSubject(
            $receiver,
            $content,
            $object,
            $sender,
            $withMail
        );
    }

    /**
     * @DI\Observe("claroline_message_sending_to_users")
     *
     * @param Claroline\CoreBundle\Event\SendMessageEvent $event
     */
    public function onMessageSendingToUsers(SendMessageEvent $event)
    {
        $users = $event->getUsers();
        $sender = $event->getSender();
        $content = $event->getContent();
        $object = $event->getObject();
        $message = $this->messageManager->create(
            $content,
            $object,
            $users,
            $sender
        );
        $this->messageManager->send($message);
    }

    /**
     * @DI\Observe("claroline_contact_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\ContactAdditionalActionEvent $event
     */
    public function onContactActionMenuRender(ContactAdditionalActionEvent $event)
    {
        $user = $event->getUser();
        $url = $this->router->generate('claro_message_show', array('message' => 0))
            . '?userIds[]=' . $user->getId();

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('send_message', array(), 'platform'),
            array('uri' => $url)
        )->setExtra('icon', 'fa fa-envelope-o');

        return $menu;
    }
}

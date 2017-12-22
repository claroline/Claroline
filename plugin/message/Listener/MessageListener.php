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

use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use Claroline\CoreBundle\Menu\ContactAdditionalActionEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
    private $request;
    private $httpKernel;
    private $taskManager;

    /**
     * @DI\InjectParams({
     *     "messageManager"  = @DI\Inject("claroline.manager.message_manager"),
     *     "router"          = @DI\Inject("router"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "taskManager"     = @DI\Inject("claroline.manager.scheduled_task_manager"),
     * })
     */
    public function __construct(
        MessageManager $messageManager,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel,
        ScheduledTaskManager $taskManager
    ) {
        $this->messageManager = $messageManager;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
        $this->taskManager = $taskManager;
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
                ['%count%' => $countUnreadMessages],
                'platform'
            );
            $menu = $event->getMenu();
            $messageMenuLink = $menu->addChild(
                $this->translator->trans('messages', [], 'platform'),
                ['route' => 'claro_message_list_received']
            )->setExtra('icon', 'fa fa-'.$tool->getClass())
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
        $menu->addChild(
            $this->translator->trans('messages', [], 'platform'),
            ['route' => 'claro_message_show']
        )
        ->setExtra('icon', 'fa fa-envelope')
        ->setExtra('qstring', 'userIds[]='.$user->getId())
        ->setExtra('title', $this->translator->trans('message', [], 'platform'));
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
        $url = $this->router->generate('claro_message_show', ['message' => 0])
            .'?userIds[]='.$user->getId();

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('send_message', [], 'platform'),
            ['uri' => $url]
        )->setExtra('icon', 'fa fa-envelope-o');

        return $menu;
    }

    /**
     * @DI\Observe("open_tool_desktop_message")
     *
     * @param DisplayToolEvent $event
     */
    public function onOpenDesktopTool(DisplayToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineMessageBundle:Message:listReceived';
        $params['page'] = 1;
        $params['search'] = '';
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_scheduled_task_execute_message")
     *
     * @param GenericDataEvent $event
     */
    public function onExecuteMessageTask(GenericDataEvent $event)
    {
        /** @var ScheduledTask $task */
        $task = $event->getData();
        $data = $task->getData();
        $users = $task->getUsers();
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (count($users) > 0 && !empty($object) && !empty($content)) {
            $message = $this->messageManager->create($content, $object, $users);
            $this->messageManager->send($message);
            $this->taskManager->markAsExecuted($task);
        }
        $event->stopPropagation();
    }
}

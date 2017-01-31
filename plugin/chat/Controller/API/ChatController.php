<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Controller\API;

use Claroline\ChatBundle\Entity\ChatRoom;
use Claroline\ChatBundle\Entity\ChatRoomMessage;
use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("api_")
 */
class ChatController extends FOSRestController
{
    private $authorization;
    private $chatManager;
    private $platformConfigHandler;
    private $tokenStorage;
    private $request;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "chatManager"           = @DI\Inject("claroline.manager.chat_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage"          = @DI\Inject("security.token_storage"),
     *     "request"               = @DI\Inject("request")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ChatManager $chatManager,
        PlatformConfigurationHandler $platformConfigHandler,
        TokenStorageInterface $tokenStorage,
        Request $request
    ) {
        $this->authorization = $authorization;
        $this->chatManager = $chatManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
        $this->request = $request;
    }

    /**
     * @View(serializerGroups={"api_chat"})
     */
    public function getXmppOptionsAction()
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();

        if ($user === '.anon') {
            throw new AccessDeniedException();
        } else {
            $chatUser = $this->chatManager->getChatUserByUser($user);
            $xmppOptions = [];
            $xmppOptions['xmppHost'] = $this->platformConfigHandler->getParameter('chat_xmpp_host');
            $xmppOptions['boshPort'] = $this->platformConfigHandler->getParameter('chat_bosh_port');
            $xmppOptions['canChat'] = !is_null($chatUser);

            if ($xmppOptions['canChat']) {
                $xmppOptions['username'] = $user->getUsername();
                $xmppOptions['firstName'] = $user->getFirstName();
                $xmppOptions['lastName'] = $user->getLastName();
                $xmppOptions['chatUsername'] = $chatUser->getChatUsername();
                $xmppOptions['chatPassword'] = $chatUser->getChatPassword();
                $options = $chatUser->getOptions();
                $xmppOptions['chatColor'] = (is_array($options) && isset($options['color'])) ? $options['color'] : null;
            }

            return $xmppOptions;
        }
    }

    /**
     * @View(serializerGroups={"api_chat"})
     */
    public function getChatRoomUserAction(ChatRoom $chatRoom)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();

        if ($user === '.anon') {
            throw new AccessDeniedException();
        } else {
            $userInfos = [];
            $chatUser = $this->chatManager->getChatUserByUser($user);
            $userInfos['canChat'] = !is_null($chatUser);
            $userInfos['canEdit'] = $this->hasChatRoomRight($chatRoom, 'EDIT');

            if ($userInfos['canChat']) {
                $userInfos['username'] = $user->getUsername();
                $userInfos['firstName'] = $user->getFirstName();
                $userInfos['lastName'] = $user->getLastName();
                $userInfos['chatUsername'] = $chatUser->getChatUsername();
                $userInfos['chatPassword'] = $chatUser->getChatPassword();
                $options = $chatUser->getOptions();
                $userInfos['chatColor'] = (is_array($options) && isset($options['color'])) ? $options['color'] : null;
            }

            return $userInfos;
        }
    }

    /**
     * @View(serializerGroups={"api_chat"})
     */
    public function postChatRoomPresenceRegisterAction(ChatRoom $chatRoom, $username, $fullName, $status)
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $this->chatManager->saveChatRoomMessage($chatRoom, $username, $fullName, $status, ChatRoomMessage::PRESENCE);
    }

    /**
     * @View(serializerGroups={"api_chat"})
     */
    public function postChatRoomMessageRegisterAction(Request $request, ChatRoom $chatRoom, $username, $fullName)
    {
        $hasRight = $this->hasChatRoomRight($chatRoom, 'OPEN');

        if (!$hasRight) {
            return new JsonResponse('not_authorized', 403);
        }
        $message = $request->request->get('message', false);
        $this->chatManager->saveChatRoomMessage($chatRoom, $username, $fullName, $message, ChatRoomMessage::MESSAGE);
    }

    /**
     * @View(serializerGroups={"api_chat"})
     */
    public function postChatUsersInfosAction(Request $request, ChatRoom $chatRoom)
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $datas = [];
        $usernames = $request->request->get('usernames', false);
        $chatUsers = $this->chatManager->getChatUsersByUsernames($usernames);

        foreach ($chatUsers as $chatUser) {
            $chatUsername = $chatUser->getChatUsername();
            $user = $chatUser->getUser();
            $options = $chatUser->getOptions();
            $color = isset($options['color']) ? $options['color'] : null;
            $datas[$chatUsername] = [
                'username' => $chatUsername,
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'color' => $color,
            ];
        }

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_chat"})
     */
    public function getRegisteredMessagesAction(ChatRoom $chatRoom)
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $datas = [];
        $names = [];
        $usernames = [];
        $colors = [];
        $messages = $this->chatManager->getMessagesByChatRoom($chatRoom);

        foreach ($messages as $message) {
            $username = $message->getUsername();

            if (!isset($names[$username])) {
                $names[$username] = $username;
                $usernames[] = $username;
            }
        }
        $chatUsers = $this->chatManager->getChatUsersByUsernames($usernames);

        foreach ($chatUsers as $chatUser) {
            $chatUsername = $chatUser->getChatUsername();
            $options = $chatUser->getOptions();
            $colors[$chatUsername] = isset($options['color']) ? $options['color'] : null;
        }

        foreach ($messages  as $message) {
            $username = $message->getUsername();
            $color = isset($colors[$username]) ? $colors[$username] : null;
            $datas[] = [
                'username' => $username,
                'userFullName' => $message->getUserFullName(),
                'creationDate' => $message->getCreationDate(),
                'type' => $message->getTypeText(),
                'content' => $message->getContent(),
                'color' => $color,
            ];
        }

        return $datas;
    }

    /**
     * @View(serializerGroups={"api_chat"})
     * @Put("room/{chatRoom}", name="put_chat_room", options={ "method_prefix" = false })
     */
    public function putChatRoomAction(ChatRoom $chatRoom)
    {
        $this->checkChatRoomRight($chatRoom, 'EDIT');
        $data = $this->request->request->get('chat_room');

        return $this->chatManager->editChatRoom($chatRoom, $data['room_type'], $data['room_status']);
    }

    private function checkChatRoomRight(ChatRoom $chatRoom, $right)
    {
        $collection = new ResourceCollection([$chatRoom->getResourceNode()]);

        if (!$this->authorization->isGranted($right, $collection)) {
            throw new AccessDeniedException();
        }
    }

    private function hasChatRoomRight(ChatRoom $chatRoom, $right)
    {
        $collection = new ResourceCollection([$chatRoom->getResourceNode()]);

        return $this->authorization->isGranted($right, $collection);
    }
}

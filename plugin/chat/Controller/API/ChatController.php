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
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "chatManager"           = @DI\Inject("claroline.manager.chat_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage"          = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ChatManager $chatManager,
        PlatformConfigurationHandler $platformConfigHandler,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->authorization = $authorization;
        $this->chatManager = $chatManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @View(serializerGroups={"api_chat"})
     * @ApiDoc(
     *     description="Returns Xmpp options",
     *     views = {"chat"}
     * )
     */
    public function getXmppOptionsAction()
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();

        if ($user === '.anon') {

            throw new AccessDeniedException();
        } else {
            $chatUser = $this->chatManager->getChatUserByUser($user);
            $xmppOptions = array();
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
     * @ApiDoc(
     *     description="Returns user informations in Chat room",
     *     views = {"chat"}
     * )
     */
    public function getChatRoomUserAction(ChatRoom $chatRoom)
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();

        if ($user === '.anon') {

            throw new AccessDeniedException();
        } else {
            $userInfos = array();
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
     * @ApiDoc(
     *     description="Change status of Chat room",
     *     views = {"chat"}
     * )
     */
    public function putRoomStatusAction(ChatRoom $chatRoom, $roomStatus)
    {
        $this->checkChatRoomRight($chatRoom, 'EDIT');
        $chatRoom->setRoomStatus($roomStatus);
        $this->chatManager->persistChatRoom($chatRoom);

        return array(
            'id' => $chatRoom->getId(),
            'roomName' => $chatRoom->getRoomName(),
            'roomStatus' => $chatRoom->getRoomStatus(),
            'roomStatusText' => $chatRoom->getRoomStatusText(),
            'roomType' => $chatRoom->getRoomType(),
            'roomTypeText' => $chatRoom->getRoomTypeText()
        );
    }

    /**
     * @View(serializerGroups={"api_chat"})
     * @ApiDoc(
     *     description="Register Chat room user presence status",
     *     views = {"chat"}
     * )
     */
    public function postChatRoomPresenceRegisterAction(ChatRoom $chatRoom, $username, $fullName, $status)
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $this->chatManager->saveChatRoomMessage($chatRoom, $username, $fullName, $status, ChatRoomMessage::PRESENCE);
    }

    /**
     * @View(serializerGroups={"api_chat"})
     * @ApiDoc(
     *     description="Register Chat room user message",
     *     views = {"chat"}
     * )
     */
    public function postChatRoomMessageRegisterAction(Request $request, ChatRoom $chatRoom, $username, $fullName)
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $message = $request->request->get('message', false);
        $this->chatManager->saveChatRoomMessage($chatRoom, $username, $fullName, $message, ChatRoomMessage::MESSAGE);
    }

    private function checkChatRoomRight(ChatRoom $chatRoom, $right)
    {
        $collection = new ResourceCollection(array($chatRoom->getResourceNode()));

        if (!$this->authorization->isGranted($right, $collection)) {

            throw new AccessDeniedException();
        }
    }

    private function hasChatRoomRight(ChatRoom $chatRoom, $right)
    {
        $collection = new ResourceCollection(array($chatRoom->getResourceNode()));

        return $this->authorization->isGranted($right, $collection);
    }
}
<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Controller;

use Claroline\ChatBundle\Entity\ChatRoom;
use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChatController extends Controller
{
    private $authorization;
    private $chatManager;
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "chatManager"           = @DI\Inject("claroline.manager.chat_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ChatManager $chatManager,
        PlatformConfigurationHandler $platformConfigHandler
    )
    {
        $this->authorization = $authorization;
        $this->chatManager = $chatManager;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/chat",
     *     name="claro_chat_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function userChatAction(User $authenticatedUser, User $user)
    {
        $xmppHost = $this->platformConfigHandler->getParameter('chat_xmpp_host');
        $xmppPort = $this->platformConfigHandler->getParameter('chat_xmpp_port');
        $chatUser = $this->chatManager->getChatUserByUser($authenticatedUser);

        return array(
            'chatUser' => $chatUser,
            'user' => $user,
            'xmppHost' => $xmppHost,
            'xmppPort' => $xmppPort
        );
    }

    /**
     * @EXT\Route(
     *     "/chat/room/{chatRoom}/open",
     *     name="claro_chat_room_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function chatRoomOpenAction(User $authenticatedUser, ChatRoom $chatRoom)
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $xmppHost = $this->platformConfigHandler->getParameter('chat_xmpp_host');
        $xmppMucHost = $this->platformConfigHandler->getParameter('chat_xmpp_muc_host');
        $xmppPort = $this->platformConfigHandler->getParameter('chat_xmpp_port');
        $chatUser = $this->chatManager->getChatUserByUser($authenticatedUser);
        $canChat = !is_null($chatUser);
        $canEdit = $this->hasChatRoomRight($chatRoom, 'EDIT');

        return array(
            'workspace' => $chatRoom->getResourceNode()->getWorkspace(),
            'canChat' => $canChat,
            'canEdit' => $canEdit,
            'chatUser' => $chatUser,
            'chatRoom' => $chatRoom,
            'xmppHost' => $xmppHost,
            'xmppMucHost' => $xmppMucHost,
            'xmppPort' => $xmppPort
        );
    }

    /**
     * @EXT\Route(
     *     "/chat/room/{chatRoom}/user/{username}/message/{message}",
     *     name="claro_chat_room_message_register",
     *     defaults={"message"=""},
     *     requirements={"message"=".+"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function chatRoomMessageRegisterAction(ChatRoom $chatRoom, $username, $message = '')
    {
        $this->checkChatRoomRight($chatRoom, 'OPEN');
        $this->chatManager->saveChatRoomMessage($chatRoom, $username, $message);

        return new JsonResponse('success', 200);
    }

    private function checkChatRoomRight(ChatRoom $chatRoom, $right)
    {
        $collection = new ResourceCollection(array($chatRoom->getResourceNode()));

        if (!$this->authorization->isGranted($right, $collection)) {

            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function hasChatRoomRight(ChatRoom $chatRoom, $right)
    {
        $collection = new ResourceCollection(array($chatRoom->getResourceNode()));

        return $this->authorization->isGranted($right, $collection);
    }
}

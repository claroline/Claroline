<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\ChatBundle\Entity\ChatRoom;
use Claroline\ChatBundle\Entity\ChatRoomMessage;
use Claroline\ChatBundle\Entity\ChatUser;
use Claroline\ChatBundle\Library\Xmpp\AnonymousImplementation;
use Claroline\ChatBundle\Library\Xmpp\AuthenticatedImplementation;
use Claroline\ChatBundle\Library\Xmpp\Protocol\Register;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Fabiang\Xmpp\Client;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Protocol\Message;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("claroline.manager.chat_manager")
 */
class ChatManager
{
    use LoggableTrait;

    private $om;
    private $pagerFactory;
    private $configHandler;
    private $chatRoomMessageRepo;
    private $chatUserRepo;

    /**
     * @DI\InjectParams({
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
     *     "utils"                 = @DI\Inject("claroline.utilities.misc"),
     *     "configHandler"         = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        ClaroUtilities $utils,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->utils = $utils;
        $this->chatRoomMessageRepo = $om->getRepository('ClarolineChatBundle:ChatRoomMessage');
        $this->chatUserRepo = $om->getRepository('ClarolineChatBundle:ChatUser');
        $this->configHandler = $configHandler;
    }

    public function importExistingUsers()
    {
        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $client = $this->getClient();

        foreach ($users as $user) {
            $chatUser = $this->chatUserRepo->findOneByUser($user);

            if (!$chatUser) {
                $this->importUser($user, $client);
            } else {
                $this->log("User {$user->getUsername()} already exists");
            }
        }

        $client->disconnect();
    }

    public function importUser(User $user, Client $client = null)
    {
        $hasCreatedClient = false;

        if (!$client) {
            $client = $this->getClient();
            $hasCreatedClient = true;
        }

        $this->log("Adding chat user for {$user->getUsername()}");
        $this->createChatUser($user, $user->getUsername(), $user->getGuid());
        $register = new Register();
        $register->setUser($user);
        $client->send($register);

        if ($hasCreatedClient) {
            $client->disconnect();
        }
    }

    public function getClient($username = null, $password = null)
    {
        $host = $this->configHandler->getParameter('chat_xmpp_host');
        $address = "tcp://{$host}:5222";

        $options = new Options($address);
        if ($this->logger) {
            $options->setLogger($this->logger);
        }

        if ($username && $password) {
            $options->setUsername($adminUsername)->setPassword($adminPassword);
            $options->setImplementation(new AuthenticatedImplementation());
        } else {
            $options->setImplementation(new AnonymousImplementation());
            $options->setAuthenticationClasses([]);
        }

        $client = new Client($options);
        $client->connect();

        return $client;
    }

    public function createChatUser(User $user, $username, $password)
    {
        $chatUser = new ChatUser();
        $chatUser->setUser($user);
        $chatUser->setChatUsername($username);
        $chatUser->setChatPassword($password);
        $this->om->persist($chatUser);
        $this->om->flush();
    }

    public function persistChatUser(ChatUser $chatUser)
    {
        $this->om->persist($chatUser);
        $this->om->flush();
    }

    public function deleteChatUser(ChatUser $chatUser)
    {
        $this->om->remove($chatUser);
    }

    public function initChatRoom(ChatRoom $chatRoom)
    {
        $roomName = $chatRoom->getRoomName();

        if (empty($roomName)) {
            $guid = $chatRoom->getResourceNode()->getGuid();
            $chatRoom->setRoomName(strtolower($guid));
            $this->om->persist($chatRoom);
            $this->om->flush();
        }
    }

    public function persistChatRoom(ChatRoom $chatRoom)
    {
        $this->om->persist($chatRoom);
        $this->om->flush();
    }

    public function saveChatRoomMessage(
        ChatRoom $chatRoom,
        $username,
        $fullName,
        $message,
        $type = ChatRoomMessage::MESSAGE
    ) {
        $roomMessage = new ChatRoomMessage();
        $roomMessage->setCreationDate(new \DateTime());
        $roomMessage->setChatRoom($chatRoom);
        $roomMessage->setUsername($username);
        $roomMessage->setUserFullName($fullName);
        $roomMessage->setContent($message);
        $roomMessage->setType($type);
        $this->om->persist($roomMessage);
        $this->om->flush();
    }

    public function copyChatRoom(ChatRoom $chatRoom)
    {
        $newRoom = new ChatRoom();
        $newRoom->setName($chatRoom->getName());
        $newRoom->setRoomName($chatRoom->getRoomName());
        $newRoom->setRoomStatus($chatRoom->getRoomStatus());
        $this->om->persist($newRoom);

        $messages = $chatRoom->getMessages();

        foreach ($messages as $message) {
            $newMessage = new ChatRoomMessage();
            $newMessage->setChatRoom($newRoom);
            $newMessage->setContent($message->getContent());
            $newMessage->setUsername($message->getUsername());
            $newMessage->setCreationDate($message->getCreationDate());
            $this->om->persist($newMessage);
        }

        return $newRoom;
    }

    /****************************************
     * Access to ChatUserRepository methods *
     ****************************************/

    public function getChatUserByUser(User $user)
    {
        return $this->chatUserRepo->findChatUserByUser($user);
    }

    public function getChatUsers(
        $search = '',
        $orderedBy = 'username',
        $order = 'ASC',
        $withPager = true,
        $page = 1,
        $max = 50
    ) {
        $chatUsers = $this->chatUserRepo->findChatUsers($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($chatUsers, $page, $max) :
            $chatUsers;
    }

    public function getAllUsersFromChatUsers()
    {
        $users = [];
        $chatUsers = $this->chatUserRepo->findAll();

        foreach ($chatUsers as $chatUser) {
            $users[] = $chatUser->getUser();
        }

        return $users;
    }

    public function getChatUsersByUsernames(array $usernames)
    {
        return count($usernames) > 0 ? $this->chatUserRepo->findChatUsersByUsernames($usernames) : [];
    }

    /***********************************************
     * Access to ChatRoomMessageRepository methods *
     ***********************************************/

    public function getMessagesByChatRoom(ChatRoom $chatRoom)
    {
        return $this->chatRoomMessageRepo->findMessagesByChatRoom($chatRoom);
    }

    public function getChatRoomParticipantsName(ChatRoom $chatRoom)
    {
        return $this->chatRoomMessageRepo->findChatRoomParticipantsName($chatRoom);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}

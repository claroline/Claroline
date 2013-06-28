<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;

class MessageRepositoryTest extends RepositoryTestCase
{
    private static $writer;
    private static $repo;
//    private static $userMsgRepo;
    private static $users;
    private static $messages;
    private static $userMessages;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$writer = self::$client->getContainer()->get('claroline.database.writer');
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Message');
        self::createUser('sender');
        self::createUser('receiver');

        self::createMessage(
            'message_1',
            self::$users['sender'],
            array(self::$users['receiver']),
            'message_1 content',
            'message_1 object'
        );
        self::createMessage(
            'message_2',
            self::$users['sender'],
            array(self::$users['receiver']),
            'message_2 content',
            'message_2 object',
            self::$messages['message_1']
        );



//    sleep(1); // dates involved...
//
//    self::$messages['message_2'] = self::$writer->create(
//        self::$users['sender_2'],
//        'some receiver string...',
//        array(self::$users['receiver_1'], self::$users['receiver_2']),
//        'message_2 content',
//        'message_2 object',
//        self::$messages['message_1']
//    );
    }

    public function testFindAll()
    {
        $this->assertEquals(2, count(self::$repo->findAll()));
    }

    public function testFindAncestors()
    {
        $messages = self::$repo->findAncestors(self::$messages['message_2']);
        $this->assertEquals(2, count($messages));
        $this->assertEquals(self::$messages['message_1'], $messages[0]);
        $this->assertEquals(self::$messages['message_2'], $messages[1]);
    }

    public function testCountUnread()
    {
        $this->assertEquals(2, self::$repo->countUnread(self::$users['receiver']));
    }


    private static function createUser($name)
    {
        $user = new User();
        $user->setFirstName($name . 'FirstName');
        $user->setLastName($name . 'LastName');
        $user->setUsername($name . 'Username');
        $user->setPlainPassword($name . 'Password');
        self::$writer->create($user);
        self::$users[$name] = $user;
    }

    private static function createMessage($alias, User $sender, array $receivers, $object, $content, Message $parent = null)
    {
        $message = new Message();
        $message->setSender($sender);
        $message->setObject($object);
        $message->setContent($content);
        $message->setTo('some receiver string');
        $message->setReceiverString('some receiver string');

        if ($parent) {
            $message->setParent($parent);
        }

        self::$writer->suspendFlush();
        self::$writer->create($message);
        self::$messages[$alias] = $message;

        $userMessage = new UserMessage();
        $userMessage->setIsSent(true);
        $userMessage->setUser($sender);
        $userMessage->setMessage($message);
        self::$writer->create($userMessage);
        self::$userMessages[$alias . '/' . $sender->getUsername()] = $userMessage;

        foreach ($receivers as $receiver) {
            $userMessage = new UserMessage();
            $userMessage->setUser($receiver);
            $userMessage->setMessage($message);
            self::$writer->create($userMessage);
            self::$userMessages[$alias . '/' . $receiver->getUsername()] = $userMessage;
        }

        self::$writer->forceFlush();
    }


//    /** @var \Claroline\CoreBundle\Repository\MessageRepository */
//    private static $repo;
//
//    public static function setUpBeforeClass()
//    {
//        parent::setUpBeforeClass();
//        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Message');
//        self::loadPlatformRoleData();
//        self::loadUserData(array('sender' => 'user', 'receiver' => 'user', 'removed' => 'user'));
//        self::loadMessagesData(
//            array(
//                array(
//                    'from' => 'sender',
//                    'to' => 'receiver',
//                    'object' => 'ancestor',
//                ),
//                array(
//                    'from' => 'sender',
//                    'to' => 'receiver',
//                    'object' => 'alone'
//                ),
//                array(
//                    'from' => 'receiver',
//                    'to' => 'sender',
//                    'object' => 'child',
//                    'parent' => 'ancestor'
//                ),
//                array(
//                    'from' => 'receiver',
//                    'to' => 'sender',
//                    'object' => 'removed1'
//                ),
//                array(
//                    'from' => 'sender',
//                    'to' => 'receiver',
//                    'object' => 'removed2'
//                )
//            )
//        );
//
//        self::$client->getContainer()->get('claroline.manager.message_manager')->markAsRemoved(
//            self::getUser('receiver'),
//            array(self::getMessage('removed1'))
//        );
//
//        self::$client->getContainer()->get('claroline.manager.message_manager')->markAsRemoved(
//            self::getUser('sender'),
//            array(self::getMessage('removed1'))
//        );
//    }
//
//    public function testFindAncestors()
//    {
//        $messages = self::$repo->findAncestors(self::getMessage('child'));
//        $this->assertEquals(2, count($messages));
//        $this->assertEquals('ancestor', $messages[0]->getObject());
//        $this->assertEquals('child', $messages[1]->getObject());
//    }
//
//    public function testCountUnread()
//    {
//        $this->assertEquals(3, self::$repo->countUnread(self::getUser('receiver')));
//    }
//
//    public function testFindReceivedByUser()
//    {
//        $messages = self::$repo->findReceivedByUser(self::getUser('receiver'));
//        $this->assertEquals(3, count($messages));
//        $this->assertEquals('ancestor', $messages[0]->getMessage()->getObject());
//    }
//
//    public function testFindSentByUser()
//    {
//        $messages = self::$repo->findSentByUser(self::getUser('receiver'));
//        $this->assertEquals(1, count($messages));
//        $this->assertEquals('child', $messages[0]->getMessage()->getObject());
//    }
//
//    public function testFindReceivedByUserAndObjectAndUsername()
//    {
//        $messages = self::$repo->findReceivedByUserAndObjectAndUsername(
//            self::getUser('receiver'),
//            'anc'
//        );
//
//        $this->assertEquals(1, count($messages));
//        $this->assertEquals('ancestor', $messages[0]->getMessage()->getObject());
//    }
//
//    public function testFindSentByUserAndObjectAndUsername()
//    {
//        $messages = self::$repo->findSentByUserAndObjectAndUsername(
//            self::getUser('sender'),
//            'anc'
//        );
//
//        $this->assertEquals(1, count($messages));
//        $this->assertEquals('ancestor', $messages[0]->getMessage()->getObject());
//    }
//
//    public function testFindUserMessages()
//    {
//        $um = self::$repo->findUserMessages(
//            self::getUser('sender'),
//            array(self::getMessage('ancestor'), self::getMessage('alone'))
//        );
//        $this->assertEquals(2, count($um));
//        $this->assertEquals('ancestor', $um[0]->getMessage()->getObject());
//    }
//
//    public function testFindRemovedByUser()
//    {
//        $um = self::$repo->findRemovedByUser(self::getUser('sender'));
//        $this->assertEquals(1, count($um));
//        $um = self::$repo->findRemovedByUser(self::getUser('receiver'));
//        $this->assertEquals(1, count($um));
//    }
//
//    public function testFindRemovedByUserAndObjectAndUsername()
//    {
//        $um = self::$repo->findRemovedByUser(self::getUser('sender'), 'ReM');
//        $this->assertEquals(1, count($um));
//        $um = self::$repo->findRemovedByUser(self::getUser('receiver', 'ReM'));
//        $this->assertEquals(1, count($um));
//    }
//
//    public function testFindByIds()
//    {
//        $messages = self::$repo->testFindByIds(array(self::getMessage('child'), self::getMessage('ancestor')));
//        $this->assertEquals(2, count($messages));
//    }
}



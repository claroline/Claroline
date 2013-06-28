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
}
<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;

abstract class AltRepositoryTestCase extends WebTestCase
{
    protected static $writer;

    protected static $users;
    protected static $messages;
    protected static $userMessages;

    private static $em;
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        self::$writer = self::$client->getContainer()->get('claroline.database.writer');
        self::$client->beginTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::$client->shutdown();
    }

    protected static function getRepository($entityClass)
    {
        return self::$em->getRepository($entityClass);
    }

    protected static function createUser($name)
    {
        $user = new User();
        $user->setFirstName($name . 'FirstName');
        $user->setLastName($name . 'LastName');
        $user->setUsername($name . 'Username');
        $user->setPlainPassword($name . 'Password');
        self::$writer->create($user);
        self::$users[$name] = $user;
    }

    protected static function createMessage(
        $alias,
        User $sender,
        array $receivers,
        $object,
        $content,
        Message $parent = null,
        $removed = false
    )
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

        if ($removed) {
            $userMessage->markAsRemoved($removed);
        }

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
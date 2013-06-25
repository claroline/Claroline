<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class MessageRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\MessageRepository */
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Message');
        self::loadPlatformRoleData();
        self::loadUserData(array('sender' => 'user', 'receiver' => 'user', 'removed' => 'user'));
        self::loadMessagesData(
            array(
                array(
                    'from' => 'sender',
                    'to' => 'receiver',
                    'object' => 'ancestor',
                ),
                array(
                    'from' => 'sender',
                    'to' => 'receiver',
                    'object' => 'alone'
                ),
                array(
                    'from' => 'receiver',
                    'to' => 'sender',
                    'object' => 'child',
                    'parent' => 'ancestor'
                ),
                array(
                    'from' => 'receiver',
                    'to' => 'sender',
                    'object' => 'removed1'
                ),
                array(
                    'from' => 'sender',
                    'to' => 'receiver',
                    'object' => 'removed2'
                )
            )
        );

        self::$client->getContainer()->get('claroline.manager.message_manager')->markAsRemoved(
            self::getUser('receiver'),
            array(self::getMessage('removed1'))
        );

        self::$client->getContainer()->get('claroline.manager.message_manager')->markAsRemoved(
            self::getUser('sender'),
            array(self::getMessage('removed1'))
        );
    }

    public function testFindAncestors()
    {
        $messages = self::$repo->findAncestors(self::getMessage('child'));
        $this->assertEquals(2, count($messages));
        $this->assertEquals('ancestor', $messages[0]->getObject());
        $this->assertEquals('child', $messages[1]->getObject());
    }

    public function testCountUnread()
    {
        $this->assertEquals(3, self::$repo->countUnread(self::getUser('receiver')));
    }

    public function testFindReceivedByUser()
    {
        $messages = self::$repo->findReceivedByUser(self::getUser('receiver'));
        $this->assertEquals(3, count($messages));
        $this->assertEquals('ancestor', $messages[0]->getMessage()->getObject());
    }

    public function testFindSentByUser()
    {
        $messages = self::$repo->findSentByUser(self::getUser('receiver'));
        $this->assertEquals(1, count($messages));
        $this->assertEquals('child', $messages[0]->getMessage()->getObject());
    }

    public function testFindReceivedByUserAndObjectAndUsername()
    {
        $messages = self::$repo->findReceivedByUserAndObjectAndUsername(
            self::getUser('receiver'),
            'anc'
        );

        $this->assertEquals(1, count($messages));
        $this->assertEquals('ancestor', $messages[0]->getMessage()->getObject());
    }

    public function testFindSentByUserAndObjectAndUsername()
    {
        $messages = self::$repo->findSentByUserAndObjectAndUsername(
            self::getUser('sender'),
            'anc'
        );

        $this->assertEquals(1, count($messages));
        $this->assertEquals('ancestor', $messages[0]->getMessage()->getObject());
    }

    public function testFindUserMessages()
    {
        $um = self::$repo->findUserMessages(
            self::getUser('sender'),
            array(self::getMessage('ancestor'), self::getMessage('alone'))
        );
        $this->assertEquals(2, count($um));
        $this->assertEquals('ancestor', $um[0]->getMessage()->getObject());
    }

    public function testFindRemovedByUser()
    {
        $um = self::$repo->findRemovedByUser(self::getUser('sender'));
        $this->assertEquals(1, count($um));
        $um = self::$repo->findRemovedByUser(self::getUser('receiver'));
        $this->assertEquals(1, count($um));
    }

    public function testFindRemovedByUserAndObjectAndUsername()
    {
        $um = self::$repo->findRemovedByUser(self::getUser('sender'), 'ReM');
        $this->assertEquals(1, count($um));
        $um = self::$repo->findRemovedByUser(self::getUser('receiver', 'ReM'));
        $this->assertEquals(1, count($um));
    }

    public function testFindByIds()
    {
        $messages = self::$repo->testFindByIds(array(self::getMessage('child'), self::getMessage('ancestor')));
        $this->assertEquals(2, count($messages));
    }
}



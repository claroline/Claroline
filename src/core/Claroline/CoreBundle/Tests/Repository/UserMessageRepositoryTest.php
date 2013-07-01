<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\AltRepositoryTestCase;

class UserMessageRepositoryTest extends AltRepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$repo = self::getRepository('ClarolineCoreBundle:UserMessage');

        self::createUser('sender');
        self::createUser('receiver_1');
        self::createUser('receiver_2');

        self::createMessage(
            'message_1',
            self::$users['sender'],
            array(self::$users['receiver_1']),
            'message_1 object',
            'message_1 content'
        );
        sleep(1); // dates involved
        self::createMessage(
            'message_2',
            self::$users['sender'],
            array(self::$users['receiver_1'], self::$users['receiver_2']),
            'message_2 object',
            'message_2 content',
            self::$messages['message_1']
        );
        sleep(1); // dates involved
        self::createMessage(
            'message_3',
            self::$users['receiver_2'],
            array(self::$users['receiver_1']),
            'message_3 object',
            'message_3 content',
            null,
            true
        );
    }

    /**
     * @group message
     * @group database
     */
    public function testFindSent()
    {
        $userMessages = self::$repo->findSent(self::$users['sender']);
        $this->assertEquals(2, count($userMessages));
        $this->assertEquals(self::$userMessages['message_2/senderUsername'], $userMessages[0]);
        $this->assertEquals(self::$userMessages['message_1/senderUsername'], $userMessages[1]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindReceivedr()
    {
        $userMessages = self::$repo->findReceived(self::$users['receiver_1']);
        $this->assertEquals(3, count($userMessages));
        $this->assertEquals(self::$userMessages['message_3/receiver_1Username'], $userMessages[0]);
        $this->assertEquals(self::$userMessages['message_2/receiver_1Username'], $userMessages[1]);
        $this->assertEquals(self::$userMessages['message_1/receiver_1Username'], $userMessages[2]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindRemoved()
    {
        $userMessages = self::$repo->findRemoved(self::$users['receiver_2']);
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::$userMessages['message_3/receiver_2Username'], $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindReceivedByObject()
    {
        $userMessages = self::$repo->findReceivedByObjectOrSender(
            self::$users['receiver_2'],
            'ssage_2 OBJ'
        );
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::$userMessages['message_2/receiver_2Username'], $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindReceivedBySender()
    {
        $userMessages = self::$repo->findReceivedByObjectOrSender(
            self::$users['receiver_1'],
            'endeR'
        );
        $this->assertEquals(2, count($userMessages));
        $this->assertEquals(self::$userMessages['message_2/receiver_1Username'], $userMessages[0]);
        $this->assertEquals(self::$userMessages['message_1/receiver_1Username'], $userMessages[1]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindSentByObject()
    {
        $userMessages = self::$repo->findSentByObject(self::$users['sender'], 'ssage_1 oB');
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::$userMessages['message_1/senderUsername'], $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindRemovedByObject()
    {
        $userMessages = self::$repo->findRemovedByObjectOrSender(
            self::$users['receiver_2'],
            'sag'
        );
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::$userMessages['message_3/receiver_2Username'], $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindRemovedBySender()
    {
        $userMessages = self::$repo->findRemovedByObjectOrSender(
            self::$users['receiver_2'],
            'eiVer_2'
        );
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::$userMessages['message_3/receiver_2Username'], $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindByMessages()
    {
        $userMessages = self::$repo->findByMessages(
            self::$users['receiver_1'],
            array(self::$messages['message_1'], self::$messages['message_2'])
        );
        $this->assertEquals(2, count($userMessages));
        $this->assertEquals(self::$userMessages['message_2/receiver_1Username'], $userMessages[0]);
        $this->assertEquals(self::$userMessages['message_1/receiver_1Username'], $userMessages[1]);
    }
}
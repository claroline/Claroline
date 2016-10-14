<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class UserMessageRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$repo = self::getRepository('ClarolineMessageBundle:UserMessage');

        self::createUser('sender');
        self::createUser('receiver_1');
        self::createUser('receiver_2');

        self::createMessage(
            'message_1',
            self::get('sender'),
            [self::get('receiver_1')],
            'message_1 object',
            'message_1 content'
        );
        self::sleep(1); // dates involved
        self::createMessage(
            'message_2',
            self::get('sender'),
            [self::get('receiver_1'), self::get('receiver_2')],
            'message_2 object',
            'message_2 content',
            self::get('message_1')
        );
        self::sleep(1); // dates involved
        self::createMessage(
            'message_3',
            self::get('receiver_2'),
            [self::get('receiver_1')],
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
        $userMessages = self::$repo->findSent(self::get('sender'));
        $this->assertEquals(2, count($userMessages));
        $this->assertEquals(self::get('message_2/sender'), $userMessages[0]);
        $this->assertEquals(self::get('message_1/sender'), $userMessages[1]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindReceivedr()
    {
        $userMessages = self::$repo->findReceived(self::get('receiver_1'));
        $this->assertEquals(3, count($userMessages));
        $this->assertEquals(self::get('message_3/receiver_1'), $userMessages[0]);
        $this->assertEquals(self::get('message_2/receiver_1'), $userMessages[1]);
        $this->assertEquals(self::get('message_1/receiver_1'), $userMessages[2]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindRemoved()
    {
        $userMessages = self::$repo->findRemoved(self::get('receiver_2'));
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::get('message_3/receiver_2'), $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindReceivedByObject()
    {
        $userMessages = self::$repo->findReceivedByObjectOrSender(self::get('receiver_2'), 'ssage_2 OBJ');
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::get('message_2/receiver_2'), $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindReceivedBySender()
    {
        $userMessages = self::$repo->findReceivedByObjectOrSender(self::get('receiver_1'), 'endeR');
        $this->assertEquals(2, count($userMessages));
        $this->assertEquals(self::get('message_2/receiver_1'), $userMessages[0]);
        $this->assertEquals(self::get('message_1/receiver_1'), $userMessages[1]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindSentByObject()
    {
        $userMessages = self::$repo->findSentByObject(self::get('sender'), 'ssage_1 oB');
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::get('message_1/sender'), $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindRemovedByObject()
    {
        $userMessages = self::$repo->findRemovedByObjectOrSender(self::get('receiver_2'), 'sag');
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::get('message_3/receiver_2'), $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindRemovedBySender()
    {
        $userMessages = self::$repo->findRemovedByObjectOrSender(self::get('receiver_2'), 'eiVer_2');
        $this->assertEquals(1, count($userMessages));
        $this->assertEquals(self::get('message_3/receiver_2'), $userMessages[0]);
    }

    /**
     * @group message
     * @group database
     */
    public function testFindByMessages()
    {
        $userMessages = self::$repo->findByMessages(
            self::get('receiver_1'),
            [self::get('message_1'), self::get('message_2')]
        );
        $this->assertEquals(2, count($userMessages));
        $this->assertEquals(self::get('message_2/receiver_1'), $userMessages[0]);
        $this->assertEquals(self::get('message_1/receiver_1'), $userMessages[1]);
    }
}

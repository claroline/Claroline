<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\AltRepositoryTestCase;

class MessageRepositoryTest extends AltRepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$repo = self::getRepository('ClarolineCoreBundle:Message');

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

    /**
     * @group message
     * @group database
     */
    public function testFindAll()
    {
        $this->assertEquals(2, count(self::$repo->findAll()));
    }

    /**
     * @group message
     * @group database
     */
    public function testFindAncestors()
    {
        $messages = self::$repo->findAncestors(self::$messages['message_2']);
        $this->assertEquals(2, count($messages));
        $this->assertEquals(self::$messages['message_1'], $messages[0]);
        $this->assertEquals(self::$messages['message_2'], $messages[1]);
    }

    /**
     * @group message
     * @group database
     */
    public function testCountUnread()
    {
        $this->assertEquals(2, self::$repo->countUnread(self::$users['receiver']));
    }
}
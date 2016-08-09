<?php

namespace Claroline\ChatBundle\Tests\API;

use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ChatControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testGetXmppOptionsAction()
    {
    }

    public function testGetChatRoomUserAction()
    {
    }

    public function testPostChatRoomPresenceRegisterAction()
    {
    }

    public function testPostChatRoomMessageRegisterAction()
    {
    }

    public function testPostChatUsersInfosAction()
    {
    }

    public function testGetRegisteredMessagesAction()
    {
    }

    public function testPutChatRoomAction()
    {
        //à tester
    }
}

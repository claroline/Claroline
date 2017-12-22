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
        $this->chatPersister = $this->client->getContainer()->get('claroline.chat_bundle.testing.persister');
    }

    public function testGetXmppOptionsAction()
    {
        $this->markTestSkipped();
    }

    public function testGetChatRoomUserAction()
    {
        $this->markTestSkipped();
    }

    public function testPostChatRoomPresenceRegisterAction()
    {
        $this->markTestSkipped();
    }

    public function testPostChatRoomMessageRegisterAction()
    {
        $this->markTestSkipped();
    }

    public function testPostChatUsersInfosAction()
    {
        $this->markTestSkipped();
    }

    public function testGetRegisteredMessagesAction()
    {
        $this->markTestSkipped();
    }

    public function testPutChatRoomAction()
    {
        $this->markTestSkipped();

        $manager = $this->persister->user('manager');
        $user = $this->persister->user('user');
        $chatRoom = $this->chatPersister->chatRoom('chatRoom', 1, 1, $manager);

        //first step, we change it
        $this->login($manager);
        $fields = ['room_type' => 2, 'room_status' => 2];
        $form = ['chat_room' => $fields];
        $this->client->request('PUT', "/clarolinechatbundle/api/room/{$chatRoom->getId()}.json", $form);
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);

        $this->assertEquals($data['room_type'], 2);
        $this->assertEquals($data['room_status'], 2);

        //this should be protected
        $this->login($user);
        $this->client->request('PUT', "/clarolinechatbundle/api/room/{$chatRoom->getId()}", $form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 403);
    }
}

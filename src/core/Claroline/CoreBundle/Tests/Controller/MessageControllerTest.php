<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class MessageControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
    }

    public function testMessageForm()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', "/message/form");
        $form = $crawler->filter('#message_form');
        $this->assertEquals(count($form), 1);
    }

    public function testMessageGroupForm()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->loadGroupData(array('group_a' => array('user')));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'GET',
            "/message/form/group/{$this->getGroup('group_a')->getId()}"
        );
        $form = $crawler->filter('#message_form');
        $this->assertEquals(count($form), 1);
        $parameters = $this->client->getRequest()->query->all();
        $this->assertEquals($parameters['ids'][0], $this->getUser('user')->getId());
    }

    public function testSendMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('content' => 'content', 'object' => 'object', 'to' => 'user'))
        );

        $crawler = $this->client->request('GET', '/message/sent/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testSendMessageReturnsFormOnError()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('object' => 'object', 'to' => 'user'))
        );
        $form = $crawler->filter('#message_form');
        $this->assertEquals(count($form), 1);
    }

    public function testAnswerMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('content' => 'content', 'object' => 'object', 'to' => 'user'))
        );
        $this->logUser($this->getUser('user'));
        $msgId = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository("ClarolineCoreBundle:Message")
            ->findOneBy(array('object' => 'object'))
            ->getId();
        $this->client->request(
            'POST',
            "/message/send/{$msgId}",
            array('message_form' => array('content' => 'content', 'object' => 'answer', 'to' => 'admin'))
        );
        $this->logUser($this->getUser('admin'));
        $msgId = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository("ClarolineCoreBundle:Message")
            ->findOneBy(array('parent' => $msgId))->getId();
        $crawler = $this->client->request('GET', "message/show/{$msgId}");
        $this->assertEquals(2, count($crawler->filter('.message-show')));
    }

    public function testAlertOnReceivedMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'user', 'from' => 'admin', 'object' => 'foo')));
        $crawler = $this->logUser($this->getUser('user'));
        $this->assertEquals(1, count($crawler->filter('.badge-important')));
    }

    public function testShowMessageMarkAsRead()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'user', 'from' => 'admin', 'object' => 'foo')));
        $crawler = $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(1, count($crawler->filter('.mark-as-read')));
        $messages = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Message')
            ->findAll();
        $msgId = $messages[0]->getId();
        $crawler = $this->client->request('GET', "/message/show/{$msgId}");
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(1, count($crawler->filter('.icon-ok-sign')));
        $this->assertEquals(0, count($crawler->filter('.alert-envelope')));
    }

    public function testRemoveMessageFromUser()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('from' => 'user', 'to' => 'admin', 'object' => 'foo')));
        $crawler = $this->logUser($this->getUser('user'));
        $userMessages = $this->em->getRepository('ClarolineCoreBundle:UserMessage')
            ->findBy(array('user' => $this->getUser('user')->getId()));
        $usrmsgId = $userMessages[0]->getId();
        $this->client->request('GET', "/message/delete/from?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testRemoveMessageToUser()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'user', 'from' => 'admin', 'object' => 'foo')));
        $crawler = $this->logUser($this->getUser('user'));
        $userMessages = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:UserMessage')
            ->findAll();
        $usrmsgId = $userMessages[0]->getId();
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testSearchSendMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('content' => 'content', 'object' => 'object', 'to' => 'user'))
        );
        //search by name
        $crawler = $this->client->request('GET', '/message/sent/page/1/search/user');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/sent/page/1/search/invalid');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        //search by object
        $crawler = $this->client->request('GET', '/message/sent/page/1/search/object');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testSearchReceivedMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('content' => 'content', 'object' => 'object', 'to' => 'user'))
        );
        $this->logUser($this->getUser('user'));
        //search by name
        $crawler = $this->client->request('GET', '/message/received/page/1/search/admin');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/received/page/1/search/invalid');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        //search by object
        $crawler = $this->client->request('GET', '/message/received/page/1/search/object');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testSearchRemovedMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'user', 'from' => 'admin', 'object' => 'foo')));
        $crawler = $this->logUser($this->getUser('user'));
        $userMessages = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:UserMessage')
            ->findAll();
        $usrmsgId = $userMessages[0]->getId();
        $object = $userMessages[0]->getMessage()->getObject();
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', "/message/removed/page/1/search/{$object}");
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testRestoreRemovedReceivedMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'user', 'from' => 'admin', 'object' => 'foo')));
        $this->logUser($this->getUser('user'));
        $userMessages = $this->em->getRepository('ClarolineCoreBundle:UserMessage')
            ->findBy(array('user' => $this->getUser('user')->getId()));
        $usrmsgId = $userMessages[0]->getId();
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        $this->client->request('DELETE', "/message/restore?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testRestoreRemovedSentMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'admin', 'from' => 'user', 'object' => 'foo')));
        $this->logUser($this->getUser('user'));
        $userMessages = $this->em->getRepository('ClarolineCoreBundle:UserMessage')
            ->findBy(array('user' => $this->getUser('user')->getId()));
        $usrmsgId = $userMessages[0]->getId();
        $crawler = $this->client->request('GET', '/message/sent/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/sent/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        $this->client->request('DELETE', "/message/restore?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/sent/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testDeletePermanentlyReceivedMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'user', 'from' => 'admin', 'object' => 'foo')));
        $this->logUser($this->getUser('user'));
        $userMessages = $this->em->getRepository('ClarolineCoreBundle:UserMessage')
            ->findBy(array('user' => $this->getUser('user')->getId()));
        $usrmsgId = $userMessages[0]->getId();
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->client->request('DELETE', "/message/delete/trash?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/received/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
    }

    public function testDeletePermanentlySentMessage()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadMessagesData(array(array('to' => 'admin', 'from' => 'user', 'object' => 'foo')));
        $this->logUser($this->getUser('user'));
        $userMessages = $this->em->getRepository('ClarolineCoreBundle:UserMessage')
            ->findBy(array('user' => $this->getUser('user')->getId()));
        $usrmsgId = $userMessages[0]->getId();
        $crawler = $this->client->request('GET', '/message/sent/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
        $this->client->request('DELETE', "/message/delete/trash?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/removed/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
        $crawler = $this->client->request('GET', '/message/sent/page');
        $this->assertEquals(0, count($crawler->filter('.row-user-message')));
    }
}

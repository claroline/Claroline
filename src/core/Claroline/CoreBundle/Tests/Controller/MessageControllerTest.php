<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadMessagesData;

class MessageControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testMessageForm()
    {
        $this->loadUserFixture(array('user'));
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/message/form");
        $form = $crawler->filter('#message_form');
        $this->assertEquals(count($form), 1);
    }

    public function testMessageGroupForm()
    {
        $this->loadUserFixture(array('user'));
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/message/form/group/{$this->getFixtureReference('group/group_a')->getId()}");
        $form = $crawler->filter('#message_form');
        $this->assertEquals(count($form), 1);
        $parameters = $this->client->getRequest()->query->all();
        $this->assertEquals($parameters['ids'][0], $this->getFixtureReference('user/user')->getId());
    }

    public function testSendMessage()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('content' => 'content', 'object' => 'object', 'to' => 'user'))
        );

       $crawler = $this->client->request('GET', '/message/list/sent');
       $this->assertEquals(1, count($crawler->filter('.row-message')));
       $this->logUser($this->getFixtureReference('user/user'));
       $crawler = $this->client->request('GET', '/message/list/received');
       $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }

    public function testSendMessageReturnsFormOnError()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
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
        $this->loadUserFixture(array('user', 'admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'POST',
            "/message/send/0",
            array('message_form' => array('content' => 'content', 'object' => 'object', 'to' => 'user'))
        );
        $this->logUser($this->getFixtureReference('user/user'));
        $msgId = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository("ClarolineCoreBundle:Message")->findOneBy(array('object' => 'object'))->getId();
        $this->client->request(
            'POST',
            "/message/send/{$msgId}",
            array('message_form' => array('content' => 'content', 'object' => 'answer', 'to' => 'admin'))
        );
       $this->logUser($this->getFixtureReference('user/admin'));
       $msgId = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository("ClarolineCoreBundle:Message")->findOneBy(array('parent' => $msgId))->getId();
       $crawler = $this->client->request('GET', "message/show/{$msgId}");
       $this->assertEquals(2, count($crawler->filter('.message-show')));
    }

    public function testAlertOnReceivedMessage()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->loadFixture(new LoadMessagesData(array('to' => 'user'), 1));
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $this->assertEquals(1, count($crawler->filter('.alert-envelope')));
    }

    public function testShowMessageMarkAsRead()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->loadFixture(new LoadMessagesData(array('to' => 'user'), 1));
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/message/list/received');
        $this->assertEquals(1, count($crawler->filter('.icon-warning-sign')));
        $messages = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Message')->findAll();
        $msgId = $messages[0]->getId();
        $crawler = $this->client->request('GET', "/message/show/{$msgId}");
        $crawler = $this->client->request('GET', '/message/list/received');
        $this->assertEquals(1, count($crawler->filter('.icon-ok-sign')));
        $this->assertEquals(0, count($crawler->filter('.alert-envelope')));
    }

    public function testRemoveMessageFromUser()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->loadFixture(new LoadMessagesData(array('from' => 'user'), 1));
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $messages = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Message')->findAll();
        $msgId = $messages[0]->getId();
        $this->client->request('GET', "/message/delete/from?ids[]={$msgId}");
        $crawler = $this->client->request('GET', '/message/list/sent/removed');
        $this->assertEquals(1, count($crawler->filter('.row-message')));
    }

    public function testRemoveMessageToUser()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->loadFixture(new LoadMessagesData(array('to' => 'user'), 1));
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $userMessages = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:UserMessage')->findAll();
        $usrmsgId = $userMessages[0]->getId();
        $this->client->request('GET', "/message/delete/to?ids[]={$usrmsgId}");
        $crawler = $this->client->request('GET', '/message/list/received/removed');
        $this->assertEquals(1, count($crawler->filter('.row-user-message')));
    }
}



<?php

namespace Claroline\MessageBundle\Tests\API;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;
use Claroline\CoreBundle\Entity\User;

class MessageControllerTest extends TransactionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
    }

    public function testGetReceivedAction()
    {
        $user = $this->createUser('user');
        $receipt = $this->createUser('receipt');
        $message = $this->createMessage('message content', 'object', [$receipt], $user);
        $this->logIn($receipt);
        $this->client->request('GET', '/message/api/received');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['message']['object'], 'object');
    }

    public function testGetSentAction()
    {
        $user = $this->createUser('user');
        $receipt = $this->createUser('receipt');
        $message = $this->createMessage('message content', 'object', [$receipt], $user);
        $this->logIn($user);
        $this->client->request('GET', '/message/api/sent');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals($data[0]['message']['object'], 'object');
    }

    public function testGetRemovedAction()
    {
        $user = $this->createUser('user');
        $receipt = $this->createUser('receipt');
        $message = $this->createMessage('message content', 'object', [$user], $user);
        $this->logIn($user);
        $this->client->request('GET', '/message/api/removed');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(count($data), 0);

        // the message manager needs to be upgraded to do better tests. Message suppression is not easy to simulate right now
        // because it uses the UserMessage entity and not Message + User
        // will do later
    }

    private function createUser($name)
    {
        $user = $this->persister->user($name);
        $this->persister->persist($user);

        return $user;
    }

    private function createMessage($content, $object, array $users, User $sender)
    {
        $messageManager = $this->client->getContainer()->get('claroline.manager.message_manager');
        $message = $messageManager->create($content, $object, $users, $sender);
        $messageManager->send($message, true, false);
    }
}

<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\ForumBundle\DataFixtures\LoadOptionsData;

class ForumListenerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator', 'admin' => 'admin'));
        $this->client->followRedirects();
        $this->resourceRepository = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
    }

    public function testForumFormCreation()
    {
         $this->logUser($this->getUser('user'));
         $crawler = $this->client->request('GET', 'resource/form/claroline_forum');
         $this->assertEquals(1, count($crawler->filter('#forum_form')));
    }

    public function testForumCreation()
    {
        $this->logUser($this->getUser('user'));
        $userRoot = $this->resourceRepository
            ->findWorkspaceRoot($this->getUser('user')->getPersonalWorkspace());
        $this->client->request(
            'POST',
            "/resource/create/claroline_forum/{$userRoot->getId()}",
            array('forum_form' => array('name' => 'test'))
        );
        $this->assertEquals(count(json_decode($this->client->getResponse()->getContent())), 1);
    }

    public function testForumIndex()
    {
        $this->loadFixture(new LoadOptionsData());
        $this->logUser($this->getUser('user'));
        $userRoot = $this->resourceRepository
            ->findWorkspaceRoot($this->getUser('user')->getPersonalWorkspace());
        $this->client->request(
            'POST',
            "/resource/create/claroline_forum/{$userRoot->getId()}",
            array('forum_form' => array('name' => 'test'))
        );
        $datas = json_decode($this->client->getResponse()->getContent());
        $crawler = $this->client->request('POST', "/resource/open/claroline_forum/{$datas[0]->id}");
        $this->assertEquals(1, count($crawler->filter('#subjects_table')));
    }

    public function testForumAdministration()
    {
        $this->loadFixture(new LoadOptionsData());
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request('GET', "/admin/plugin/clarolineforum/options");
        $this->assertEquals(1, count($crawler->filter('#forum_options_form')));
        $this->client->request(
            'POST',
            '/forum/options/edit',
            array('forum_options_form' => array('subjects' => 20, 'messages' => 20))
        );
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $options = $em->getRepository('ClarolineForumBundle:ForumOptions')->findAll();
        $this->assertEquals(20, $options[0]->getSubjects());
    }
}
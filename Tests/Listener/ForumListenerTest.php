<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Event\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\ForumBundle\DataFixtures\LoadOptionsData;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;

class ForumListenerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->addResourceTypeToTemplate('claroline_forum');
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator', 'admin' => 'admin'));
        $this->client->followRedirects();
        $this->resourceRepository = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
    }

    public function tearDown()
    {
        $this->resetTemplate();
        parent::tearDown();
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

    public function testForumCopy()
    {
        $this->loadFixture(new LoadForumData('test', 'user', 2, 2));
        $event = new CopyResourceEvent($this->getForum('test'));
        $this->client->getContainer()->get('event_dispatcher')->dispatch('copy_claroline_forum', $event);
        $this->assertEquals(1, count($event->getCopy()));
    }

    public function testForumCopyIsFlushed()
    {
         $this->loadFixture(new LoadForumData('test', 'user', 2, 2));
         $rootDirId = $this->getDirectory('user')->getId();
         $this->logUser($this->getUser('user'));
         $this->client->request(
             'GET',
             "/resource/copy/{$rootDirId}?ids[]={$this->getForum('test')->getId()}"
         );
         $this->client->request('GET', "/resource/directory/{$rootDirId}");
         $dir = json_decode($this->client->getResponse()->getContent());
         $this->assertObjectHasAttribute('resources', $dir);
         $this->assertEquals(2, count($dir->resources));
    }

    public function testExportTemplateForum()
    {
         $this->loadFixture(new LoadForumData('test', 'user', 2, 2));
         $event = new ExportResourceTemplateEvent($this->getForum('test'));
         $this->client->getContainer()->get('event_dispatcher')
             ->dispatch('resource_claroline_forum_to_template', $event);
         $config = $event->getConfig();
         $this->assertEquals(2, count($config['subjects']));
    }

    public function testImportTemplateForum()
    {
        $forum = array(
            'subjects' => array(
                array('title' => 'title 1', 'initial_message' => 'message 1'),
                array('title' => 'title 2', 'initial_message' => 'message 2')
            )
        );

        $event = new ImportResourceTemplateEvent(
            $forum,
            $this->getDirectory('user'),
            $this->getUser('user')
        );

        $this->client->getContainer()->get('event_dispatcher')
            ->dispatch('resource_claroline_forum_from_template', $event);

        $this->assertEquals(2, count($event->getResource()->getSubjects()));
    }
}

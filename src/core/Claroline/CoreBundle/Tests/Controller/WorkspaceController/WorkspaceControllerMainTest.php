<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceControllerMainTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadWorkspaceFixture();
        $this->client->followRedirects();
    }

    public static function setUpBeforeClass()
    {
        $client = self::createClient();
        $container = $client->getContainer();
        $dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $pluginDirectory = $container->getParameter('claroline.stub_plugin_directory');
        $loader = new \Claroline\CoreBundle\Library\Installation\Plugin\Loader($pluginDirectory);
        $pluginFqcn = 'Valid\WithWidgets\ValidWithWidgets';
        $plugin = $loader->load($pluginFqcn);
        $dbWriter->insert($plugin);
    }

    public static function tearDownAfterClass()
    {
        $client = self::createClient();
        $container = $client->getContainer();
        $pluginDirectory = $container->getParameter('claroline.stub_plugin_directory');
        $loader = new \Claroline\CoreBundle\Library\Installation\Plugin\Loader($pluginDirectory);
        $pluginFqcn = 'Valid\WithWidgets\ValidWithWidgets';
        $plugin = $loader->load($pluginFqcn);
        $container->get('claroline.plugin.recorder')->unregister($plugin);
        $container->get('claroline.plugin.migrator')->remove($plugin);
    }

    public function testWSCreatorCanSeeHisWS()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $link = $crawler->filter('#link-my-workspaces')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(4, $crawler->filter('.row-workspace')->count());
    }

    public function testAdminCanSeeHisWs()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link-my-workspaces')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(2, $crawler->filter('.row-workspace')->count());
    }

    public function testWSCreatorCanCreateWS()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $link = $crawler->filter('#link-create-ws-form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_form[name]'] = 'new_workspace';
        $form['workspace_form[type]'] = 'simple';
        $form['workspace_form[code]'] = 'code';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(7, $crawler->filter('.row-workspace')->count());
    }

    public function testWSCreatorCanDeleteHisWS()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_d')->getId()}");
        $crawler = $this->client->request('GET', "/workspaces/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(3, $crawler->filter('.row-workspace')->count());
    }

    public function testWSManagerCanSeeHisWS()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('GET', "/workspaces/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $link = $crawler->filter("#link-home-{$this->getFixtureReference('workspace/ws_d')->getId()}")->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, $crawler->filter(".welcome-home")->count());
    }

    public function testUserCanSeeWSList()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(6, $crawler->filter('.row-workspace')->count());
    }

    //111111111111111111111111111111111
    //++++++++++++++++++++++++++++++/
    // ACCESS WORKSPACE MAIN PAGES +/
    //++++++++++++++++++++++++++++++/

    public function testDisplayResource()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $crawler = $this->client->request('GET', "/workspaces/resource/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}");
         $this->assertEquals(1, count($crawler->filter('html:contains("Resource manager")')));
    }

    public function testUserCantAccessUnregisteredResource()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/resource/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayHome()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testUserCantAccessUnregisteredHome()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUserManagement()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/tools/user_management");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserManagement()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}/tools/user_management");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUnregisteredUserList()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/tools/users/unregistered");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserList()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}/tools/users/unregistered");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUserParameters()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/tools/user/{$this->getFixtureReference('user/user')->getId()}");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testUserCantAccessUnregisteredUserParameters()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}/tools/user/{$this->getFixtureReference('user/user')->getId()}");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayGroupManagement()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/tools/group_management");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredGroupManagement()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}/tools/group_management");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUnregisteredGroupList()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/tools/groups/unregistered");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredGroupList()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}/tools/groups/unregistered");
         $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testManagerCanSeeWidgetProperties()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/properties/widget/display");
        $this->assertGreaterThan(3, count($crawler->filter('.row-widget-config')));
    }

    public function testManagerCanInvertWidgetVisible()
    {
        //admin must unlock first
        $this->logUser($this->getFixtureReference('user/user'));
        $configs = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findAll();
        $countConfigs = count($configs);
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/widgets");
        $countVisibleWidgets = count($crawler->filter('.widget-content'));
        $this->client->request(
            'POST', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/widget/{$configs[0]->getWidget()->getId()}/baseconfig/{$configs[0]->getId()}/invertvisible"
        );
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/widgets");
        $this->assertEquals($countVisibleWidgets, count($crawler->filter('.widget-content')));
        $configs = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findAll();
        $this->assertEquals(++$countConfigs, count($configs));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('POST', "/admin/plugin/lock/{$configs[0]->getId()}");
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/widgets");
        $this->assertEquals(--$countVisibleWidgets, count($crawler->filter('.widget-content')));
    }
}
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
}
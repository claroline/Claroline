<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
    }

    public function testWSCreatorcanViewHisWorkspaces()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $link = $crawler->filter('#link-my-workspaces')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(2, $crawler->filter('.row-workspace')->count());
    }

    public function testAdmincanViewHisWorkspaces()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->loadWorkspaceData(array('ws_e' => 'admin'));
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link-my-workspaces')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(2, $crawler->filter('.row-workspace')->count());
    }

    public function testWSCreatorCanCreateWS()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $link = $crawler->filter('#link-create-ws-form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_form[name]'] = 'new_workspace';
        $form['workspace_form[type]'] = 'simple';
        $form['workspace_form[code]'] = 'code';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(1, $crawler->filter('.row-workspace')->count());
    }

    public function testWSCreatorCanDeleteHisWS()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(
            array(
                'ws_a' => 'ws_creator',
                'ws_b' => 'ws_creator',
                'ws_c' => 'ws_creator',
                'ws_d' => 'ws_creator',
            )
        );
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/{$this->getFixtureReference('workspace/ws_d')->getId()}"
        );
        $crawler = $this->client->request(
            'GET',
            "/workspaces/user/{$this->getFixtureReference('user/ws_creator')->getId()}"
        );
        $this->assertEquals(4, $crawler->filter('.row-workspace')->count());
    }

    public function testWSManagercanViewHisWS()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(
            array(
                'ws_a' => 'ws_creator',
                'ws_b' => 'ws_creator',
                'ws_c' => 'ws_creator',
                'ws_d' => 'ws_creator',
            )
        );
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request(
            'GET',
            "/workspaces/user/{$this->getFixtureReference('user/ws_creator')->getId()}"
        );
        $link = $crawler->filter("#link-home-{$this->getFixtureReference('workspace/ws_d')->getId()}")
            ->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, $crawler->filter(".welcome-home")->count());
    }

    public function testUsercanViewWSList()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->loadWorkspaceData(
            array(
            'ws_e' => 'admin',
            'ws_f' => 'admin'
            )
        );
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(2, $crawler->filter('.row-workspace')->count());
    }

    //111111111111111111111111111111111
    //++++++++++++++++++++++++++++++/
    // ACCESS WORKSPACE MAIN PAGES +/
    //++++++++++++++++++++++++++++++/

    public function testUserCantAccessUnregisteredResource()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwuId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwuId}/open/tool/resource_manager"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayHome()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwsId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwsId}/open/tool/home"
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredHome()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwaId}/open/tool/home"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUserManagement()
    {
        $this->loadUserData(array('user' => 'user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/{$pwuId}/open/tool/user_management");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserManagement()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/{$pwaId}/open/tool/user_management");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUnregisteredUserList()
    {
        $this->loadUserData(array('user' => 'user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/tool/user_management/{$pwuId}/users/unregistered");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserList()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/tool/user_management/{$pwaId}/users/unregistered");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUserParameters()
    {
        $this->loadUserData(array('user' => 'user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwuId}/user/{$this->getFixtureReference('user/user')->getId()}"
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserParameters()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $userId = $this->getFixtureReference('user/user')->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/tool/user_management/{$pwaId}/user/{$userId}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayGroupManagement()
    {
        $this->loadUserData(array('user' => 'user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/{$pwuId}/open/tool/group_management");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredGroupManagement()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/{$pwaId}/open/tool/group_management");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUnregisteredGroupList()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->client->request('GET', "/workspaces/tool/group_management/{$pwuId}/groups/unregistered");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredGroupList()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request('GET', "/workspaces/tool/group_management/{$pwaId}/groups/unregistered");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testWSCreatorCantCreateTwoWSWithSameCode()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $link = $crawler->filter('#link-create-ws-form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_form[name]'] = 'first_new_workspace';
        $form['workspace_form[type]'] = 'simple';
        $form['workspace_form[code]'] = 'same_code';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(1, $crawler->filter('.row-workspace')->count());
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_form[name]'] = 'second_new_workspace';
        $form['workspace_form[type]'] = 'simple';
        $form['workspace_form[code]'] = 'same_code';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(1, $crawler->filter('.row-workspace')->count());
    }
}
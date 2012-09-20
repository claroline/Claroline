<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadManyUsersData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadManyGroupsData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadRoleData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadGroupData;

class WorkspaceControllerTest extends FunctionalTestCase
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

    public function testDeleteGroupFromWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
        $this->client->request('DELETE', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/manyGroup1')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(0, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testLimitedGroupList()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0/unregistered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(13, count($groups));
    }

    public function testPaginatedGroupsOfWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData);
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/group_a')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));;
    }

    public function testSearchUnregisteredGroupsByNameWithAjax()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/search/a", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
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

    public function testDisplayTools()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/user')->getPersonalWorkspace()->getId()}/tools");
         $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

    }

    public function testUserCantAccessUnregisteredTools()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $this->client->request('GET', "/workspaces/{$this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId()}/tools");
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


    //66666666666666666666666666666666666
    //++++++++++++++++++/
    // TEST ADD GROUPS +/
    //++++++++++++++++++/

    public function testAddGroup()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData);
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/group_a')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));;
    }

    public function testAddGroupIsProtected()
    {
        $this->markTestSkipped('not implemented yet');
    }

    public function testMultiAddGroup()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData);

        $groupA = $this->getFixtureReference('group/group_a')->getId();
        $groupB = $this->getFixtureReference('group/group_b')->getId();
        $groupC = $this->getFixtureReference('group/group_c')->getId();

        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/add/group?0={$groupA}&1={$groupB}&2={$groupC}"
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(3, count($jsonResponse));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent())));;
    }

    public function testMultiAddGroupIsProtected()
    {
        $this->markTestSkipped('not implemented yet');
    }
}
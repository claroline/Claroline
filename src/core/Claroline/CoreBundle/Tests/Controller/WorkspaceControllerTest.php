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

    public function testDeleteUserFromWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user_management");
        $this->assertEquals(1, $crawler->filter(".row-user")->count());
        $this->client->request('DELETE', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $crawler = $this->client->request('GET', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user_management");
        $this->assertEquals(0, $crawler->filter(".row-user")->count());
    }

    public function testDeleteGroupFromWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/group_management");
        $this->assertEquals(1, $crawler->filter(".row-group")->count());
        $this->client->request('DELETE', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/manyGroup1')->getId()}");
        $crawler = $this->client->request('GET', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/group_management");
        $this->assertEquals(0, $crawler->filter(".row-group")->count());
    }

    public function testLimitedUserList()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(25, count($users));
    }


    public function testLimitedGroupList()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(13, count($groups));
    }

    //todo: fix a bug wich happens when the response return only 1 user
    public function testSearchUnregisteredUsers()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/doe", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(4, count($users));

        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/firstName", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(30, count($users));
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

    public function testAddGroup()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData);
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/group_a')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/group_management");
        $this->assertEquals(1, $crawler->filter(".row-group")->count());
    }

    public function testAddUser()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user_management");
        $this->assertEquals(2, $crawler->filter(".row-user")->count());
    }

    public function testMultiAddUser()
    {
        $userId = $this->getFixtureReference('user/user')->getId();
        $secondUserId = $this->getFixtureReference('user/user_2')->getId();
        $thirdUserId = $this->getFixtureReference('user/user_3')->getId();

        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/add/user?0={$userId}&1={$secondUserId}&2={$thirdUserId}"
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(3, count($jsonResponse));
        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user_management");
        $this->assertEquals(4, count($crawler->filter('.row-user')));
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

        $crawler = $this->client->request('GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/group_management");

        $this->assertEquals(3, count($crawler->filter('.row-group')));
    }
}
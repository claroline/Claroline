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
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
        $this->client->request('DELETE', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(0, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testDeleteGroupFromWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
        $this->client->request('DELETE', "workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/manyGroup1')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(0, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testLimitedUserList()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/1/unregistered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
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
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/1/unregistered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(13, count($groups));
    }

    public function testPaginatedUsersOfWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
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
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));;
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
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/group/{$this->getFixtureReference('group/group_a')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));;
    }

    public function testAddUser()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
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
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(4, count(json_decode($this->client->getResponse()->getContent())));
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
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/groups/1/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent())));;
    }
}
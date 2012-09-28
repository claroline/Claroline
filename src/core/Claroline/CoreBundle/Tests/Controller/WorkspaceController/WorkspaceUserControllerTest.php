<?php
namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadManyUsersData;

class WorkspaceUserControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadWorkspaceFixture();
        $this->client->followRedirects();
    }

    //1111111111111111111
    //++++++++++++++++++/
    //+ TEST ADD USERS +/
    //++++++++++++++++++/
/*
    public function testAddUser()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $user = json_decode($response);
        $this->assertEquals(1, count($user));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testAddUserIsProtected()
    {
        $this->markTestSkipped('not yet implemented');
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
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(4, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testMultiAddUserIsProtected()
    {
        $this->markTestSkipped('not yet implemented');
    }

    //222222222222222222222222
    //+++++++++++++++++++++++/
    //+ TEST REMOVING USERS +/
    //+++++++++++++++++++++++/
    public function testDeleteUserFromWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
        $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    //only admins can delete
    public function testDeleteUserFromWorkspaceIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCantRemoveLastManager()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        //adds user/user in the workspace
        $this->client->request('PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        //sets user/user as a manager
        $this->client->request('POST', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user/{$this->getFixtureReference('user/user')->getId()}", array('form' => array('role' => $this->getFixtureReference('workspace/ws_a')->getManagerRole()->getId()))
        );
        $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $crawler = $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
//        var_dump($this->client->getResponse()->getContent());
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testCantRemoveManagerPersonnal()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $this->client->request('PUT', "/workspaces/{$pwu->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->client->request('POST', "/workspaces/{$pwu->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}", array('form' => array('role' => $this->getFixtureReference('workspace/ws_a')->getManagerRole()->getId()))
        );
        $crawler = $this->client->request('DELETE', "/workspaces/{$pwu->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("original manager")')));
    }

    public function testMultiDeleteUserFromWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
        $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users?0={$this->getFixtureReference('user/user')->getId()}");
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testCantMultiremoveLastManager()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users?0={$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testMultiDeleteUserFromWorkspaceIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('DELETE', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users?0={$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCantMultiremoveManagerPersonal()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $this->client->request('PUT', "/workspaces/{$pwu->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->client->request('POST', "/workspaces/{$pwu->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}", array('form' => array('role' => $this->getFixtureReference('workspace/ws_a')->getManagerRole()->getId())));
        $crawler = $this->client->request('DELETE', "/workspaces/{$pwu->getId()}/users?0={$this->getFixtureReference('user/user')->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("personal workspace")')));
    }

    //333333333333333333333333
    //+++++++++++++++++++++++/
    // TEST USER PROPERTIES +/
    //+++++++++++++++++++++++/

    public function testUserPropertiesCanBeEdited()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $this->client->request('PUT', "/workspaces/{$pwu->getId()}/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->client->request('GET', "/workspaces/{$pwu->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('POST', "/workspaces/{$pwu->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}", array('form' => array('role' => $pwu->getManagerRole()->getId())));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request(
            'GET', "/workspaces/{$pwu->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $users = json_decode($this->client->getResponse()->getContent());

        foreach ($users as $user) {
            $this->assertEquals('Manager', $user->roles);
        }
    }

    //only admins can edit properties
    public function testUserPropertiesIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('PUT', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->request('POST', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}", array('form' => array('role' => $this->getFixtureReference('workspace/ws_a')->getVisitorRole()->getId())));
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testLastManagerCantEditHisRole()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('POST', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/tools/user/{$this->getFixtureReference('user/ws_creator')->getId()}", array('form' => array('role' => $this->getFixtureReference('workspace/ws_a')->getVisitorRole()->getId()))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testPersonalWsOrignalManagerCantEditHisRole()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $crawler = $this->client->request('POST', "/workspaces/{$pwu->getId()}/tools/user/{$this->getFixtureReference('user/user')->getId()}", array('form' => array('role' => $pwu->getVisitorRole()->getId()))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("personal workspace")')));
    }

    //4444444444444444444
    //++++++++++++++++++/
    // TEST USER LISTS +/
    //++++++++++++++++++/
*/
    public function testUnregisteredUserList()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $users = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:User')->findAll();
        var_dump(count($users));
        $countpwr = 0;
        $countRole = 0;
        foreach ($users as $user){
            if($user->getPersonalWorkspace() != null){
                $countpwr++;
            }

            if(count($user->getRoles()) > 0){
                $countRole++;
            }
        }
        var_dump($countpwr);
        var_dump($countRole);
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/unregistered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(25, count($users));
    }/*

    public function testUnregisteredUserListIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/unregistered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testRegisteredUsersOfWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testRegisteredUsersOfWorkspaceIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/users/0/registered", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchUnregisteredUsers()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/doe/unregistered/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(4, count($users));

        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/firstName/unregistered/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(25, count($users));
    }

    public function testSearchUnregisteredUsersIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/doe/unregistered/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchRegisteredUsers()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/doe/registered/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(1, count($users));
    }

    public function testSearchRegisteredUsersIsProtected()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'GET', "/workspaces/{$this->getFixtureReference('workspace/ws_a')->getId()}/user/search/doe/registered/0", array(), array(), array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }*/
}
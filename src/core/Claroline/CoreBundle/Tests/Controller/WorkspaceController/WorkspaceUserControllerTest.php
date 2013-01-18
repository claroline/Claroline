<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceUserControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    //1111111111111111111
    //++++++++++++++++++/
    //+ TEST ADD USERS +/
    //++++++++++++++++++/

    public function testMultiAddAndDeleteUser()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->loadWorkspaceFixture(array('ws_a'));
        $userId = $this->getFixtureReference('user/user')->getId();
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();

        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'PUT', "/workspaces/{$wsAId}/add/user?userIds[]={$userId}"
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
        $this->client->request(
            'GET',
            "/workspaces/".$wsAId."/users/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
        $this->client->request(
            'DELETE',
            "/workspaces/{$wsAId}/users?userIds[]={$userId}"
        );
        $this->client->request(
            'GET',
            "/workspaces/{$wsAId}/users/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testMultiAddUserIsProtected()
    {
        $this->markTestSkipped('not yet implemented');
    }

    //222222222222222222222222
    //+++++++++++++++++++++++/
    //+ TEST REMOVING USERS +/
    //+++++++++++++++++++++++/

    public function testCantMultiremoveLastManager()
    {
        $this->loadUserFixture();
        $this->loadWorkspaceFixture();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $creatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/{$wsAId}/users?userIds[]={$creatorId}"
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testMultiDeleteUserFromWorkspaceIsProtected()
    {
        $this->loadUserFixture(array('ws_creator', 'user'));
        $this->loadWorkspaceFixture(array('ws_a'));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $creatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $userId = $this->getFixtureReference('user/user')->getId();
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->client->request(
            'PUT',
            "/workspaces/{$wsAId}/user/{$userId}"
        );
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'DELETE',
            "/workspaces/{$wsAId}/users?userIds[]={$creatorId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCantMultiremoveManagerPersonal()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->loadWorkspaceFixture(array('ws_a'));
        $this->logUser($this->getFixtureReference('user/user'));
        $creatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $userId = $this->getFixtureReference('user/user')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $this->client->request(
            'PUT',
            "/workspaces/{$pwu->getId()}/add/user?userIds[]={$creatorId}"
        );
        $this->client->request(
            'POST',
            "/workspaces/{$pwu->getId()}/tools/user/{$creatorId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->getManagerRole($this->getFixtureReference('workspace/ws_a'))))
        );
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/{$pwu->getId()}/users?userIds[]={$userId}"
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("personal workspace")')));
    }

    //333333333333333333333333
    //+++++++++++++++++++++++/
    // TEST USER PROPERTIES +/
    //+++++++++++++++++++++++/

    public function testUserPropertiesCanBeEdited()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->logUser($this->getFixtureReference('user/user'));
        $creatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $this->client->request(
            'PUT',
            "/workspaces/{$pwu->getId()}/add/user?userIds[]={$creatorId}"
        );
        $this->client->request(
            'GET',
            "/workspaces/{$pwu->getId()}/tools/user/{$creatorId}"
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request(
            'POST',
            "/workspaces/{$pwu->getId()}/tools/user/{$creatorId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->getManagerRole($pwu)->getId()))
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request(
            'GET',
            "/workspaces/{$pwu->getId()}/users/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $users = json_decode($this->client->getResponse()->getContent());
        $managerRole = $this->client->getContainer()
            ->get('translator')
            ->trans('manager', array(), 'platform');

        foreach ($users as $user) {
            $this->assertContains($managerRole, $user->roles);
        }
    }

    //only admins can edit properties
    public function testUserPropertiesIsProtected()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $creatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $pwcId = $this->getFixtureReference('user/ws_creator')->getPersonalWorkspace()->getId();
        $userId = $this->getFixtureReference('user/user')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->request(
            'PUT',
            "/workspaces/{$pwcId}/user/{$userId}"
        );
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'GET',
            "/workspaces/{$pwcId}/tools/user/{$creatorId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $visitorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->getVisitorRole($this->getFixtureReference('user/ws_creator')->getPersonalWorkspace())
            ->getId();
        $this->client->request(
            'POST',
            "/workspaces/{$pwcId}/tools/user/{$pwcId}",
            array('form' => array('role' => $visitorRoleId))
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testLastManagerCantEditHisRole()
    {
        $this->loadUserFixture(array('ws_creator'));
        $this->loadWorkspaceFixture(array('ws_a'));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $creatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $visitorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->getVisitorRole($this->getFixtureReference('workspace/ws_a'))
            ->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/{$wsAId}/tools/user/{$creatorId}",
            array('form' => array('role' => $visitorRoleId))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testPersonalWsOrignalManagerCantEditHisRole()
    {
        $this->loadUserFixture(array('user'));
        $this->logUser($this->getFixtureReference('user/user'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace();
        $userId = $this->getFixtureReference('user/user')->getId();
        $visitorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->getVisitorRole($pwu)
            ->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/{$pwu->getId()}/tools/user/{$userId}",
            array('form' => array('role' => $visitorRoleId))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("personal workspace")')));
    }

    //4444444444444444444
    //++++++++++++++++++/
    // TEST USER LISTS +/
    //++++++++++++++++++/

    public function testUnregisteredUserList()
    {
        $this->loadUserFixture();
        $this->logUser($this->getFixtureReference('user/user'));
        $users = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:User')
            ->findAll();
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwuId}/users/0/unregistered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(4, count($users));
    }

    public function testUnregisteredUserListIsProtected()
    {
        $this->loadUserFixture(array('ws_creator', 'user'));
        $this->loadWorkspaceFixture(array('ws_a'));
        $this->logUser($this->getFixtureReference('user/user'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->client->request(
            'GET', "/workspaces/{$wsAId}/users/0/unregistered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testRegisteredUsersOfWorkspace()
    {
        $this->loadUserFixture(array('ws_creator', 'user'));
        $this->loadWorkspaceFixture(array('ws_a'));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$wsAId}/users/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testRegisteredUsersOfWorkspaceIsProtected()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwcId = $this->getFixtureReference('user/ws_creator')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET', "/workspaces/{$pwcId}/users/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchUnregisteredUsers()
    {
        $this->loadUserFixture();
        $this->loadWorkspaceFixture(array('ws_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->client->request(
            'GET', "/workspaces/{$wsAId}/user/search/doe/unregistered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(4, count($users));

        $this->client->request(
            'GET', "/workspaces/{$wsAId}/user/search/bob/unregistered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(1, count($users));
    }

    public function testSearchUnregisteredUsersIsProtected()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwcId = $this->getFixtureReference('user/ws_creator')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET', "/workspaces/{$pwcId}/user/search/doe/unregistered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchRegisteredUsers()
    {
        $this->loadUserFixture(array('admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET', "/workspaces/{$pwaId}/user/search/doe/registered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $users = json_decode($response);
        $this->assertEquals(1, count($users));
    }

    public function testSearchRegisteredUsersIsProtected()
    {
        $this->loadUserFixture(array('user', 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET', "/workspaces/{$pwaId}/user/search/doe/registered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

}
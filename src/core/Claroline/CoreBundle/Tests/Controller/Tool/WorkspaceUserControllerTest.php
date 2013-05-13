<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceUserControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRoleData();
    }

    //1111111111111111111
    //++++++++++++++++++/
    //+ TEST ADD USERS +/
    //++++++++++++++++++/

    public function testMultiAddAndDeleteUser()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $userId = $this->getUser('user')->getId();
        $wsAId = $this->getWorkspace('ws_a')->getId();

        $this->logUser($this->getUser('ws_creator'));
        $this->client->request(
            'PUT', "/workspaces/tool/user_management/{$wsAId}/add/user?ids[]={$userId}"
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
        $crawler = $this->client->request('GET', "/workspaces/tool/user_management/{$wsAId}/users/registered/page");
        $this->assertEquals(2, $crawler->filter('.row-user')->count());
        $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$userId}"
        );
        $crawler = $this->client->request('GET', "/workspaces/tool/user_management/{$wsAId}/users/registered/page");
        $this->assertEquals(1, $crawler->filter('.row-user')->count());
    }

    public function testMultiAddUserIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $pwu = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user_2'));
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$pwu}/add/user?ids[]=1"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //222222222222222222222222
    //+++++++++++++++++++++++/
    //+ TEST REMOVING USERS +/
    //+++++++++++++++++++++++/

    public function testCantMultiremoveLastManager()
    {
        $this->loadUserData(
            array(
                'user' => 'user',
                'user_2' => 'user',
                'user_3' => 'user',
                'ws_creator' => 'ws_creator',
                'admin' => 'admin'
            )
        );

        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));

        $this->logUser($this->getUser('ws_creator'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$creatorId}"
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testMultiDeleteUserFromWorkspaceIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('ws_creator'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $userId = $this->getUser('user')->getId();
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$wsAId}/user/{$userId}"
        );
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$creatorId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCantMultiremoveManagerPersonal()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('user'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $userId = $this->getUser('user')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $pwu = $this->getUser('user')->getPersonalWorkspace();
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$pwu->getId()}/add/user?ids[]={$creatorId}"
        );
        $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$pwu->getId()}/user/{$creatorId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($this->getWorkspace('ws_a'))))
        );
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$pwu->getId()}/users?ids[]={$userId}"
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
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->logUser($this->getUser('user'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $pwu = $this->getUser('user')->getPersonalWorkspace();
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$pwu->getId()}/add/user?ids[]={$creatorId}"
        );
        $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwu->getId()}/user/{$creatorId}"
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$pwu->getId()}/user/{$creatorId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($pwu)->getId()))
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        /*
        $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwu->getId()}/users/0/registered",
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
        }*/
    }

    //only admins can edit properties
    public function testUserPropertiesIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->logUser($this->getUser('ws_creator'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $pwcId = $this->getUser('ws_creator')->getPersonalWorkspace()->getId();
        $userId = $this->getUser('user')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$pwcId}/user/{$userId}"
        );
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwcId}/user/{$creatorId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $visitorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->findVisitorRole($this->getUser('ws_creator')->getPersonalWorkspace())
            ->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$pwcId}/user/{$pwcId}",
            array('form' => array('role' => $visitorRoleId))
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testLastManagerCantEditHisRole()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('ws_creator'));
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $creatorId = $this->getUser('ws_creator')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $visitorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->findVisitorRole($this->getWorkspace('ws_a'))
            ->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$wsAId}/user/{$creatorId}",
            array('form' => array('role' => $visitorRoleId))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    public function testPersonalWsOrignalManagerCantEditHisRole()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $pwu = $this->getUser('user')->getPersonalWorkspace();
        $userId = $this->getUser('user')->getId();
        $visitorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->findVisitorRole($pwu)
            ->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$pwu->getId()}/user/{$userId}",
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
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $this->logUser($this->getUser('user'));
        $users = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:User')
            ->findAll();
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwuId}/users/unregistered/page"
        );
        $this->assertEquals(1, $crawler->filter('.row-user')->count());
    }

    public function testUnregisteredUserListIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('user'));
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$wsAId}/users/unregistered/page"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testRegisteredUsersOfWorkspaceIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $pwcId = $this->getUser('ws_creator')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/workspaces/tool/user_management/{$pwcId}/users/registered/page");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchUnregisteredUsers()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator', 'admin' => 'admin'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('admin'));
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$wsAId}/users/unregistered/page/1/search/doe"
        );
        $this->assertEquals(2, $crawler->filter('.row-user')->count());
    }

    public function testSearchUnregisteredUsersIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->logUser($this->getUser('user'));
        $pwcId = $this->getUser('ws_creator')->getPersonalWorkspace()->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwcId}/users/unregistered/page/1/search/doe"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchRegisteredUsers()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $pwaId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwaId}/users/registered/page/1/search/doe"
        );
        $this->assertEquals(1, $crawler->filter('.row-user')->count());
    }

    public function testSearchRegisteredUsersIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('user'));
        $pwaId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwaId}/users/registered/page/1/search/doe"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

}
<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceUserControllerTest extends FunctionalTestCase
{
    private $logRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRoleData();
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    //1111111111111111111
    //++++++++++++++++++/
    //+ TEST ADD USERS +/
    //++++++++++++++++++/

    public function testMultiAddAndDeleteUser()
    {
        $now = new \DateTime();

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

        $addLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $this->getUser('ws_creator')->getId(),
            null,
            $wsAId,
            $userId
        );
        $this->assertEquals(1, count($addLogs));

        $removeLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $this->getUser('ws_creator')->getId(),
            null,
            $wsAId,
            $userId
        );
        $this->assertEquals(1, count($removeLogs));
    }

    public function testMultiAddUserIsProtected()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $pwu = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user_2'));
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$pwu}/add/user?ids[]=1"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $this->getUser('user_2')->getId(),
            null,
            $pwu,
            1
        );
        $this->assertEquals(0, count($logs));
    }

    //222222222222222222222222
    //+++++++++++++++++++++++/
    //+ TEST REMOVING USERS +/
    //+++++++++++++++++++++++/

    public function testCantMultiremoveLastManager()
    {
        $now = new \DateTime();

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

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $creatorId,
            null,
            $wsAId,
            $creatorId
        );
        $this->assertEquals(0, count($logs));
    }

    public function testMultiDeleteUserFromWorkspaceIsProtected()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('ws_creator'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $userId = $this->getUser('user')->getId();
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $this->client->request(
            'PUT', "/workspaces/tool/user_management/{$wsAId}/add/user?ids[]={$userId}"
        );

        $this->logUser($this->getUser('user'));
        $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$creatorId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $addLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $this->getUser('ws_creator')->getId(),
            null,
            $wsAId,
            $userId
        );
        $this->assertEquals(1, count($addLogs));

        $removeLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $this->getUser('user')->getId(),
            null,
            $wsAId,
            $creatorId
        );
        $this->assertEquals(0, count($removeLogs));
    }

    public function testCantMultiremoveManagerPersonal()
    {
        $now = new \DateTime();

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
        $managerRoleId = $em->getRepository('ClarolineCoreBundle:Role')->findManagerRole($pwu)->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$pwu->getId()}/user/{$creatorId}",
            array('form' => array('role' => $managerRoleId))
        );
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$pwu->getId()}/users?ids[]={$userId}"
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("personal workspace")')));

        $addLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $creatorId
        );
        $this->assertEquals(2, count($addLogs));

        $addManagerLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $creatorId,
            $managerRoleId
        );
        $this->assertEquals(1, count($addManagerLogs));

        $removeWsCreatorLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $creatorId
        );
        $this->assertEquals(1, count($removeWsCreatorLogs));

        $removeUserLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $userId
        );
        $this->assertEquals(0, count($removeUserLogs));
    }

    //333333333333333333333333
    //+++++++++++++++++++++++/
    // TEST USER PROPERTIES +/
    //+++++++++++++++++++++++/

    public function testUserPropertiesCanBeEdited()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->logUser($this->getUser('user'));
        $userId = $this->getUser('user')->getId();
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
        $managerRoleId = $em->getRepository('ClarolineCoreBundle:Role')->findManagerRole($pwu)->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/user_management/{$pwu->getId()}/user/{$creatorId}",
            array('form' => array('role' => $managerRoleId))
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $addLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $creatorId
        );
        $this->assertEquals(2, count($addLogs));

        $addManagerLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $creatorId,
            $managerRoleId
        );
        $this->assertEquals(1, count($addManagerLogs));

        $removeWsCreatorLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $creatorId
        );
        $this->assertEquals(1, count($removeWsCreatorLogs));
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
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator'));
        $this->logUser($this->getUser('ws_creator'));
        $creatorId = $this->getUser('ws_creator')->getId();
        $pwcId = $this->getUser('ws_creator')->getPersonalWorkspace()->getId();
        $userId = $this->getUser('user')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->client->request(
            'PUT',
            "/workspaces/tool/user_management/{$pwcId}/add/user?ids[]={$userId}"
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
            "/workspaces/tool/user_management/{$pwcId}/user/{$creatorId}",
            array('form' => array('role' => $visitorRoleId))
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $addLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $creatorId,
            null,
            $pwcId,
            $userId
        );
        $this->assertEquals(1, count($addLogs));

        $failedAddLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $userId,
            null,
            $pwcId,
            $creatorId,
            $visitorRoleId
        );
        $this->assertEquals(0, count($failedAddLogs));

        $failedRemoveLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $userId,
            null,
            $pwcId,
            $creatorId
        );
        $this->assertEquals(0, count($failedRemoveLogs));
    }

    public function testLastManagerCantEditHisRole()
    {
        $now = new \DateTime();

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

        $failedAddLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $creatorId,
            null,
            $wsAId,
            $creatorId,
            $visitorRoleId
        );
        $this->assertEquals(0, count($failedAddLogs));

        $failedRemoveLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $creatorId,
            null,
            $wsAId,
            $creatorId
        );
        $this->assertEquals(0, count($failedRemoveLogs));
    }

    public function testPersonalWsOrignalManagerCantEditHisRole()
    {
        $now = new \DateTime();

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

        $failedAddLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $userId,
            $visitorRoleId
        );
        $this->assertEquals(0, count($failedAddLogs));

        $failedRemoveLogs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $userId,
            null,
            $pwu->getId(),
            $userId
        );
        $this->assertEquals(0, count($failedRemoveLogs));
    }

    //4444444444444444444
    //++++++++++++++++++/
    // TEST USER LISTS +/
    //++++++++++++++++++/

    public function testUnregisteredUserList()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $this->logUser($this->getUser('user'));
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
        $this->client->request(
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
        $this->client->request(
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
        $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwaId}/users/registered/page/1/search/doe"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

}
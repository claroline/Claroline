<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceGroupControllerTest extends FunctionalTestCase
{
    private $logRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRoleData();
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function testMultiAddGroup()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $groupAId = $this->getGroup('group_a')->getId();
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'PUT',
            "/workspaces/tool/group_management/{$pwuId}/add/group?ids[]={$groupAId}"
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwuId}/groups/registered/page"
        );
        $this->assertEquals(1, count($crawler->filter('.row-group')));

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_group',
            $now,
            $this->getUser('user')->getId(),
            null,
            $pwuId,
            null,
            null,
            $groupAId
        );
        $this->assertEquals(1, count($logs));
    }

    public function testMultiAddGroupIsProtected()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user_2'));
        $this->client->request(
            'PUT',
            "/workspaces/tool/group_management/{$pwuId}/add/group?ids[]=1"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_group',
            $now,
            $this->getUser('user_2')->getId(),
            null,
            $pwuId,
            null,
            null,
            1
        );
        $this->assertEquals(0, count($logs));
    }

    //222222222222222222222222
    //+++++++++++++++++++++++/
    //+ TEST REMOVING GROUPS +/
    //+++++++++++++++++++++++/

    public function testMultiDeleteGroupFromWorkspace()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getUser('ws_creator'));
        $wsAId = $this->getWorkspace('ws_a')->getId();

        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/registered/page"
        );
        $this->assertEquals(1, count($crawler->filter('.row-group')));
        $grAId = $this->getGroup('group_a')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/group_management/{$wsAId}/groups?ids[]={$grAId}"
        );
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/registered/page"
        );
        $this->assertEquals(0, count($crawler->filter('.row-group')));

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_group',
            $now,
            $this->getUser('ws_creator')->getId(),
            null,
            $wsAId,
            null,
            null,
            $grAId
        );
        $this->assertEquals(1, count($logs));
    }

    public function testMultiDeleteGroupFromWorkspaceIsProtected()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getUser('user'));
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $grAId = $this->getGroup('group_a')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/group_management/{$wsAId}/groups?ids[]={$grAId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        $logs = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_group',
            $now,
            $this->getUser('user')->getId(),
            null,
            $wsAId,
            null,
            null,
            $grAId
        );
        $this->assertEquals(0, count($logs));
    }

    public function testMultiDeleteCantRemoveLastManager()
    {
        $now = new \DateTime();

        $this->loadUserData(
            array(
                'user' => 'user',
                'user_2' => 'user',
                'ws_creator' => 'ws_creator',
                'admin' => 'admin'
             )
        );
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getUser('admin'));
        $adminId = $this->getUser('admin')->getId();
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $grAId = $this->getGroup('group_a')->getId();
        $managerRoleId = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($this->getWorkspace('ws_a'))->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $managerRoleId))
        );
        $wsCreatorId = $this->getUser('ws_creator')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$wsCreatorId}"
        );
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/tool/group_management/{$wsAId}/groups?ids[]={$grAId}"
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));

        $addGroupToManagerLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_group',
            $now,
            $adminId,
            null,
            $wsAId,
            null,
            $managerRoleId,
            $grAId
        );
        $this->assertEquals(1, count($addGroupToManagerLogs));

        $removeCreatorFromManager = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $adminId,
            null,
            $wsAId,
            $wsCreatorId,
            $managerRoleId
        );
        $this->assertEquals(1, count($removeCreatorFromManager));

        $removeGroupFromManager = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_group',
            $now,
            $adminId,
            null,
            $wsAId,
            null,
            $managerRoleId,
            $grAId
        );
        $this->assertEquals(0, count($removeGroupFromManager));
    }

    //333333333333333333333333
    //+++++++++++++++++++++++/
    // TEST GROUP PROPERTIES +/
    //+++++++++++++++++++++++/

    public function testGroupPropertiesCanBeEdited()
    {
        $now = new \DateTime();

        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $grAId = $this->getGroup('group_a')->getId();
        $managerRoleId = $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($this->getWorkspace('ws_a'))->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $managerRoleId))
        );
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/registered/page"
        );
        $this->assertEquals(1, count($crawler->filter('.row-group')));

        $addGroupToManagerLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_group',
            $now,
            $this->getUser('ws_creator')->getId(),
            null,
            $wsAId,
            null,
            $managerRoleId,
            $grAId
        );
        $this->assertEquals(1, count($addGroupToManagerLogs));
    }

    public function testLastGroupManagerCantBeEdited()
    {
        $now = new \DateTime();

        $this->loadUserData(
            array(
                'user' => 'user',
                'user_2' => 'user',
                'ws_creator' => 'ws_creator',
                'admin' => 'admin'
             )
        );
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getUser('admin'));
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $grAId = $this->getGroup('group_a')->getId();
        $managerRoleId = $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($this->getWorkspace('ws_a'))->getId();
        $adminId = $this->getUser('admin')->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $managerRoleId))
        );
        $wsCreatorId = $this->getUser('ws_creator')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$wsCreatorId}"
        );
        $collaboratorRoleId = $em->getRepository('ClarolineCoreBundle:Role')
                ->findCollaboratorRole($this->getWorkspace('ws_a'))->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $collaboratorRoleId))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));

        $addGroupToManagerLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_group',
            $now,
            $adminId,
            null,
            $wsAId,
            null,
            $managerRoleId,
            $grAId
        );
        $this->assertEquals(1, count($addGroupToManagerLogs));

        $removeCreatorFromManager = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_user',
            $now,
            $adminId,
            null,
            $wsAId,
            $wsCreatorId,
            $managerRoleId
        );
        $this->assertEquals(1, count($removeCreatorFromManager));

        $removeGroupFromManager = $this->logRepository->findActionAfterDate(
            'ws_role_unsubscribe_group',
            $now,
            $adminId,
            null,
            $wsAId,
            null,
            $managerRoleId,
            $grAId
        );
        $this->assertEquals(0, count($removeGroupFromManager));

        $addGroupToCollaboratorLogs = $this->logRepository->findActionAfterDate(
            'ws_role_subscribe_group',
            $now,
            $adminId,
            null,
            $wsAId,
            null,
            $collaboratorRoleId,
            $grAId
        );
        $this->assertEquals(1, count($addGroupToManagerLogs));
    }

    //4444444444444444444
    //+++++++++++++++++++/
    //+ TEST GROUP LIST +/
    //+++++++++++++++++++/

    public function testLimitedGroupList()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->loadGroupData(array('group_a' => array('user')));
        $this->logUser($this->getUser('user'));
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwuId}/groups/unregistered/page"
        );
        $this->assertEquals(1, count($crawler->filter('.row-group')));
    }

    public function testLimitedGroupListIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('user'));
        $pwaId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwaId}/groups/unregistered/page"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchUnregisteredGroupsByNameWithAjax()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->logUser($this->getUser('user'));
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwuId}/groups/unregistered/page/1/search/a"
        );
        $this->assertEquals(1, count($crawler->filter('.row-group')));
    }

    public function testSearchRegisteredGroupsByNameWithAjax()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getUser('ws_creator'));
        $wsAId = $this->getWorkspace('ws_a')->getId();
        $grAId = $this->getGroup('group_a')->getId();
        $this->client->request(
            'PUT',
            "/workspaces/tool/group_management/{$wsAId}/add/group?ids[]={$grAId}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/registered/page/1/search/a"
        );

        $this->assertEquals(1, count($crawler->filter('.row-group')));
    }

    private function addGroupAToWsA()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->getGroup('group_a')->addRole(
            $em->getRepository('ClarolineCoreBundle:Role')
                ->findCollaboratorRole($this->getWorkspace('ws_a'))
        );
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($this->getGroup('group_a'));
        $em->flush();
    }
}
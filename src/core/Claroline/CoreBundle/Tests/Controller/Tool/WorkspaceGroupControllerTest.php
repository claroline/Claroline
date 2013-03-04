<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceGroupControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRoleData();
    }

    public function testMultiAddGroup()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $groupAId = $this->getFixtureReference('group/group_a')->getId();
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request(
            'PUT',
            "/workspaces/tool/group_management/{$pwu}/add/group?ids[]={$groupAId}"
        );

        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($jsonResponse));
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwu}/groups/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testMultiAddGroupIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $pwu = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getFixtureReference('user/user_2'));
        $this->client->request(
            'PUT',
            "/workspaces/tool/group_management/{$pwu}/add/group?ids[]=1"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    //222222222222222222222222
    //+++++++++++++++++++++++/
    //+ TEST REMOVING GROUPS +/
    //+++++++++++++++++++++++/

    public function testMultiDeleteGroupFromWorkspace()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();

        $this->client->request(
            'GET', "/workspaces/tool/group_management/{$wsAId}/groups/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
        $grAId = $this->getFixtureReference('group/group_a')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/group_management/{$wsAId}/groups?ids[]={$grAId}"
        );
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(0, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testMultiDeleteGroupFromWorkspaceIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getFixtureReference('user/user'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $grAId = $this->getFixtureReference('group/group_a')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/group_management/{$wsAId}/groups?ids[]={$grAId}"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testMultiDeleteCantRemoveLastManager()
    {
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
        $this->logUser($this->getFixtureReference('user/admin'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $grAId = $this->getFixtureReference('group/group_a')->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($this->getFixtureReference('workspace/ws_a'))->getId()))
        );
        $wsCreatorId = $this->getFixtureReference('user/ws_creator')->getId();
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
    }

    //333333333333333333333333
    //+++++++++++++++++++++++/
    // TEST GROUP PROPERTIES +/
    //+++++++++++++++++++++++/

    public function testGroupPropertiesCanBeEdited()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $grAId = $this->getFixtureReference('group/group_a')->getId();
        $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($this->getFixtureReference('workspace/ws_a'))->getId()))
        );
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $groups = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($groups));
        $managerRole = $this->client->getContainer()
            ->get('translator')
            ->trans('manager', array(), 'platform');

        foreach ($groups as $group) {
            $this->assertContains($managerRole, $group->roles);
        }
    }

    public function testLastGroupManagerCantBeEdited()
    {
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
        $this->logUser($this->getFixtureReference('user/admin'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $grAId = $this->getFixtureReference('group/group_a')->getId();
        $crawler = $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($this->getFixtureReference('workspace/ws_a'))->getId()))
        );
        $wsCreatorId = $this->getFixtureReference('user/ws_creator')->getId();
        $this->client->request(
            'DELETE',
            "/workspaces/tool/user_management/{$wsAId}/users?ids[]={$wsCreatorId}"
        );
        $crawler = $this->client->request(
            'POST',
            "/workspaces/tool/group_management/{$wsAId}/group/{$grAId}",
            array('form' => array('role' => $em->getRepository('ClarolineCoreBundle:Role')
                ->findCollaboratorRole($this->getFixtureReference('workspace/ws_a'))->getId()))
        );
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, count($crawler->filter('html:contains("every managers")')));
    }

    //4444444444444444444
    //+++++++++++++++++++/
    //+ TEST GROUP LIST +/
    //+++++++++++++++++++/

    public function testLimitedGroupList()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->loadGroupData(array('group_a' => array('user')));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwuId}/groups/0/unregistered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
    }

    public function testLimitedGroupListIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwaId}/groups/0/unregistered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testPaginatedGroupsOfWorkspace()
    {
        $this->loadUserData(
            array(
                'user' => 'user',
                'user_2' => 'user',
                'ws_creator' => 'ws_creator'
            )
        );
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->addGroupAToWsA();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$wsAId}/groups/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testPaginatedGroupsOfWorkspaceIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwaId}/groups/0/registered",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchUnregisteredGroupsByNameWithAjax()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwuId = $this->getFixtureReference('user/user')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwuId}/group/search/a/unregistered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
    }

    public function testSearchUnregisteredGroupsByNameWithAjaxIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwaId}/group/search/a/unregistered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSearchRegisteredGroupsByNameWithAjax()
    {
        $this->loadUserData(array('user' => 'user', 'user_2' => 'user', 'ws_creator' => 'ws_creator'));
        $this->loadGroupData(array('group_a' => array('user', 'user_2')));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $wsAId = $this->getFixtureReference('workspace/ws_a')->getId();
        $grAId = $this->getFixtureReference('group/group_a')->getId();
        $this->client->request(
            'PUT',
            "/workspaces/tool/group_management/{$wsAId}/add/group?ids[]={$grAId}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->client->request(
            'GET', "/workspaces/tool/group_management/{$wsAId}/group/search/group/registered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $response = $this->client->getResponse()->getContent();
        $groups = json_decode($response);
        $this->assertEquals(1, count($groups));
    }

    public function testSearchRegisteredGroupsByNameWithAjaxIsProtected()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getFixtureReference('user/user'));
        $pwaId = $this->getFixtureReference('user/admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/tool/group_management/{$pwaId}/group/search/group/registered/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    private function addGroupAToWsA()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->getFixtureReference('group/group_a')->addRole(
            $em->getRepository('ClarolineCoreBundle:Role')
                ->findCollaboratorRole($this->getFixtureReference('workspace/ws_a'))
        );
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($this->getFixtureReference('group/group_a'));
        $em->flush();
    }
}
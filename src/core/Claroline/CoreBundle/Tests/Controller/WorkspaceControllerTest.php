<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceControllerTest extends FunctionalTestCase
{
    private $logRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function testWSCreatorcanViewHisWorkspaces()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $crawler = $this->logUser($this->getUser('ws_creator'));
        $link = $crawler->filter('#link-my-workspaces')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(2, $crawler->filter('.row-workspace')->count());
    }

    public function testAdmincanViewHisWorkspaces()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->loadWorkspaceData(array('ws_e' => 'admin'));
        $crawler = $this->logUser($this->getUser('admin'));
        $link = $crawler->filter('#link-my-workspaces')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(2, $crawler->filter('.row-workspace')->count());
    }

    public function testWSCreatorCanCreateWS()
    {
        $now = new \DateTime();

        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $crawler = $this->logUser($this->getUser('ws_creator'));
        $link = $crawler->filter('#link-create-ws-form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_form[name]'] = 'new_workspace';
        $form['workspace_form[type]'] = 'simple';
        $form['workspace_form[code]'] = 'code';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/workspaces");
        $this->assertEquals(1, $crawler->filter('.row-workspace')->count());

        $logs = $this->logRepository->findActionAfterDate(
            'workspace_create',
            $now,
            $this->getUser('ws_creator')->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    /**
     * @group debug
     */
    public function testWSCreatorCanDeleteHisWS()
    {
        // $now = new \DateTime();

        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(
            array(
                'ws_a' => 'ws_creator',
                'ws_b' => 'ws_creator',
                'ws_c' => 'ws_creator',
                'ws_d' => 'ws_creator',
            )
        );

        $this->logUser($this->getUser('ws_creator'));

        $crawler = $this->client->request(
            'DELETE',
            "/workspaces/{$this->getWorkspace('ws_d')->getId()}"
        );
        $crawler = $this->client->request(
            'GET',
            "/workspaces/user"
        );

        $this->assertEquals(4, $crawler->filter('.row-workspace')->count());

        // $logs = $this->logRepository->findActionAfterDate(
        //     'workspace_delete',
        //     $now,
        //     $this->getUser('ws_creator')->getId()
        // );
        // $this->assertEquals(1, count($logs));
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
        $this->logUser($this->getUser('ws_creator'));
        $crawler = $this->client->request(
            'GET',
            "/workspaces"
        );
        $link = $crawler->filter("#link-home-{$this->getWorkspace('ws_d')->getId()}")
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
        $this->logUser($this->getUser('user'));
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
        $this->logUser($this->getUser('user'));
        $pwuId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwuId}/open/tool/resource_manager"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayHome()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $pwsId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwsId}/open/tool/home"
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredHome()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('user'));
        $pwaId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $this->client->request(
            'GET',
            "/workspaces/{$pwaId}/open/tool/home"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUserManagement()
    {
        $this->loadUserData(array('user' => 'user'));
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/workspaces/{$pwuId}/open/tool/user_management");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserManagement()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $pwaId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/workspaces/{$pwaId}/open/tool/user_management");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayUserParameters()
    {
        $this->loadUserData(array('user' => 'user'));
        $pwuId = $this->getUser('user')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'GET',
            "/workspaces/tool/user_management/{$pwuId}/user/{$this->getUser('user')->getId()}"
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCantAccessUnregisteredUserParameters()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $pwaId = $this->getUser('admin')->getPersonalWorkspace()->getId();
        $userId = $this->getUser('user')->getId();
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/workspaces/tool/user_management/{$pwaId}/user/{$userId}");
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

    public function testCreateWorkspaceGrantAccessToWorkspace()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $link = $crawler->filter('#link-create-ws-form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_form[name]'] = 'first_new_workspace';
        $form['workspace_form[type]'] = 'simple';
        $form['workspace_form[code]'] = 'a_code';
        $this->client->submit($form);
        $ws = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->findOneByName('first_new_workspace');
        $this->client->request('GET', "/workspaces/{$ws->getId()}/open/tool/home");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCanCreateWorkspaceTag()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', '/workspaces/tag/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_tag_form[name]'] = 'tag 2';
        $this->client->submit($form);
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser($this->getUser('user'));
        $this->assertEquals(2, count($tags));
    }

    public function testUserCantCreateTwoWorkspaceTagsWithSameName()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', '/workspaces/tag/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser($this->getUser('user'));
        $this->assertEquals(1, count($tags));
        $crawler = $this->client->request('GET', '/workspaces/tag/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser($this->getUser('user'));
        $this->assertEquals(1, count($tags));
    }

    public function testUserCanAssociateAndRemoveTagToWorkspace()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $pws = $this->getUser('user')->getPersonalWorkspace();
        $userId = $this->getUser('user')->getId();
        $workspaceId = $pws->getId();
        $crawler = $this->client->request("GET", "/workspaces/tag/createform");
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);

        $tag = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('user' => $userId, 'name' => 'tag'));

        $this->client->request(
            "POST",
            "/workspaces/{$userId}/workspace/{$workspaceId}/tag/add/{$tag->getName()}"
        );
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findByWorkspaceAndUser($pws, $this->getUser('user'));
        $this->assertEquals(1, count($relWsTag));

        $this->client->request(
            "DELETE",
            "/workspaces/{$userId}/workspace/{$workspaceId}/tag/remove/{$tag->getId()}"
        );
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findByWorkspaceAndUser($pws, $this->getUser('user'));
        $this->assertEquals(0, count($relWsTag));
    }

    public function testUserCanAssociateInexistingTagToWorkspace()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $pws = $this->getUser('user')->getPersonalWorkspace();
        $userId = $this->getUser('user')->getId();
        $workspaceId = $pws->getId();

        $this->client->request(
            "POST",
            "/workspaces/{$userId}/workspace/{$workspaceId}/tag/add/tag"
        );
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser($this->getUser('user'));
        $this->assertEquals(1, count($tags));
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneByWorkspaceAndTagAndUser($pws, $tags[0], $this->getUser('user'));
        $this->assertNotNull($relWsTag);
    }

    public function testAdminCanCreateWorkspaceTag()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request('GET', '/workspaces/tag/admin/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['admin_workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['admin_workspace_tag_form[name]'] = 'tag 2';
        $this->client->submit($form);
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);
        $this->assertEquals(2, count($tags));
    }

    public function testAdminCantCreateTwoWorkspaceTagsWithSameName()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request('GET', '/workspaces/tag/admin/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['admin_workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);
        $this->assertEquals(1, count($tags));
        $crawler = $this->client->request('GET', '/workspaces/tag/admin/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['admin_workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);
        $this->assertEquals(1, count($tags));
    }

    public function testAdminCanAssociateAndRemoveTagToWorkspace()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $pws = $this->getUser('admin')->getPersonalWorkspace();
        $workspaceId = $pws->getId();
        $crawler = $this->client->request("GET", "/workspaces/tag/admin/createform");
        $form = $crawler->filter('button[type=submit]')->form();
        $form['admin_workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);

        $tag = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('user' => null, 'name' => 'tag'));

        $this->client->request(
            "POST",
            "/workspaces/workspace/{$workspaceId}/tag/add/{$tag->getName()}"
        );
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findAdminByWorkspace($pws);
        $this->assertEquals(1, count($relWsTag));

        $this->client->request(
            "DELETE",
            "/workspaces/workspace/{$workspaceId}/tag/remove/{$tag->getId()}"
        );
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findAdminByWorkspace($pws);
        $this->assertEquals(0, count($relWsTag));
    }

    public function testAdminCanAssociateInexistingTagToWorkspace()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $pws = $this->getUser('admin')->getPersonalWorkspace();
        $workspaceId = $pws->getId();

        $this->client->request(
            "POST",
            "/workspaces/workspace/{$workspaceId}/tag/add/tag"
        );
        $tags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);
        $this->assertEquals(1, count($tags));
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($pws, $tags[0]);
        $this->assertNotNull($relWsTag);
    }

    public function testUserAndAdminCanCreateTagWithTheSameName()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $userId = $this->getUser('admin')->getId();
        $pws = $this->getUser('admin')->getPersonalWorkspace();
        $workspaceId = $pws->getId();

        $this->client->request(
            "POST",
            "/workspaces/{$userId}/workspace/{$workspaceId}/tag/add/tag"
        );
        $userTags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser($this->getUser('admin'));
        $this->assertEquals(1, count($userTags));
        $userRelWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneByWorkspaceAndTagAndUser($pws, $userTags[0], $this->getUser('admin'));
        $this->assertNotNull($userRelWsTag);

        $this->client->request(
            "POST",
            "/workspaces/workspace/{$workspaceId}/tag/add/tag"
        );
        $adminTags = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser(null);
        $this->assertEquals(1, count($adminTags));
        $adminRelWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($pws, $adminTags[0]);
        $this->assertNotNull($adminRelWsTag);

        $allRelWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findAllByWorkspaceAndUser($pws, $this->getUser('admin'));
        $this->assertEquals(2, count($allRelWsTag));
    }

    public function testNonAdminCantAccessCreateAdminTagForm()
    {
        $this->setExpectedException('Exception');
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', '/workspaces/tag/admin/createform');
        $crawler->filter('button[type=submit]')->form();
    }

    public function testNonAdminCantAssociateAdminTagToWorkspace()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request('GET', '/workspaces/tag/admin/createform');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['admin_workspace_tag_form[name]'] = 'tag';
        $this->client->submit($form);
        $tag = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('user' => null, 'name' => 'tag'));
        $this->assertNotNull($tag);

        $this->logUser($this->getUser('user'));
        $pws = $this->getUser('user')->getPersonalWorkspace();
        $this->client->request(
            "POST",
            "/workspaces/workspace/{$pws->getId()}/tag/add/{$tag->getName()}"
        );
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($pws, $tag);
        $this->assertNull($relWsTag);
    }

    public function testNonAdminCantRemoveAdminTagFromWorkspace()
    {
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $pws = $this->getUser('user')->getPersonalWorkspace();
        $this->client->request(
            "POST",
            "/workspaces/workspace/{$pws->getId()}/tag/add/tag"
        );
        $tag = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findOneBy(array('user' => null, 'name' => 'tag'));
        $this->assertNotNull($tag);

        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($pws, $tag);
        $this->assertNotNull($relWsTag);

        $this->logUser($this->getUser('user'));
        $this->client->request(
            "DELETE",
            "/workspaces/workspace/{$pws->getId()}/tag/remove/{$tag->getId()}"
        );
        $relWsTag = $this->em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findOneAdminByWorkspaceAndTag($pws, $tag);
        $this->assertNotNull($relWsTag);
    }
}

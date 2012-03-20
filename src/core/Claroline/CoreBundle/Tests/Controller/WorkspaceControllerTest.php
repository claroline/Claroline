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
         $link = $crawler->filter('#link_workspace')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_owned_WS')->link();
         $crawler = $this->client->click($link);   
         $this->assertEquals(4, $crawler->filter('.row_workspace')->count()); 
    }
    
    public function testAdminCanSeeHisWs()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));         
         $link = $crawler->filter('#link_workspace')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_owned_WS')->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(2, $crawler->filter('.row_workspace')->count());
    }
    
    public function testWSCreatorCanCreateWS()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
         $link = $crawler->filter('#link_workspace')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_create_WS_form')->link();
         $crawler = $this->client->click($link); 
         $form = $crawler->filter('input[type=submit]')->form(); 
         $form['workspace_form[name]'] = 'new_workspace';
         $form['workspace_form[type]'] = 'simple';
         $this->client->submit($form);
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $this->assertEquals(5, $crawler->filter('.row_workspace')->count()); 
    }
    
    public function testWSCreatorCanDeleteHisWS()
    {
         $this->logUser($this->getFixtureReference('user/ws_creator'));  
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $link = $crawler->filter("#link_delete_{$this->getFixtureReference('workspace/ws_d')->getId()}")->link();
         $crawler = $this->client->click($link);
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $this->assertEquals(3, $crawler->filter('.row_workspace')->count());          
    }
    
    public function testWSManagerCanSeeHisWS()
    {
         $this->logUser($this->getFixtureReference('user/ws_creator'));  
         $crawler = $this->client->request('GET', "/workspace/list/{$this->getFixtureReference('user/ws_creator')->getId()}");
         $link = $crawler->filter("#link_show_{$this->getFixtureReference('workspace/ws_d')->getId()}")->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(1, $crawler->filter("#div_WS_show")->count());
    }
    
    public function testUserCanSeeWSList()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/workspace/list");
        $this->assertEquals(6, $crawler->filter('.row_workspace')->count()); 
    }
       
    public function testDeleteUserFromWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/admin')); 
        $crawler = $this->client->request('GET', "workspace/show/list/user/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter(".row_user")->count());
        $link = $crawler->filter("#link_delete_user_{$this->getFixtureReference('user/ws_creator')->getId()}")->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(0, $crawler->filter(".row_user")->count());  
    }
    
    public function testDeleteGroupFromWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin')); 
        $crawler = $this->client->request('GET', "workspace/show/list/user/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter(".row_group")->count());
        $link = $crawler->filter("#link_delete_group_{$this->getFixtureReference('group/manyGroup1')->getId()}")->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(0, $crawler->filter(".row_group")->count());  
    }
 
    public function testAJAXControllerGetAddUsers()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/get/add/user/{$this->getFixtureReference('workspace/ws_a')->getId()}/1",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(25, $crawler->filter(".checkbox_user_name")->count());
        $this->assertEquals(1, $crawler->filter("#checkbox_user_{$this->getFixtureReference('user/manyUser28')->getId()}")->count());
    }
    
    public function testAJAXControllerAddUserToWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator')); 
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/add/user/{$this->getFixtureReference('user/user')->getId()}/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(1, $crawler->filter("#user_{$this->getFixtureReference('user/user')->getId()}")->count());
        $crawler = $this->client->request('GET', "/workspace/show/list/user/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(2, $crawler->filter(".row_user")->count());
    }
    
    public function testAJAXControllerDeleteUserFromWorkspace()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'POST', 
            "/workspace/ajax/delete/user/{$this->getFixtureReference('user/ws_creator')->getId()}/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        ); 
        $this->assertEquals("success", $this->client->getResponse()->getContent());
        $crawler = $this->client->request('GET', "workspace/show/list/user/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(0, $crawler->filter(".row_user")->count()); 
    }
    
    public function testAJAXControllerGetGenericSearchUnregisteredUser()
    {
        $this->loadFixture(new LoadManyUsersData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/search/user/doe/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(4, $crawler->filter(".checkbox_user_name")->count()); 
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/search/user/admin/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(1, $crawler->filter(".checkbox_user_name")->count());     
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/search/user/firstname/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(125, $crawler->filter(".checkbox_user_name")->count());         
    }
    
    public function testAJAXControllerGetGenericSearchUnregisteredGroup()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/search/group/a/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(1, $crawler->filter(".checkbox_group_name")->count());
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/search/group/group/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(3, $crawler->filter(".checkbox_group_name")->count()); 
    }
    
    public function testAJAXControllerGetAddGroups()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/get/add/group/{$this->getFixtureReference('workspace/ws_a')->getId()}/0",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $this->assertEquals(10, $crawler->filter(".checkbox_group_name")->count());        
    }
    
    public function testAJAXControllerAddGroupToWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadGroupData);
        $this->logUser($this->getFixtureReference('user/ws_creator')); 
        $crawler = $this->client->request(
            'POST', 
            "/workspace/ajax/add/group/{$this->getFixtureReference('group/group_a')->getId()}/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );  
        $this->assertEquals(1, $crawler->filter("#group_{$this->getFixtureReference('group/group_a')->getId()}")->count());
        $crawler = $this->client->request('GET', "/workspace/show/list/user/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(1, $crawler->filter(".row_group")->count());
    }
    
    public function testAJAXControllerDeleteGroupFromWorkspace()
    {
        $this->loadFixture(new LoadRoleData());
        $this->loadFixture(new LoadManyUsersData());
        $this->loadFixture(new LoadManyGroupsData());        
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'POST', 
            "/workspace/ajax/delete/group/{$this->getFixtureReference('group/manyGroup1')->getId()}/{$this->getFixtureReference('workspace/ws_a')->getId()}",
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        ); 
        $this->assertEquals("success", $this->client->getResponse()->getContent());
        $crawler = $this->client->request('GET', "workspace/show/list/user/{$this->getFixtureReference('workspace/ws_a')->getId()}");
        $this->assertEquals(0, $crawler->filter(".row_group")->count()); 
    }
}
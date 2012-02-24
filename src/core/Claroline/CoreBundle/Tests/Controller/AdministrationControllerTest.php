<?php
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AdministrationControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadGroupFixture();
        $this->client->followRedirects();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testAdminCanSeeGroups()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));
         $link = $crawler->filter('a:contains("Administration")')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_group_list')->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(3, $crawler->filter('.row_group')->count());    
    }
    
    public function testAdminCanSeeUsersFromGroup()
    {
          $crawler = $this->logUser($this->getFixtureReference('user/admin'));
          $crawler = $this->client->request('GET', '/admin/group/list');
          $link = $crawler->filter("#link_show_{$this->getFixtureReference('group/group_a')->getId()}")->link();
          $crawler = $this->client->click($link);
          $this->assertEquals(2, $crawler->filter('.row_user')->count());
    }
    
    public function testAdminCanCreateGroups()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));
         $link = $crawler->filter('a:contains("Administration")')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_group_create_form')->link();
         $crawler = $this->client->click($link);
         $form = $crawler->filter('input[type=submit]')->form();
         $form['group_form[name]'] = 'Group D';
         $this->client->submit($form);
         $crawler = $this->client->request('GET', '/admin/group/list');
         $this->assertEquals(4, $crawler->filter('.row_group')->count()); 
    }
    
    public function testAdminCanAddUserToGroup()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));
         $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
         $link = $crawler->filter('#link_add_user_to_group')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter("#link_add_user_{$this->getFixtureReference('user/admin')->getId()}")->link();
         $this->client->click($link);
         $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
         $this->assertEquals(3, $crawler->filter('.row_user')->count());
    }
    
    public function testAdminCanRemoveUserFromGroup()
    {
          $crawler = $this->logUser($this->getFixtureReference('user/admin'));
          $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
          $link = $crawler->filter("#link_delete_{$this->getFixtureReference('user/user')->getId()}")->link();
          $crawler = $this->client->click($link);
          $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
          $this->assertEquals(1, $crawler->filter('.row_user')->count());
    }
    
    public function testAdminCanDeleteGroup()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));
         $crawler = $this->client->request('GET', '/admin/group/list');
         $link = $crawler->filter("#link_delete_{$this->getFixtureReference('group/group_a')->getId()}")->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(2, $crawler->filter('.row_group')->count()); 
    }
    
    public function testOnlyAdminCanMangeGroup()
    {
          $crawler = $this->logUser($this->getFixtureReference('user/user'));
          $crawler = $this->client->request('GET', '/admin/group/list');
          $this->assertEquals(403, $this->client->getResponse()->getStatusCode());; 
    }
}
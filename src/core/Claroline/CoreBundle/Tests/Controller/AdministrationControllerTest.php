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
         $link = $crawler->filter('#link_administration')->link();
         $crawler = $this->client->click($link);
         $link = $crawler->filter('#link_group_list')->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(3, $crawler->filter('.row_group')->count());    
    }
    
    public function testAdminCanSeeUsersFromGroup()
    {
          $this->logUser($this->getFixtureReference('user/admin'));
          $crawler = $this->client->request('GET', '/admin/group/list');
          $link = $crawler->filter("#link_show_{$this->getFixtureReference('group/group_a')->getId()}")->link();
          $crawler = $this->client->click($link);
          $this->assertEquals(2, $crawler->filter('.row_user')->count());
    }
    
    public function testAdminCanCreateGroups()
    {
         $crawler = $this->logUser($this->getFixtureReference('user/admin'));
         $link = $crawler->filter('#link_administration')->link();
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
         $this->logUser($this->getFixtureReference('user/admin'));
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
          $this->logUser($this->getFixtureReference('user/admin'));
          $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
          $link = $crawler->filter("#link_delete_{$this->getFixtureReference('user/user')->getId()}")->link();
          $this->client->click($link);
          $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
          $this->assertEquals(1, $crawler->filter('.row_user')->count());
    }
    
    public function testAdminCanDeleteGroup()
    {
         $this->logUser($this->getFixtureReference('user/admin'));
         $crawler = $this->client->request('GET', '/admin/group/list');
         $link = $crawler->filter("#link_delete_{$this->getFixtureReference('group/group_a')->getId()}")->link();
         $crawler = $this->client->click($link);
         $this->assertEquals(2, $crawler->filter('.row_group')->count()); 
    }
    
    public function testAdminCanEditGroupSettings()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/group/list');
        $link = $crawler->filter("#link_settings_{$this->getFixtureReference('group/group_a')->getId()}")->link();
        $crawler = $this->client->click($link);
        $selected = $crawler->filterXpath("//select/option[. = 'ROLE_A']")->attr('selected');
        $this->assertEquals("selected", $selected);
        $form = $crawler->filter('input[type=submit]')->form();      
        $form['group_form[ownedRoles]'] = $this->getFixtureReference('role/admin')->getId();
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/admin/group/settings/form/{$this->getFixtureReference('group/group_a')->getId()}");
        $selected = $crawler->filter('option:contains("ROLE_ADMIN")')->attr('selected');
        $this->assertEquals("selected", $selected);
    }
    
    public function testOnlyAdminCanManageGroup()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/admin/group/list');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());;
    }
    
    public function testEditClaroSelfRegistration()
    {
        $this->setPlatformTestOptions();
        $this->assertEquals(true, static::$kernel->getContainer()->get('claroline.config.platform_config_handler')->getParameter('allow_self_registration'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin');
        $link = $crawler->filter("#link_claro_settings")->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('input[type=submit]')->form();
        $form['claro_settings_form[selfRegistration]'] = false;
        $this->client->submit($form);
        $this->client->request('GET', '/logout');
        $this->assertEquals(false, static::$kernel->getContainer()->get('claroline.config.platform_config_handler')->getParameter('allow_self_registration'));
        $this->assertEquals(0, $crawler->filter("#link_registration")->count());
    }
    
    public function testEditClaroLanguage()
    {
        $this->setPlatformTestOptions();
        $this->assertEquals('en', static::$kernel->getContainer()->get('claroline.config.platform_config_handler')->getParameter('locale_language'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/claronext/settings/form');
        $this->assertEquals(1, $crawler->filter("a:contains('Logout')")->count());
        $form = $crawler->filter('input[type=submit]')->form();
        $form['claro_settings_form[localLanguage]'] = 'fr';
        $crawler = $this->client->submit($form);
        $this->assertEquals(1, $crawler->filter("#link_logout")->count());
        $crawler = $this->client->request('GET', '/logout');
        $this->assertEquals(1, $crawler->filter("#link_login")->count());
    }
    
    public function setPlatformTestOptions()
    {
        static::$kernel->getContainer()->get('claroline.config.platform_config_handler')->setParameter('allow_self_registration', true);
        static::$kernel->getContainer()->get('claroline.config.platform_config_handler')->setParameter('locale_language', 'en');
    }
}
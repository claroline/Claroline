<?php

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class UserControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testUserProfileEdit()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'user/profile/edit');
        $form = $crawler->filter('input[type=submit]')->form();
        $readonly = ($crawler->filter('#user_form_ownedRoles')->attr('disabled'));
        $selected = $crawler->filter('option:contains("ROLE_USER")')->attr('selected');
        $this->assertEquals("selected", $selected);
        $crawler = $this->client->submit(
            $form, array('user_form[firstName]' => 'toto', 'user_form[plainPassword][first]' =>
            'abc', 'user_form[plainPassword][second]' => 'abc'));
        $username = $crawler->filter('#username')->text();
        $this->assertEquals("toto Doe", $username);
        $this->assertEquals("disabled", $readonly);
        $this->client->request('GET', '/logout');
        $this->getFixtureReference('user/user')->setPlainPassword('abc');
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
    }

    public function testUnregisteredProfileEdit()
    {
        $crawler = $this->client->request('GET', 'user/profile/edit');
        $this->assertEquals(1, $crawler->filter('#login_form')->count());
    }

    public function testAdminOwnProfileEdit()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', 'user/profile/edit');
        $form = $crawler->filter('input[type=submit]')->form();
        $selected = $crawler->filter('option:contains("ROLE_ADMIN")')->attr('selected');
        $this->assertEquals("selected", $selected);
        $readonly = ($crawler->filter('#user_form_ownedRoles')->attr('disabled'));
        $this->assertEquals("", $readonly);
        $form['user_form[firstName]'] = 'toto';
        $form['user_form[plainPassword][first]'] = 'abc';
        $form['user_form[plainPassword][second]'] = 'abc';
        $form['user_form[ownedRoles]'] = $this->getFixtureReference('role/user')->getId();
        $this->client->submit($form);
        $this->getFixtureReference('user/admin')->setPlainPassword('abc');
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $username = $crawler->filter('')->text();
        $this->assertEquals("toto Doe", $username);
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Administration")')->count());
    }

    public function testAdminCanGetFullUserList()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('a:contains("Administration")')->link();
        $crawler = $this->client->click($link);
        $crawler = $link = $crawler->filter('#link_list_user')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(9, $crawler->filter('.headerX')->children()->count());
        $this->assertEquals(5, $crawler->filter('.row_user')->count());
    }
    
    public function testUserCanGetFullUserList()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'admin/list/user');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode()); 
    }
    public function testUnregisteredCanGetFullUserList()
    {
        $crawler = $this->client->request('GET', 'admin/list/user');
        $this->assertEquals(1, $crawler->filter('#login_form')->count());
    }
    
    public function testWorkspaceCreatorCanGetFullUserList ()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('GET', 'admin/list/user');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminCanDeleteFromList()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', 'admin/list/user');
        $link = $crawler->filter('a:contains("delete")')->eq(0)->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(4, $crawler->filter('.row_user')->count());
    }
    
    public function testUserCanDeleteUsers()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request(
            'GET', "/user/profile/delete/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testWorskpaceCreatorCanDeleteUsers()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request(
            'GET', "/user/profile/delete/{$this->getFixtureReference('user/user')->getId()}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testUnregisteredCanDeleteUsers()
    {
        $crawler = $this->client->request(
            'GET', "/user/profile/delete/{$this->getFixtureReference('user/ws_creator')->getId()}");
        $this->assertEquals(1, $crawler->filter('#login_form')->count());    
    }
    
    public function testAdminCanAddUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('a:contains("Administration")')->link();
        $crawler = $this->client->click($link);
        $crawler = $link = $crawler->filter('#link_add_user')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('input[type=submit]')->form();
        $form['user_form[firstName]'] = 'toto';
        $form['user_form[lastName]'] = 'tata';
        $form['user_form[username]'] = 'tototata';
        $form['user_form[plainPassword][first]'] = 'abc';
        $form['user_form[plainPassword][second]'] = 'abc';
        $form['user_form[ownedRoles]'] = $this->getFixtureReference('role/user')->getId();
        $crawler = $this->client->submit($form);
        $this->assertEquals(6, $crawler->filter('.row_user')->count());
    }
    
    public function testUserCanAddUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'admin/add/user/form');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());; 
    }
    
    public function testWorkspaceCreatorCanAddUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('GET', 'admin/add/user/form');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());; 
    }
    
    public function testUnregisteredCanAddUser()
    {
        $crawler = $this->client->request('GET', 'admin/add/user/form');
        $this->assertEquals(1, $crawler->filter('#login_form')->count());
    }
}
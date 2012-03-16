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

    public function testUserProfileEdit()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'user/profile/edit');
        $form = $crawler->filter('input[type=submit]')->form();
        $readonly = $crawler->filter('#user_form_ownedRoles')->attr('disabled');
        $selected = $crawler->filter('option:contains("ROLE_USER")')->attr('selected');
        $this->assertEquals("selected", $selected);
        $crawler = $this->client->submit(
            $form, 
            array(
                'user_form[firstName]' => 'toto', 
                'user_form[plainPassword][first]' => 'abc', 
                'user_form[plainPassword][second]' => 'abc'
            )
        );
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
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', 'user/profile/edit');
        $form = $crawler->filter('input[type=submit]')->form();
        $selected = $crawler->filter('option:contains("ROLE_ADMIN")')->attr('selected');
        $this->assertEquals("selected", $selected);
        $readonly = $crawler->filter('#user_form_ownedRoles')->attr('disabled');
        $this->assertEquals("", $readonly);
        $form['user_form[firstName]'] = 'toto';
        $form['user_form[plainPassword][first]'] = 'abc';
        $form['user_form[plainPassword][second]'] = 'abc';
        $form['user_form[ownedRoles]'] = $this->getFixtureReference('role/user')->getId();
        $this->client->submit($form);
        $this->getFixtureReference('user/admin')->setPlainPassword('abc');
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $username = $crawler->filter('#username')->text();
        $this->assertEquals("toto Doe", $username);
        $this->assertEquals(0, $crawler->filter('#login_form .failure_msg')->count());
        $this->assertEquals(0, $crawler->filter('#link_administration')->count());
    }

    public function testAdminCanGetFullUserList()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link_administration')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter('#link_list_user')->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(9, $crawler->filter('.headerX')->children()->count());
        $this->assertEquals(5, $crawler->filter('.row_user')->count());
    }
    
    public function testUserCantGetFullUserList()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', 'admin/list/user');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode()); 
    }
    public function testUnregisteredCantGetFullUserList()
    {
        $crawler = $this->client->request('GET', 'admin/list/user');
        $this->assertEquals(1, $crawler->filter('#login_form')->count());
    }
    
    public function testWorkspaceCreatorCantGetFullUserList ()
    {
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('GET', 'admin/list/user');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminCanDeleteFromList()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', 'admin/list/user');
        $link = $crawler->filter('.link_delete')->eq(0)->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(4, $crawler->filter('.row_user')->count());
    }
    
    public function testUserCantDeleteUsers()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/user'));
        $creatorUserId = $this->getFixtureReference('user/ws_creator')->getId();
        $crawler = $this->client->request('GET', "/user/profile/delete/{$creatorUserId}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testWorskpaceCreatorCantDeleteUsers()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/ws_creator'));
        $userId = $this->getFixtureReference('user/user')->getId();
        $crawler = $this->client->request('GET', "/user/profile/delete/{$userId}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testUnregisteredCantDeleteUsers()
    {
        $creatorUserId = $this->getFixtureReference('user/ws_creator')->getId();
        $crawler = $this->client->request('GET', "/user/profile/delete/{$creatorUserId}");
        $this->assertEquals(1, $crawler->filter('#login_form')->count());    
    }
    
    public function testAdminCanAddUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link_administration')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter('#link_add_user')->link();
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
}
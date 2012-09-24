<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AdministrationControllerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Library\Testing\PlatformTestConfigurationHandler */
    private $configHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadGroupFixture();
        $this->configHandler = $this->client
            ->getContainer()
            ->get('claroline.config.platform_config_handler');
        $this->client->followRedirects();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->configHandler->eraseTestConfiguration();
    }

    public function testAdminCanSeeGroups()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(3, $crawler->filter('.row-group')->count());
    }

    public function testAdminCanSeeUsersFromGroup()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users/1");
        $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testAdminCanCreateUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link-administration')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter('#link_add_user')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['profile_form[firstName]'] = 'toto';
        $form['profile_form[lastName]'] = 'tata';
        $form['profile_form[username]'] = 'tototata';
        $form['profile_form[plainPassword][first]'] = 'abc';
        $form['profile_form[plainPassword][second]'] = 'abc';
        $form['profile_form[ownedRoles]'] = $this->getFixtureReference('role/user')->getId();
        $this->client->submit($form);
        $user = $this->getUser('tototata');
        $repositoryWs = $user->getPersonalWorkspace();
        $this->assertEquals(1, count($repositoryWs));
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(6, $crawler->filter('.row-user')->count());
    }

    public function testUserCreationFormIsDisplayedWithErrors()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('POST', '/admin/user');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['profile_form[firstName]'] = '';
        $crawler = $this->client->submit($form);
        $this->assertEquals(1, count($crawler->filter('#profile_form')));
    }

    public function testAdminCanDeleteUser()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(5, $crawler->filter('.row-user')->count());
        $this->client->request('DELETE', "/admin/user/{$this->getFixtureReference('user/user')->getId()}");
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(4, $crawler->filter('.row-user')->count());
    }

    public function testAdminCannotDeleteHimself()
    {
        $admin = $this->getFixtureReference('user/admin');
        $crawler = $this->logUser($admin);
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(5, $crawler->filter('.row-user')->count());
        $this->assertEquals(0, count($crawler->filter('.link-delete-user')->eq(4)));
        $this->client->request('DELETE', "/admin/user/{$admin->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    public function testUserCannotDeleteHimself()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('DELETE', "/admin/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 403);
    }

    public function testAdminCanCreateGroups()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link-administration')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter('#link_group_create_form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['group_form[name]'] = 'Group D';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(4, $crawler->filter('.row-group')->count());
    }

    public function testGroupCreationFormIsDisplayedWithErrors()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/group/form');
        $form = $crawler->filter('button[type=submit]')->form();
        $this->client->submit($form);
        $this->assertEquals(1, count($crawler->filter('#group_form')));
    }

    public function testAdminCanAddUserToGroup()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
        $link = $crawler->filter('#link_add_user_to_group')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter("#link_add_user_{$this->getFixtureReference('user/admin')->getId()}")->link();
        $this->client->click($link);
        $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users/1");
        $this->assertEquals(3, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testAdminCanRemoveUserFromGroup()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('DELETE', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users/1");
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testAdminCanDeleteGroup()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('DELETE', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(2, $crawler->filter('.row-group')->count());
    }

    public function testMultiDeleteGroups()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('DELETE', "/admin/groups?0={$this->getFixtureReference('group/group_a')->getId()}");
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(2, $crawler->filter('.row-group')->count());
    }

    public function testAdminCanEditGroupSettings()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', "/admin/group/settings/form/{$this->getFixtureReference('group/group_a')->getId()}");
        $selected = $crawler->filterXpath("//select/option[. = 'ROLE_A']")->attr('selected');
        $this->assertEquals("selected", $selected);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['group_form[ownedRoles]'] = $this->getFixtureReference('role/admin')->getId();
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/admin/group/settings/form/{$this->getFixtureReference('group/group_a')->getId()}");
        $selected = $crawler->filter('option:contains("ROLE_ADMIN")')->attr('selected');
        $this->assertEquals("selected", $selected);
    }

    public function testGroupSettingsFormWithErrorsIsRendered()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', "/admin/group/settings/form/{$this->getFixtureReference('group/group_a')->getId()}");
        $form = $crawler->filter('button[type=submit]')->form();
        $form['group_form[name]'] = '';
        $crawler = $this->client->submit($form);
        $this->assertEquals(1, count($crawler->filter('#group_form')));
    }

    public function testEditSelfRegistrationParameter()
    {
        $this->configHandler->setParameter('allow_self_registration', false);
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(0, $crawler->filter("#link-registration")->count());

        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin');
        $link = $crawler->filter("#link_platform_parameters")->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['platform_parameters_form[selfRegistration]'] = true;
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/logout');
        $this->assertEquals(1, $crawler->filter("#link-registration")->count());
    }

    public function testEditLanguageParameter()
    {
        $this->configHandler->setParameter('locale_language', 'en');
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $this->assertEquals('Logout', trim($crawler->filter("#link-logout")->text()));

        $crawler = $this->client->request('GET', '/admin/platform/settings/form');
        $form = $crawler->filter('button[type=submit]')->form();
        $form['platform_parameters_form[localLanguage]'] = 'fr';
        $crawler = $this->client->submit($form);

        $this->assertEquals('Déconnexion', trim($crawler->filter("#link-logout")->text()));
    }

    private function getUser($username)
    {
        $user = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneByUsername($username);

        return $user;
    }
}
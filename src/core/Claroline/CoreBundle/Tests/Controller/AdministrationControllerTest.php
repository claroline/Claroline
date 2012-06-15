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

    public function testAdminCanCreateUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link_administration')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter('#link_add_user')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('input[type=submit]')->form();
        $form['profile_form[firstName]'] = 'toto';
        $form['profile_form[lastName]'] = 'tata';
        $form['profile_form[username]'] = 'tototata';
        $form['profile_form[plainPassword][first]'] = 'abc';
        $form['profile_form[plainPassword][second]'] = 'abc';
        $form['profile_form[ownedRoles]'] = $this->getFixtureReference('role/user')->getId();
        $crawler = $this->client->submit($form);
        $user = $this->getUser('tototata');
        $repositoryWs = $user->getPersonnalWorkspace();
        $this->assertEquals(1, count($repositoryWs));
        $this->assertEquals(6, $crawler->filter('.row_user')->count());
    }

    public function testAdminCanDeleteUser()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link_administration')->link();
        $crawler = $this->client->request('GET', 'admin/user/list');
        $this->assertEquals(5, $crawler->filter('.row_user')->count());
        $link = $crawler->filter('.link_delete')->eq(0)->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(4, $crawler->filter('.row_user')->count());
    }

    public function testAdminCannotDeleteHimself()
    {
        $admin = $this->getFixtureReference('user/admin');
        $crawler = $this->logUser($admin);
        $crawler = $this->client->request('GET', 'admin/user/list');
        $this->assertEquals(5, $crawler->filter('.row_user')->count());
        $this->assertEquals(0, count($crawler->filter('.link_delete')->eq(4)));
        $this->client->request('GET', "/admin/user/delete/{$admin->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
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

    public function testEditSelfRegistrationParameter()
    {
        $this->configHandler->setParameter('allow_self_registration', false);
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(0, $crawler->filter("#link_registration")->count());

        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin');
        $link = $crawler->filter("#link_claro_settings")->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('input[type=submit]')->form();
        $form['claro_settings_form[selfRegistration]'] = true;
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/logout');
        $this->assertEquals(1, $crawler->filter("#link_registration")->count());
    }

    public function testEditLanguageParameter()
    {
        $this->configHandler->setParameter('locale_language', 'en');
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $this->assertEquals('Logout', trim($crawler->filter("#link_logout")->text()));

        $crawler = $this->client->request('GET', '/admin/platform/settings/form');
        $form = $crawler->filter('input[type=submit]')->form();
        $form['claro_settings_form[localLanguage]'] = 'fr';
        $crawler = $this->client->submit($form);

        $this->assertEquals('Déconnexion', trim($crawler->filter("#link_logout")->text()));
    }

    private function getUser($username)
    {
        $user = $this->em->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneByUsername($username);

        return $user;
    }
}
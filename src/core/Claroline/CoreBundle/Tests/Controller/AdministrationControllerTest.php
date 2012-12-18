<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Installation\Plugin\Loader;
use Symfony\Component\HttpFoundation\Response;

class AdministrationControllerTest extends FunctionalTestCase
{
    /** @var Claroline\CoreBundle\Library\Testing\PlatformTestConfigurationHandler */
    private $configHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user', 'admin'));
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

    public function testAdmincanViewGroups()
    {
        $this->loadGroupFixture(array('group_a'));
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(1, $crawler->filter('.row-group')->count());
    }

    public function testAdminCanSearchGroups()
    {
        $this->loadGroupFixture(array('group_a'));
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/groups/search/A/0.html');
        $this->assertEquals(1, $crawler->filter('.row-group')->count());
    }

    public function testAdminCanSearchUsers()
    {
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/users/search/john/0.html');
        $this->assertEquals(1, $crawler->filter('.row-user')->count());
    }

    public function testAdmincanViewUsersFromGroup()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users/0");
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
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
        $form['profile_form[platformRole]'] = $this->getFixtureReference('role/user')->getId();
        $this->client->submit($form);
        $user = $this->getUser('tototata');
        $repositoryWs = $user->getPersonalWorkspace();
        $this->assertEquals(1, count($repositoryWs));
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(3, $crawler->filter('.row-user')->count());
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

    public function testmultiDeleteUsers()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(2, $crawler->filter('.row-user')->count());
        $this->client->request('DELETE', "/admin/users?ids[]={$this->getFixtureReference('user/user')->getId()}");
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(1, $crawler->filter('.row-user')->count());
    }

    public function S_testAdminCannotDeleteHimself()
    {
        $admin = $this->getFixtureReference('user/admin');
        $crawler = $this->logUser($admin);
        $crawler = $this->client->request('GET', '/admin/users/0.html');
        $this->assertEquals(5, $crawler->filter('.row-user')->count());
        $this->assertEquals(0, count($crawler->filter('.link-delete-user')->eq(4)));
        $this->client->request('DELETE', "/admin/user/{$admin->getId()}");
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    public function S_testUserCannotDeleteHimself()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('DELETE', "/admin/user/{$this->getFixtureReference('user/user')->getId()}");
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 403);
    }

    public function testAdminCanCreateGroups()
    {
        $this->loadGroupFixture(array('group_a'));
        $crawler = $this->logUser($this->getFixtureReference('user/admin'));
        $link = $crawler->filter('#link-administration')->link();
        $crawler = $this->client->click($link);
        $link = $crawler->filter('#link_group_create_form')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['group_form[name]'] = 'Group D';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(2, $crawler->filter('.row-group')->count());
    }

    public function testGroupCreationFormIsDisplayedWithErrors()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/group/form');
        $form = $crawler->filter('button[type=submit]')->form();
        $this->client->submit($form);
        $this->assertEquals(1, count($crawler->filter('#group_form')));
    }

    public function testAdminCanMultiAddUserToGroup()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'PUT',
            "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users?userIds[]={$this->getFixtureReference('user/admin')->getId()}"
        );
       $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users/0");
       $this->assertEquals(2, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testAdminCanMultiDeleteUsersFromGroup()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request(
            'PUT', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users?userIds[]={$this->getFixtureReference('user/admin')->getId()}"
        );

        $this->client->request(
            'DELETE', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users?userIds[]={$this->getFixtureReference('user/admin')->getId()}"
        );
       $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/users/0");
       $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testPaginatedGrouplessUsersAction()
    {
         $this->loadGroupFixture(array('group_a'));
         $this->logUser($this->getFixtureReference('user/admin'));
         $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/unregistered/users/0");
         $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testSearchPaginatedGrouplessUsersAction()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/unregistered/users/0/search/doe");
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testSearchPaginatedUserOfGroups()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}/search/doe/users/0");
        $this->assertEquals(1, count(json_decode($this->client->getResponse()->getContent())));
    }

    public function testAddUserToGroupLayoutAction()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', "/admin/group/add/{$this->getFixtureReference('group/group_a')->getId()}");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserGroupListLayout()
    {
       $this->loadGroupFixture(array('group_a'));
       $this->logUser($this->getFixtureReference('user/admin'));
       $this->client->request('GET', "/admin/group/{$this->getFixtureReference('group/group_a')->getId()}");
       $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testMultiDeleteGroups()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('DELETE', "/admin/groups?ids[]={$this->getFixtureReference('group/group_a')->getId()}");
        $crawler = $this->client->request('GET', '/admin/groups/0.html');
        $this->assertEquals(0, $crawler->filter('.row-group')->count());
    }

    public function testAdminCanEditGroupSettings()
    {
        $this->loadGroupFixture(array('group_a'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $originalRoleId = $this->getFixtureReference('role/role_a')->getId();
        $adminRoleId = $this->getFixtureReference('role/admin')->getId();
        $crawler = $this->client->request('GET', "/admin/group/settings/form/{$this->getFixtureReference('group/group_a')->getId()}");
        $selected = $crawler->filter("option[value={$originalRoleId}]")->attr('selected');
        $this->assertEquals('selected', $selected);
        $form = $crawler->filter('button[type=submit]')->form();
        $form['group_form[platformRole]'] = $this->getFixtureReference('role/admin')->getId();
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/admin/group/settings/form/{$this->getFixtureReference('group/group_a')->getId()}");
        $selected = $crawler->filter("option[value={$adminRoleId}]")->attr('selected');
        $this->assertEquals('selected', $selected);
    }

    public function testGroupSettingsFormWithErrorsIsRendered()
    {
        $this->loadGroupFixture(array('group_a'));
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

    public function testPluginParametersActionThrowsEvent()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', '/admin/plugins');
        $this->client->request('GET', '/admin/plugin/plugin/options');
        $this->assertContains('plugin_options_plugin', $this->client->getResponse()->getContent());
    }

    public function testAdmincanViewWidgetParameters()
    {
        $this->registerStubPlugins(array(
            'Valid\Simple\ValidSimple',
            'Valid\WithWidgets\ValidWithWidgets'
        ));
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin/widgets');
        //exampletext has 4 widgets
        $this->assertGreaterThan(3, count($crawler->filter('.row-widget-config')));
    }

    public function testAdminCanSetWidgetVisibleOption()
    {
        $this->registerStubPlugins(array(
            'Valid\Simple\ValidSimple',
            'Valid\WithWidgets\ValidWithWidgets'
        ));
        $this->logUser($this->getFixtureReference('user/admin'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isVisible' => true, 'isDesktop' => false));
        //exampletext has 4 widgets
        $this->assertGreaterThan(3, count($configs));
        $this->client->request('POST', "/admin/plugin/visible/{$configs[0]->getId()}");
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isVisible' => true, 'isDesktop' => false));
        $this->assertGreaterThan(2, count($configs));
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isVisible' => false, 'isDesktop' => false));
        $this->assertGreaterThan(0, count($configs));
    }

    public function testAdminCanSetWidgetLockOption()
    {
        $this->registerStubPlugins(array(
            'Valid\Simple\ValidSimple',
            'Valid\WithWidgets\ValidWithWidgets'
        ));
        $this->logUser($this->getFixtureReference('user/admin'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isLocked' => true, 'isDesktop' => false));
        //exampletext has 4 widgets
        $this->assertGreaterThan(3, count($configs));
        $this->client->request('POST', "/admin/plugin/lock/{$configs[0]->getId()}");
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isLocked' => true, 'isDesktop' => false));
        $this->assertGreaterThan(2, count($configs));
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isLocked' => false, 'isDesktop' => false));
        $this->assertGreaterThan(0, count($configs));
    }

    public function testDesktopDisplayVisibleWidgets()
    {
                $this->registerStubPlugins(array(
            'Valid\Simple\ValidSimple',
            'Valid\WithWidgets\ValidWithWidgets'
        ));
         $this->logUser($this->getFixtureReference('user/admin'));
         $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
         $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isVisible' => true, 'isDesktop' => true));
         $crawler = $this->client->request('GET', '/desktop/info');
         $this->assertEquals(count($crawler->filter('.widget')), count($configs));
    }

    public function testConfigureWorkspaceWidgetActionThrowsEvent()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $widget = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneBy(array('name' => 'claroline_rssreader'));
        $crawler = $this->client->request('GET', "/admin/widget/{$widget->getId()}/configuration/workspace");
        $this->assertEquals(count($crawler->filter('#rss_form')), 1);
    }

    public function testConfigureDesktopWidgetActionThrowsEvent()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $widget = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\Widget')->findOneBy(array('name' => 'claroline_rssreader'));
        $crawler = $this->client->request('GET', "/admin/widget/{$widget->getId()}/configuration/desktop");
        $this->assertEquals(count($crawler->filter('#rss_form')), 1);
    }

    private function registerStubPlugins(array $pluginFqcns)
    {
        $container = $this->client->getContainer();
        $dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $pluginDirectory = $container->getParameter('claroline.stub_plugin_directory');
        $loader = new Loader($pluginDirectory);
        $validator = $container->get('claroline.plugin.validator');

        foreach ($pluginFqcns as $pluginFqcn) {
            $plugin = $loader->load($pluginFqcn);
            $validator->validate($plugin);
            $dbWriter->insert($plugin, $validator->getPluginConfiguration());
        }
    }

    private function getUser($username)
    {
        $user = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\User')
            ->findOneByUsername($username);

        return $user;
    }
}
<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class HomeControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->resetTemplate();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
    }

    public function testWorkspaceManagercanViewWidgetProperties()
    {
        $this->registerStubPlugins(array('Valid\WithWidgets\ValidWithWidgets'));
        $pwuId = $this->getUser('john')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request('GET', "/tool/home/workspace/{$pwuId}/widget");
        $this->assertGreaterThan(3, count($crawler->filter('.row-widget-config')));
        $this->resetTemplate();
    }

    public function testViewWidgetPropertiesIsProtected()
    {
        $this->loadUserData(array('alfred' => 'user'));
        $pwuId = $this->getUser('john')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('alfred'));
        $this->client->request('GET', "/tool/home/workspace/{$pwuId}/widget");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDisplayWidgetConfigurationFormPage()
    {
        $this->markTestSkipped('No event can be caught.');
        $this->registerStubPlugins(array('Valid\WithWidgets\ValidWithWidgets'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $newWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName('claroline_testwidget1');
        $pwuId = $this->getUser('john')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('john'));
        $this->client->request(
            'GET',
            "/tool/home/workspace/{$pwuId}/widget/{$newWidget->getId()}/configuration"
        );

        $this->resetTemplate();
    }

    public function testWorkspaceManagerCanInvertWidgetVisible()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->registerStubPlugins(array('Valid\WithWidgets\ValidWithWidgets'));
        $pwuId = $this->getUser('john')->getPersonalWorkspace()->getId();
        //admin must unlock first
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findAll();
        $crawler = $this->client->request('GET', "/workspaces/{$pwuId}/widgets");
        $countVisibleWidgets = count($crawler->filter('.widget'));
        $this->client->request(
            'POST',
            "/tool/home/workspace/{$pwuId}/widget/{$configs[0]->getWidget()->getId()}/baseconfig"
            . "/{$configs[0]->getId()}/invertvisible"
        );
        $crawler = $this->client->request('GET', "/workspaces/{$pwuId}/widgets");
        $this->assertEquals(--$countVisibleWidgets, count($crawler->filter('.widget')));
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findAll();
        $this->logUser($this->getUser('admin'));
        $this->client->request('POST', "/admin/plugin/lock/{$configs[0]->getId()}");
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request('GET', "/workspaces/{$pwuId}/widgets");
        $this->assertEquals(++$countVisibleWidgets, count($crawler->filter('.widget')));
        $newWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName('claroline_testwidget1');
        $baseConfig = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('widget' => $newWidget, 'isDesktop' => false));
        //try to create a new DisplayConfig entity
        $this->client->request(
            'POST',
            "/tool/home/workspace/{$pwuId}/widget/{$newWidget->getId()}/baseconfig"
            . "/{$baseConfig->getId()}/invertvisible"
        );
        $crawler = $this->client->request('GET', "/workspaces/{$pwuId}/widgets");
        $this->assertEquals(--$countVisibleWidgets, count($crawler->filter('.widget')));

        $this->resetTemplate();
    }

    public function testWorkspaceWidgetVisibleInversionIsProtected()
    {
        $this->loadUserData(array('alfred' => 'user'));
        $pwuId = $this->getUser('john')->getPersonalWorkspace()->getId();
        $this->logUser($this->getUser('alfred'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $configs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findAll();
        $this->client->request(
            'POST',
            "/tool/home/workspace/{$pwuId}/widget/{$configs[0]->getWidget()->getId()}/baseconfig"
            . "/{$configs[0]->getId()}/invertvisible"
        );
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDesktopManagerCanInvertWidgetVisible()
    {
        $this->loadUserData(array('admin' => 'admin'));
        //admin must unlock first
        $this->logUser($this->getUser('john'));
        $configs = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findBy(array('isDesktop' => true));
        $countConfigs = count($configs);
        $crawler = $this->client->request('GET', '/desktop/tool/open/home');
        $countVisibleWidgets = count($crawler->filter('.widget'));
        $this->client->request(
            'POST',
            "/tool/home/desktop/config/{$configs[0]->getId()}"
            . "/widget/{$configs[0]->getWidget()->getId()}/invertvisible"
        );
        $crawler = $this->client->request('GET', '/desktop/tool/open/home');
        $this->assertEquals(--$countVisibleWidgets, count($crawler->filter('.widget')));
        $configs = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findBy(array('isDesktop' => true));
        $this->assertEquals(++$countConfigs, count($configs));
        $this->logUser($this->getUser('admin'));
        $this->client->request('POST', "/admin/plugin/lock/{$configs[0]->getId()}");
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request('GET', '/desktop/tool/open/home');
        $this->assertEquals(++$countVisibleWidgets, count($crawler->filter('.widget')));
    }

    public function testDesktopWidgetProperties()
    {
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request(
            'GET',
            "/tool/home/desktop/widget/properties"
        );

        $this->assertEquals(1, count($crawler->filter('#widget-table')));
    }
}

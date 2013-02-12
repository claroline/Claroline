<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class DesktopControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    //test if the url is working
    public function testPersoAction()
    {
        $this->loadUserFixture(array('admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', '/desktop/tool/open/home');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //test if the url is working
    public function testResourceManagerAction()
    {
        $this->loadUserFixture(array('admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', '/desktop/tool/open/resource_manager');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDesktopParametersAction()
    {
        $this->loadUserFixture(array('admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/desktop/tool/open/parameters');
        $this->assertEquals(2, count($crawler->filter('.li-user-parameters')));
    }

    public function testManagerCanInvertWidgetVisible()
    {
        $this->loadUserFixture(array('user', 'admin'));
        //admin must unlock first
        $this->logUser($this->getFixtureReference('user/user'));
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
            "/desktop/tool/properties/config/{$configs[0]->getId()}"
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
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('POST', "/admin/plugin/lock/{$configs[0]->getId()}");
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/desktop/tool/open/home');
        $this->assertEquals(++$countVisibleWidgets, count($crawler->filter('.widget')));
    }
}
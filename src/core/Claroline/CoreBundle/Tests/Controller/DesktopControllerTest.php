<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class DesktopControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    //test if the url is working
    public function testPersoAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', '/desktop/perso');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //test if the url is working
    public function testResourceManagerAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('GET', '/desktop/resources');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDesktopParametersAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/desktop/user/parameters');
        $this->assertEquals(1, count($crawler->filter('.li-user-parameters')));
    }

    public function testManagerCanInvertWidgetVisible()
    {
        //admin must unlock first
        $this->logUser($this->getFixtureReference('user/user'));
        $configs = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isDesktop' => true));
        $countConfigs = count($configs);
        $crawler = $this->client->request('GET', "/desktop/info");
        $countVisibleWidgets = count($crawler->filter('.widget'));
        $this->client->request(
            'POST', "/desktop/config/{$configs[0]->getId()}/widget/{$configs[0]->getWidget()->getId()}/invertvisible"
        );
                   var_dump($this->client->getResponse()->getContent());
        $crawler = $this->client->request('GET', "/desktop/info");
        $this->assertEquals($countVisibleWidgets, count($crawler->filter('.widget')));
        $configs = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('isDesktop' => true));
        $this->assertEquals(++$countConfigs, count($configs));
        $this->logUser($this->getFixtureReference('user/admin'));
        $this->client->request('POST', "/admin/plugin/lock/{$configs[0]->getId()}");
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/desktop/info");
        $this->assertEquals(--$countVisibleWidgets, count($crawler->filter('.widget')));
    }
}
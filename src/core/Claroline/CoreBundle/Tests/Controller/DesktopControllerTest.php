<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class DesktopControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
    }

    //test if the url is working
    public function testPersoAction()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request('GET', '/desktop/tool/open/home');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    //test if the url is working
    public function testResourceManagerAction()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request('GET', '/desktop/tool/open/resource_manager');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testDesktopParametersAction()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request('GET', '/desktop/tool/open/parameters');
        $this->assertEquals(2, count($crawler->filter('.li-user-parameters')));
    }

    public function testOpenDesktopActionRedirectsToHomeByDefault()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $this->client->request('GET', '/desktop/open');
        $currentUrl = $this->client->getRequest()->getUri();
        $this->assertRegExp('/\/open\/home$/', $currentUrl);
    }
}
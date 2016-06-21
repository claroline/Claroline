<?php

namespace Claroline\CoreBundle\Tests\API\Admin;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Library\Testing\Persister;

/**
 * Specific tests for organizations
 * How to run:
 * - create database
 * - bin/phpunit vendor/claroline/core/.../PluginControllerTest.php -c app/phpunit.xml.
 */
class PluginControllerTest extends TransactionalTestCase
{
    /** @var Persister */
    private $persister;
    /** @var User */
    private $admin;

    protected function setUp()
    {
        parent::setUp();
        $this->persister = $this->client->getContainer()->get('claroline.library.testing.persister');
        $roleAdmin = $this->persister->role('ROLE_ADMIN');
        $this->admin = $this->persister->user('admin');
        $this->admin->addRole($roleAdmin);
        $this->persister->persist($this->admin);
        $this->persister->flush();
    }

    //@route: api_get_locations
    //@url: /api/plugins.{_format}
    public function testGetPluginsAction()
    {
        $this->logIn($this->admin);
        $this->client->request('GET', '/api/plugins.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertGreaterThanOrEqual(35, count($data));
    }

    //@route: api_disable_plugin
    //@url: /api/plugin/{plugin}/disable.{_format}
    public function testDisablePluginAction()
    {
        $this->logIn($this->admin);

        //get the plugin list
        $this->client->request('GET', '/api/plugins.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->client->request('PATCH', "/api/plugin/{$data[0]['id']}/disable.json");
        $this->client->request('GET', '/api/plugins.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(false, $data[0]['is_loaded']);
    }

    //@route: api_enable_plugin
    //@url: /api/plugin/{plugin}/enable.{_format}
    public function testEnablePluginAction()
    {
        $this->logIn($this->admin);

        //get the plugin list
        $this->client->request('GET', '/api/plugins.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->client->request('PATCH', "/api/plugin/{$data[0]['id']}/enable.json");
        $this->client->request('GET', '/api/plugins.json');
        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->assertEquals(true, $data[0]['is_loaded']);
    }
}

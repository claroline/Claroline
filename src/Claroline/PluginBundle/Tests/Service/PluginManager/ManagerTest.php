<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ManagerTest extends WebTestCase
{
    private $client;

    /** @var Manager */
    private $manager;

    public function setUp()
    {
        $this->client = self::createClient();
        $this->manager = $this->client->getContainer()->get('claroline.plugin.manager');
    }

    public function test()
    {

    }
}
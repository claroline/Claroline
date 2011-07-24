<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileWriterTest extends WebTestCase
{
    private $client;

    /** @var FileWriter */
    private $fileWriter;

    public function setUp()
    {
        $this->client = self::createClient();
        $this->fileWriter = $this->client->getContainer()->get('claroline.plugin.file_writer');
    }

    public function test()
    {
        
    }
}
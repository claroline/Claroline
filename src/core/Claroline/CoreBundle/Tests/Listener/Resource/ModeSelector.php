<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModeSelector extends WebTestCase
{
    public function testModeDefaultValue()
    {
        $this->assertFalse(Mode::$isPathMode);
    }

    public function testModeCanBeSwitchedToPath()
    {
        $client = static::createClient();
        $client->request('GET', '/', array('_mode' => 'path'));

        $this->assertTrue(Mode::$isPathMode);
    }
}
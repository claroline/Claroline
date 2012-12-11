<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModeSelector extends WebTestCase
{
    public function testModeDefaultValue()
    {
        $this->assertFalse(Mode::$isPathMode);
        $this->assertEquals('ClarolineCoreBundle:Workspace:layout.html.twig', Mode::$template);
    }

    public function testModeCanBeSwitchedToPath()
    {
        $client = static::createClient();
        $client->request('GET', '/', array('_mode' => 'path'));

        $this->assertTrue(Mode::$isPathMode);
        $this->assertEquals('ClarolineCoreBundle:Resource:path_layout.html.twig', Mode::$template);
    }
}
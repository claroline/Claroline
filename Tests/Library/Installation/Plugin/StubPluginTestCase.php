<?php

namespace Claroline\CoreBundle\Tests\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Library\Installation\Plugin\Loader;

abstract class StubPluginTestCase extends WebTestCase
{
    private $loader;
    private $stubPluginsPath;

    protected function setUp()
    {
        $this->loader = new Loader();
        $this->stubPluginsPath = __DIR__ . '/../../../Stub/plugin';
    }

    protected function getLoader()
    {
        return $this->loader;
    }

    protected function buildPluginPath($fqcn)
    {
        return $this->stubPluginsPath . '/' . str_replace('\\', '/', $fqcn) . '.php';
    }
}

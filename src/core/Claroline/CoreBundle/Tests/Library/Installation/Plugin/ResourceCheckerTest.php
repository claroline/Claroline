<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Tests\Library\Installation\Plugin\StubPluginTestCase;
use Claroline\CoreBundle\Library\Installation\Plugin\ValidationError;

class ResourceCheckerTest extends StubPluginTestCase
{
    /** @var CommonChecker */
    private $checker;

    protected function setUp()
    {
        parent::setUp();
        $container = static::createClient()->getContainer();
        $this->checker = $container->get('claroline.plugin.config_checker');
    }

    public function testCheckerReturnsAnErrorOnNonExistentResourceFile()
    {
        $pluginFqcn = 'Invalid\NonExistentConfigFile1\InvalidNonExistentConfigFile1';
        $path = $this->buildPluginPath($pluginFqcn);
        $errors = $this->checker->check($this->getLoader()->load($pluginFqcn, $path));
        $this->assertContains("config.yml file missing", $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnMissingResourceKey()
    {
        $pluginFqcn = 'Invalid\MissingResourceKey1\InvalidMissingResourceKey1';
        $path = $this->buildPluginPath($pluginFqcn);
        $errors = $this->checker->check($this->getLoader()->load($pluginFqcn, $path));
        $this->assertTrue($errors[0] instanceof ValidationError);
    }

    public function testCheckerReturnsAnErrorOnUnloadableResourceClass()
    {
        $pluginFqcn = 'Invalid\UnloadableResourceClass1\InvalidUnloadableResourceClass1';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Invalid{$ds}"
            . "UnloadableResourceClass1{$ds}Entity{$ds}ResourceX.php";
        $path = $this->buildPluginPath($pluginFqcn);
        $errors = $this->checker->check($this->getLoader()->load($pluginFqcn, $path));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('was not found', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnloadableResourceClass2()
    {
        $pluginFqcn = 'Invalid\UnloadableResourceClass2\InvalidUnloadableResourceClass2';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Invalid{$ds}"
            . "UnloadableResourceClass2{$ds}Entity{$ds}ResourceX.php";
        $path = $this->buildPluginPath($pluginFqcn);
        $errors = $this->checker->check($this->getLoader()->load($pluginFqcn, $path));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('must extend', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnexpectedLargeIcon()
    {
        $pluginFqcn = 'Invalid\UnexpectedResourceIcon\InvalidUnexpectedResourceIcon';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Invalid{$ds}"
            . "UnexpectedResourceIcon{$ds}Entity{$ds}ResourceX.php";
        $path = $this->buildPluginPath($pluginFqcn);
        $errors = $this->checker->check($this->getLoader()->load($pluginFqcn, $path));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('this file was not found', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnexpectedIcon()
    {
        $pluginFqcn = 'Invalid\UnexpectedIcon\InvalidUnexpectedIcon';
        $path = $this->buildPluginPath($pluginFqcn);
        $errors = $this->checker->check($this->getLoader()->load($pluginFqcn, $path));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('this file was not found', $errors[0]->getMessage());
    }
}

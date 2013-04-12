<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Library\Installation\Plugin\ValidationError;

class ResourceCheckerTest extends WebTestCase
{
    /** @var CommonChecker */
    private $checker;

    /** @var Loader */
    private $loader;

    protected function setUp()
    {
        $container = static::createClient()->getContainer();
        $this->checker = $container->get('claroline.plugin.config_checker');
        $pluginDirectory = $container->getParameter('claroline.param.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
    }

    public function testCheckerReturnsAnErrorOnNonExistentResourceFile()
    {
        $pluginFqcn = 'Invalid\NonExistentConfigFile1\InvalidNonExistentConfigFile1';
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertContains("config.yml file missing", $errors[0]->getMessage());
    }


    public function testCheckerReturnsAnErrorOnMissingResourceKey()
    {
        $pluginFqcn = 'Invalid\MissingResourceKey1\InvalidMissingResourceKey1';
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
    }

    public function testCheckerReturnsAnErrorOnUnloadableResourceClass()
    {
        $pluginFqcn = 'Invalid\UnloadableResourceClass1\InvalidUnloadableResourceClass1';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Invalid{$ds}"
            . "UnloadableResourceClass1{$ds}Entity{$ds}ResourceX.php";
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('was not found', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnloadableResourceClass2()
    {
        $pluginFqcn = 'Invalid\UnloadableResourceClass2\InvalidUnloadableResourceClass2';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Invalid{$ds}"
            . "UnloadableResourceClass2{$ds}Entity{$ds}ResourceX.php";
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('must extend', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnexpectedLargeIcon()
    {
        $pluginFqcn = 'Invalid\UnexpectedResourceIcon\InvalidUnexpectedResourceIcon';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Invalid{$ds}"
            . "UnexpectedResourceIcon{$ds}Entity{$ds}ResourceX.php";
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('this file was not found', $errors[0]->getMessage());
    }

    public function testCheckerReturnsAnErrorOnUnexpectedIcon()
    {
        $pluginFqcn = 'Invalid\UnexpectedIcon\InvalidUnexpectedIcon';
        $errors = $this->checker->check($this->loader->load($pluginFqcn));
        $this->assertTrue($errors[0] instanceof ValidationError);
        $this->assertContains('this file was not found', $errors[0]->getMessage());
    }
}
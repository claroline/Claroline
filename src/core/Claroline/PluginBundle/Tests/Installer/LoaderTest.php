<?php

namespace Claroline\PluginBundle\Installer;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Exception\LoaderException;

class LoaderTest extends WebTestCase
{
    /** @var Claroline\PluginBundle\Installer\Loader */
    private $loader;
    
    /** @var string */
    private $extensionPath;
    
    public function setUp()
    {
        $this->loader = self::createClient()->getContainer()->get('claroline.plugin.loader');
        $this->overrideDefaultPluginDirectories($this->loader);
    }
    
    public function testLoaderCanReturnAnInstanceOfALoadablePluginBundleClass()
    {
        $plugin = $this->loader->load('Valid\Basic\ValidBasic');
        
        $this->assertInstanceOf('Valid\Basic\ValidBasic', $plugin);
    }
    
    public function testLoaderThrowsAnExceptionIfExpectedBundleClassFileDoesntExist()
    {
        try
        {
            $this->loader->load('Invalid\NoBundleClassFile\InvalidNoBundleClassFile');
            $this->fail('No exception thrown');
        }
        catch (LoaderException $ex)
        {
            $this->assertEquals(LoaderException::NO_PLUGIN_FOUND, $ex->getCode());
        }
    }
    
    public function testLoaderThrowsAnExceptionIfMoreThanOneBundleClassFileIsFound()
    {
        try
        {
            $this->loader->load('Incompatible\SameFQCNThanAnotherPlugin\IncompatibleSameFQCNThanAnotherPlugin');
            $this->fail('No exception thrown');
        }
        catch (LoaderException $ex)
        {
            $this->assertEquals(LoaderException::MULTIPLE_PLUGINS_FOUND, $ex->getCode());
        }
    }
    
    /**
     * @dataProvider nonExistentBundleClassProvider
     */
    public function testLoaderThrowsAnExceptionIfExpectedBundleClassDoesntExist($fqcn)
    {
        try
        {
            $this->loader->load($fqcn);
            $this->fail('No exception thrown');
        }
        catch (LoaderException $ex)
        {
            $this->assertEquals(LoaderException::NON_EXISTENT_BUNDLE_CLASS, $ex->getCode());
        }
    }
    
    /**
     * @dataProvider nonInstantiableBundleClassProvider
     */
    public function testLoaderThrowsAnExceptionIfBundleClassIsNotInstantiable($fqcn)
    {
        try
        {
            $this->loader->load($fqcn);
            $this->fail('No exception thrown');
        }
        catch (LoaderException $ex)
        {
            $this->assertEquals(LoaderException::NON_INSTANTIABLE_BUNDLE_CLASS, $ex->getCode());
        }
    }
    
    public function nonExistentBundleClassProvider()
    {
        return array(
            array('Invalid\UnloadableBundleClass1\InvalidUnloadableBundleClass1'),
            array('Invalid\UnloadableBundleClass2\InvalidUnloadableBundleClass2'),
            array('Invalid\UnloadableBundleClass3\InvalidUnloadableBundleClass3'),
            array('Invalid\UnloadableBundleClass4\InvalidUnloadableBundleClass4')
        );
    }
    
    public function nonInstantiableBundleClassProvider()
    {
        return array(
            array('Invalid\UnloadableBundleClass5\InvalidUnloadableBundleClass5'),
            array('Invalid\UnloadableBundleClass6\InvalidUnloadableBundleClass6')
        );
    }
    
    private function overrideDefaultPluginDirectories(Loader $loader)
    {
        $ds = DIRECTORY_SEPARATOR;
        $pluginDir = __DIR__ . "{$ds}..{$ds}stub{$ds}plugin{$ds}";
        $this->extensionPath = "{$pluginDir}extension";
        $loader->setPluginDirectories(
            array(
                'extension' => $this->extensionPath,
                'application' => "{$pluginDir}application",
                'tool' =>"{$pluginDir}tool"
            )
        );
    }
}
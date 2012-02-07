<?php

namespace Claroline\CoreBundle\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Exception\InstallationException;

class LoaderTest extends WebTestCase
{
    /** @var Claroline\CoreBundle\Installer\Loader */
    private $loader;
    
    /** @var string */
    private $extensionPath;
    
    protected function setUp()
    {
        $container = self::createClient()->getContainer();
        $this->loader = $container->get('claroline.plugin.loader');
        $stubDir = $container->getParameter('claroline.stub_plugin_directory');
        $this->overrideDefaultPluginDirectories($this->loader, $stubDir);
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
        catch (InstallationException $ex)
        {
            $this->assertEquals(InstallationException::NO_PLUGIN_FOUND, $ex->getCode());
        }
    }
    
    public function testLoaderThrowsAnExceptionIfMoreThanOneBundleClassFileIsFound()
    {
        try
        {
            $this->loader->load('Incompatible\SameFQCNThanAnotherPlugin\IncompatibleSameFQCNThanAnotherPlugin');
            $this->fail('No exception thrown');
        }
        catch (InstallationException $ex)
        {
            $this->assertEquals(InstallationException::MULTIPLE_PLUGINS_FOUND, $ex->getCode());
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
        catch (InstallationException $ex)
        {
            $this->assertEquals(InstallationException::NON_EXISTENT_BUNDLE_CLASS, $ex->getCode());
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
        catch (InstallationException $ex)
        {
            $this->assertEquals(InstallationException::NON_INSTANTIABLE_BUNDLE_CLASS, $ex->getCode());
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
    
    private function overrideDefaultPluginDirectories(Loader $loader, $stubDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->extensionPath = "{$stubDir}{$ds}extension";
        $loader->setPluginDirectories(
            array(
                'extension' => $this->extensionPath,
                'tool' =>"{$stubDir}{$ds}tool"
            )
        );
    }
}
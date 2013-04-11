<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoaderTest extends WebTestCase
{
    /** @var Loader */
    private $loader;

    protected function setUp()
    {
        $pluginDirectory = static::createClient()
            ->getContainer()
            ->getParameter('claroline.param.stub_plugin_directory');
        $this->loader = new Loader($pluginDirectory);
    }

    public function testLoaderCanReturnAnInstanceOfALoadablePluginBundleClass()
    {
        $plugin = $this->loader->load('Valid\Simple\ValidSimple');
        $this->assertInstanceOf('Valid\Simple\ValidSimple', $plugin);
    }

    public function testLoaderThrowsAnExceptionIfExpectedBundleClassFileDoesntExist()
    {
        try {
            $this->loader->load('Invalid\NoBundleClassFile\InvalidNoBundleClassFile');
            $this->fail('No exception thrown');
        } catch (RuntimeException $ex) {
            $this->assertEquals(Loader::NO_PLUGIN_FOUND, $ex->getCode());
        }
    }

    /**
     * @dataProvider nonExistentBundleClassProvider
     */
    public function testLoaderThrowsAnExceptionIfExpectedBundleClassDoesntExist($fqcn)
    {
        try {
            $this->loader->load($fqcn);
            $this->fail('No exception thrown');
        } catch (RuntimeException $ex) {
            $this->assertEquals(Loader::NON_EXISTENT_BUNDLE_CLASS, $ex->getCode());
        }
    }

    /**
     * @dataProvider nonInstantiableBundleClassProvider
     */
    public function testLoaderThrowsAnExceptionIfBundleClassIsNotInstantiable($fqcn)
    {
        try {
            $this->loader->load($fqcn);
            $this->fail('No exception thrown');
        } catch (RuntimeException $ex) {
            $this->assertEquals(Loader::NON_INSTANTIABLE_BUNDLE_CLASS, $ex->getCode());
        }
    }

    /**
     * @dataProvider unexpectedBundleTypeProvider
     */
    public function testLoaderThrowsAnExceptionIfBundleClassDoesntExtendPluginBundle($fqcn)
    {
        try {
            $this->loader->load($fqcn);
            $this->fail('No exception thrown');
        } catch (RuntimeException $ex) {
            $this->assertEquals(Loader::UNEXPECTED_BUNDLE_TYPE, $ex->getCode());
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

    public function unexpectedBundleTypeProvider()
    {
        return array(
            array('Invalid\UnexpectedBundleType\InvalidUnexpectedBundleType')
        );
    }
}
<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Tests\Library\Installation\Plugin\StubPluginTestCase;

class LoaderTest extends StubPluginTestCase
{
    public function testLoaderCanReturnAnInstanceOfALoadablePluginBundleClass()
    {
        $path = $this->buildPluginPath('Valid\Simple\ValidSimple');
        $plugin = $this->getLoader()->load('Valid\Simple\ValidSimple', $path);
        $this->assertInstanceOf('Valid\Simple\ValidSimple', $plugin);
    }

    public function testLoaderThrowsAnExceptionIfExpectedBundleClassFileDoesntExist()
    {
        try {
            $path = $this->buildPluginPath('Invalid\NoBundleClassFile\InvalidNoBundleClassFile');
            $this->getLoader()->load('Invalid\NoBundleClassFile\InvalidNoBundleClassFile', $path);
            $this->fail('No exception thrown');
        } catch (\RuntimeException $ex) {
            $this->assertEquals(Loader::NO_PLUGIN_FOUND, $ex->getCode());
        }
    }

    /**
     * @dataProvider nonExistentBundleClassProvider
     */
    public function testLoaderThrowsAnExceptionIfExpectedBundleClassDoesntExist($fqcn)
    {
        try {
            $path = $this->buildPluginPath($fqcn);
            $this->getLoader()->load($fqcn, $path);
            $this->fail('No exception thrown');
        } catch (\RuntimeException $ex) {
            $this->assertEquals(Loader::NON_EXISTENT_BUNDLE_CLASS, $ex->getCode());
        }
    }

    /**
     * @dataProvider nonInstantiableBundleClassProvider
     */
    public function testLoaderThrowsAnExceptionIfBundleClassIsNotInstantiable($fqcn)
    {
        try {
            $path = $this->buildPluginPath($fqcn);
            $this->getLoader()->load($fqcn, $path);
            $this->fail('No exception thrown');
        } catch (\RuntimeException $ex) {
            $this->assertEquals(Loader::NON_INSTANTIABLE_BUNDLE_CLASS, $ex->getCode());
        }
    }

    /**
     * @dataProvider unexpectedBundleTypeProvider
     */
    public function testLoaderThrowsAnExceptionIfBundleClassDoesntExtendPluginBundle($fqcn)
    {
        try {
            $path = $this->buildPluginPath($fqcn);
            $this->getLoader()->load($fqcn, $path);
            $this->fail('No exception thrown');
        } catch (\RuntimeException $ex) {
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

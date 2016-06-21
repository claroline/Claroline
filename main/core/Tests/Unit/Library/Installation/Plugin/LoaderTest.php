<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\StubPluginTrait;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    use StubPluginTrait;

    private $loader;

    public function setUp()
    {
        $this->loader = new Loader();
    }

    public function testLoaderCanReturnAnInstanceOfALoadablePluginBundleClass()
    {
        $path = $this->getPluginClassPath('Valid\Simple\ValidSimple');
        $plugin = $this->loader->load('Valid\Simple\ValidSimple', $path);
        $this->assertInstanceOf('Valid\Simple\ValidSimple', $plugin);
    }

    public function testLoaderThrowsAnExceptionIfExpectedBundleClassFileDoesntExist()
    {
        try {
            $path = $this->getPluginClassPath('Invalid\NoBundleClassFile\InvalidNoBundleClassFile');
            $this->loader->load('Invalid\NoBundleClassFile\InvalidNoBundleClassFile', $path);
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
            $path = $this->getPluginClassPath($fqcn);
            $this->loader->load($fqcn, $path);
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
            $path = $this->getPluginClassPath($fqcn);
            $this->loader->load($fqcn, $path);
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
            $path = $this->getPluginClassPath($fqcn);
            $this->loader->load($fqcn, $path);
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
            array('Invalid\UnloadableBundleClass4\InvalidUnloadableBundleClass4'),
        );
    }

    public function nonInstantiableBundleClassProvider()
    {
        return array(
            array('Invalid\UnloadableBundleClass5\InvalidUnloadableBundleClass5'),
            array('Invalid\UnloadableBundleClass6\InvalidUnloadableBundleClass6'),
        );
    }

    public function unexpectedBundleTypeProvider()
    {
        return array(
            array('Invalid\UnexpectedBundleType\InvalidUnexpectedBundleType'),
        );
    }
}

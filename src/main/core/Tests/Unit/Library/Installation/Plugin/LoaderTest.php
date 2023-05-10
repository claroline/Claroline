<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\Unit\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Installation\Plugin\Loader;
use Claroline\CoreBundle\Library\Testing\StubPluginTrait;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    use StubPluginTrait;

    private $loader;

    public function setUp(): void
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
        return [
            ['Invalid\UnloadableBundleClass1\InvalidUnloadableBundleClass1'],
            ['Invalid\UnloadableBundleClass2\InvalidUnloadableBundleClass2'],
            ['Invalid\UnloadableBundleClass3\InvalidUnloadableBundleClass3'],
            ['Invalid\UnloadableBundleClass4\InvalidUnloadableBundleClass4'],
        ];
    }

    public function nonInstantiableBundleClassProvider()
    {
        return [
            ['Invalid\UnloadableBundleClass5\InvalidUnloadableBundleClass5'],
            ['Invalid\UnloadableBundleClass6\InvalidUnloadableBundleClass6'],
        ];
    }

    public function unexpectedBundleTypeProvider()
    {
        return [
            ['Invalid\UnexpectedBundleType\InvalidUnexpectedBundleType'],
        ];
    }
}

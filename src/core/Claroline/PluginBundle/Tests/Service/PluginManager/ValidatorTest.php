<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Tests\PluginBundleTestCase;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

/**
 * Note : Unless otherwise specified, all the data providers of this test case
 *        refer to actual stub plugins living in the "stub/plugin" directory.
 */
class ValidatorTest extends PluginBundleTestCase
{
    /**
     * Helper method.
     * 
     * @param string $pluginFQCN The plugin's FQCN 
     * @param integer $exceptionCode A ValidationException class constant
     */
    private function assertValidationExceptionIsThrown($pluginFQCN, $exceptionCode)
    {
        try
        {
            $this->validator->check($pluginFQCN);
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals($exceptionCode, $ex->getCode());
        }
    }
    
    /***********************************************************************************/
    /*                              Base plugin tests                                  */
    /***********************************************************************************/
    
    public function testConstructorThrowsAnExceptionOnNonExistentPluginDirectoryPath()
    {
        try
        {
            new Validator('/non_existent/path', new \Symfony\Component\Yaml\Parser());
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_PLUGIN_DIR, $ex->getCode());
        }
    }

    /**
     * @dataProvider nonConventionalFQCNProvider
     */
    public function testCheckThrowsAnExceptionOnNonConventionalPluginFQCN($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_FQCN);
    }

    /**
     * @dataProvider nonExistentDirectoryStructureProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentPluginDirectoryStructure($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_DIRECTORY_STRUCTURE);
    }

    /**
     * @dataProvider nonExistentBundleClassFileProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentBundleClassFile($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_PLUGIN_CLASS_FILE);
    }

    /**
     * @dataProvider unloadableBundleClassProvider
     */
    public function testCheckThrowsAnExceptionOnUnloadableBundleClass($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_PLUGIN_CLASS);
    }

    /**
     * @dataProvider unexpectedPluginClassTypeProvider
     */
    public function testCheckThrowsAnExceptionIfPluginClassDoesntExtendClarolinePlugin($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_PLUGIN_TYPE);
    }

    /**
     * @dataProvider nonExistentRoutingResourceTypeProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentRoutingResource($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_ROUTING_PATH);
    }

    /**
     * @dataProvider unexpectedRoutingResourceLocationProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidRoutingResourceLocation($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_ROUTING_LOCATION);
    }

    /**
     * @dataProvider nonYamlRoutingResourceProvider
     */
    public function testCheckThrowsAnExceptionOnNonYamlRoutingFile($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_ROUTING_EXTENSION);
    }

    /**
     * @dataProvider unloadableYamlRoutingResourceProvider
     */
    public function testCheckThrowsAnExceptionOnUnloadableYamlRoutingFile($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_YAML_RESOURCE);
    }

    /**
     * @dataProvider unexpectedTranslationKeyProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidTranslationKey($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_TRANSLATION_KEY);
    }

    /**
     * @dataProvider validPluginProvider
     */
    public function testCheckDoesntThrowAnyExceptionOnValidPluginArgument($FQCN)
    {
        try
        {
            $this->validator->check($FQCN);
            $this->assertTrue(true);
        }
        catch (ValidationException $ex)
        {
            $this->fail("A validation exception was thrown with code {$ex->getCode()}.");
        }
    }
    
    public function nonConventionalFQCNProvider()
    {
        // Arbitrary strings
        return array(
          array('VendorX\DummyPluginBundle\BadNamespace\VendorXDummyPluginBundle'),
          array('VendorX\DummyPluginBundle\BadNamePluginBundle'),
          array('VendorX\VendorXDummyPluginBundle')
        );
    }
    
    public function nonExistentDirectoryStructureProvider()
    {
        // Arbitrary strings
        return array(
          array('NonExistentVendor\TestBundle\NonExistentVendorTestBundle'),
          array('Invalid\NonExistentBundle\InvalidNonExistentBundle'),
        );
    }

    public function nonExistentBundleClassFileProvider()
    {
        return array(
            array('Invalid\NoBundleClassFile\InvalidNoBundleClassFile')
        );
    }

    public function unloadableBundleClassProvider()
    {
        return array(
          array('Invalid\UnloadableBundleClass_1\InvalidUnloadableBundleClass_1'),
          array('Invalid\UnloadableBundleClass_2\InvalidUnloadableBundleClass_2'),
          array('Invalid\UnloadableBundleClass_3\InvalidUnloadableBundleClass_3'),
          array('Invalid\UnloadableBundleClass_4\InvalidUnloadableBundleClass_4')
        );
    }

    public function unexpectedPluginClassTypeProvider()
    {
        return array(
          array('Invalid\UnexpectedBundleClassType_1\InvalidUnexpectedBundleClassType_1'),
          array('Invalid\UnexpectedBundleClassType_2\InvalidUnexpectedBundleClassType_2')
        );
    }

    public function nonExistentRoutingResourceTypeProvider()
    {
        return array(
            array('Invalid\NonExistentRoutingResource_1\InvalidNonExistentRoutingResource_1'),
            array('Invalid\NonExistentRoutingResource_2\InvalidNonExistentRoutingResource_2')
        );
    }

    public function unexpectedRoutingResourceLocationProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingResourceLocation_1\InvalidUnexpectedRoutingResourceLocation_1')
        );
    }

    public function nonYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\NonYamlRoutingResource_1\InvalidNonYamlRoutingResource_1')
        );
    }

    public function unloadableYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\UnloadableRoutingResource_1\InvalidUnloadableRoutingResource_1')
        );
    }

    public function unexpectedTranslationKeyProvider()
    {
        return array(
            array('Invalid\UnexpectedTranslationKey_1\InvalidUnexpectedTranslationKey_1'),
            array('Invalid\UnexpectedTranslationKey_2\InvalidUnexpectedTranslationKey_2'),
            array('Invalid\UnexpectedTranslationKey_3\InvalidUnexpectedTranslationKey_3'),
            array('Invalid\UnexpectedTranslationKey_4\InvalidUnexpectedTranslationKey_4')
        );
    }

    public function validPluginProvider()
    {
        return array(
            array('Valid\Minimal\ValidMinimal')
        );
    }
    
    /***********************************************************************************/
    /*                            Application plugin tests                             */
    /***********************************************************************************/

    /**
     * @dataProvider unexpectedLauncherTypeProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedApplicationLauncherType($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_APPLICATION_LAUNCHER);
    }

    /**
     * @dataProvider noLauncherProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationDoesntProvideLaunchers($FQCN)
    {
        $this->assertValidationExceptionIsThrown(
                $FQCN,
                ValidationException::INVALID_APPLICATION_LAUNCHER);
    }

    /**
     * @dataProvider validApplicationProvider
     */
    public function testCheckDoesntThrowAnyExceptionOnValidApplicationArgument($FQCN)
    {
        try
        {
            $this->validator->check($FQCN);
            $this->assertTrue(true);
        }
        catch (ValidationException $ex)
        {
            $this->fail("A validation exception was thrown with code {$ex->getCode()}.");
        }
    }
    
    public function noLauncherProvider()
    {
        return array(
            array('InvalidApplication\NoLauncher\InvalidApplicationNoLauncher')
        );
    }

    public function unexpectedLauncherTypeProvider()
    {
        return array(
            array('InvalidApplication\UnexpectedLauncherType_1\InvalidApplicationUnexpectedLauncherType_1')
        );
    }
    
    public function validApplicationProvider()
    {
        return array(
            array('ValidApplication\TwoLaunchers\ValidApplicationTwoLaunchers')
        );
    }
}
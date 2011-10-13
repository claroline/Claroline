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
            new Validator(
                '/non_existent/path', 
                new \ArrayObject(),
                new \Symfony\Component\Yaml\Parser()
            );
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
    public function testCheckThrowsAnExceptionOnNonConventionalPluginFQCN($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_FQCN
        );
    }

    /**
     * @dataProvider nonExistentDirectoryStructureProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentPluginDirectoryStructure($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_DIRECTORY_STRUCTURE
        );
    }

    /**
     * @dataProvider nonExistentBundleClassFileProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentBundleClassFile($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_PLUGIN_CLASS_FILE
        );
    }

    /**
     * @dataProvider unloadableBundleClassProvider
     */
    public function testCheckThrowsAnExceptionOnUnloadableBundleClass($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_PLUGIN_CLASS
        );
    }

    /**
     * @dataProvider unexpectedPluginClassTypeProvider
     */
    public function testCheckThrowsAnExceptionIfPluginClassDoesntExtendClarolinePlugin($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_PLUGIN_TYPE
        );
    }

    /**
     * @dataProvider unexpectedRoutingPrefixProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedRoutingPrefix($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_ROUTING_PREFIX
        );
    }
    
    public function testCheckThrowsAnExceptionOnAlreadyRegisteredRoutingPrefix()
    {
        $this->client->beginTransaction();
        $this->manager->install('Valid\Custom\ValidCustom');
            
        try
        {
            $this->validator->check('Incompatible\ConflictWithValidCustom1\IncompatibleConflictWithValidCustom1');
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals(ValidationException::INVALID_ROUTING_PREFIX, $ex->getCode());
        }
        
        $this->client->rollback();
    }
    
    /**
     * @dataProvider nonExistentRoutingResourceTypeProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentRoutingResource($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_ROUTING_PATH
        );
    }

    /**
     * @dataProvider unexpectedRoutingResourceLocationProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedRoutingResourceLocation($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_ROUTING_LOCATION
        );
    }

    /**
     * @dataProvider nonYamlRoutingResourceProvider
     */
    public function testCheckThrowsAnExceptionOnNonYamlRoutingFile($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_ROUTING_EXTENSION
        );
    }

    /**
     * @dataProvider unloadableYamlRoutingResourceProvider
     */
    public function testCheckThrowsAnExceptionOnUnloadableYamlRoutingFile($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_YAML_RESOURCE
        );
    }

    /**
     * @dataProvider unexpectedTranslationKeyProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidTranslationKey($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_TRANSLATION_KEY
        );
    }

    /**
     * @dataProvider validPluginProvider
     */
    public function testCheckDoesntThrowAnyExceptionOnValidPluginArgument($fqcn)
    {
        try
        {
            $this->validator->check($fqcn);
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
            array('Invalid\UnloadableBundleClass1\InvalidUnloadableBundleClass1'),
            array('Invalid\UnloadableBundleClass2\InvalidUnloadableBundleClass2'),
            array('Invalid\UnloadableBundleClass3\InvalidUnloadableBundleClass3'),
            array('Invalid\UnloadableBundleClass4\InvalidUnloadableBundleClass4')
        );
    }

    public function unexpectedPluginClassTypeProvider()
    {
        return array(
            array('Invalid\UnexpectedBundleClassType1\InvalidUnexpectedBundleClassType1'),
            array('Invalid\UnexpectedBundleClassType2\InvalidUnexpectedBundleClassType2')
        );
    }

    public function unexpectedRoutingPrefixProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingPrefix1\InvalidUnexpectedRoutingPrefix1'),
            array('Invalid\UnexpectedRoutingPrefix2\InvalidUnexpectedRoutingPrefix2'),
            array('Invalid\UnexpectedRoutingPrefix3\InvalidUnexpectedRoutingPrefix3')
        );
    }
    
    public function nonExistentRoutingResourceTypeProvider()
    {
        return array(
            array('Invalid\NonExistentRoutingResource1\InvalidNonExistentRoutingResource1'),
            array('Invalid\NonExistentRoutingResource2\InvalidNonExistentRoutingResource2')
        );
    }

    public function unexpectedRoutingResourceLocationProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingResourceLocation1\InvalidUnexpectedRoutingResourceLocation1')
        );
    }

    public function nonYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\NonYamlRoutingResource1\InvalidNonYamlRoutingResource1')
        );
    }

    public function unloadableYamlRoutingResourceProvider()
    {
        return array(
            array('Invalid\UnloadableRoutingResource1\InvalidUnloadableRoutingResource1')
        );
    }

    public function unexpectedTranslationKeyProvider()
    {
        return array(
            array('Invalid\UnexpectedTranslationKey1\InvalidUnexpectedTranslationKey1'),
            array('Invalid\UnexpectedTranslationKey2\InvalidUnexpectedTranslationKey2'),
            array('Invalid\UnexpectedTranslationKey3\InvalidUnexpectedTranslationKey3'),
            array('Invalid\UnexpectedTranslationKey4\InvalidUnexpectedTranslationKey4')
        );
    }

    public function validPluginProvider()
    {
        return array(
            array('Valid\Minimal\ValidMinimal'),
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom')
        );
    }
    
    /***********************************************************************************/
    /*                            Application plugin tests                             */
    /***********************************************************************************/

    /**
     * @dataProvider unexpectedGetLauncherReturnValueProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedApplicationGetLauncherReturnValue($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_APPLICATION_GET_LAUNCHER_METHOD
        );
    }
    
    /**
     * @dataProvider unexpectedLauncherTypeProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedApplicationLauncherType($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_APPLICATION_LAUNCHER
        );
    }

    /**
     * @dataProvider noLauncherProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationDoesntProvideLaunchers($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_APPLICATION_LAUNCHER
        );
    }

    /**
     * @dataProvider unexpectedIndexRouteProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationReturnsAnUnexpectedIndexRoute($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_APPLICATION_INDEX
        );
    }
    
    /**
     * @dataProvider unexpectedIsEligibleReturnTypeProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationDoesntReturnABooleanInTheIsEligibleForIndexMethod($fqcn)
    {
        $this->assertValidationExceptionIsThrown(
            $fqcn,
            ValidationException::INVALID_APPLICATION_IS_ELIGIBLE_METHOD
        );
    }
    
    /**
     * @dataProvider validApplicationProvider
     */
    public function testCheckDoesntThrowAnyExceptionOnValidApplicationArgument($fqcn)
    {
        try
        {
            $this->validator->check($fqcn);
            $this->assertTrue(true);
        }
        catch (ValidationException $ex)
        {
            $this->fail("A validation exception was thrown with code {$ex->getCode()}.");
        }
    }

    public function unexpectedGetLauncherReturnValueProvider()
    {
        return array(
            array('InvalidApplication\UnexpectedGetLauncherReturnValue1\InvalidApplicationUnexpectedGetLauncherReturnValue1')
        );
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
            array('InvalidApplication\UnexpectedLauncherType1\InvalidApplicationUnexpectedLauncherType1')
        );
    }
    
    public function unexpectedIndexRouteProvider()
    {
        return array(
            array('InvalidApplication\UnexpectedIndexRoute1\InvalidApplicationUnexpectedIndexRoute1'),
            array('InvalidApplication\UnexpectedIndexRoute2\InvalidApplicationUnexpectedIndexRoute2'),
            array('InvalidApplication\UnexpectedIndexRoute3\InvalidApplicationUnexpectedIndexRoute3')
        );
    }
    
    public function unexpectedIsEligibleReturnTypeProvider()
    {
        return array(
            array('InvalidApplication\UnexpectedIsEligibleForPlatformIndexReturnType1\InvalidApplicationUnexpectedIsEligibleForPlatformIndexReturnType1')
        );
    }
    
    public function validApplicationProvider()
    {
        return array(
            array('ValidApplication\Minimal\ValidApplicationMinimal'),
            array('ValidApplication\TwoLaunchers\ValidApplicationTwoLaunchers'),
            array('ValidApplication\EligibleForIndex1\ValidApplicationEligibleForIndex1')
        );
    }
}
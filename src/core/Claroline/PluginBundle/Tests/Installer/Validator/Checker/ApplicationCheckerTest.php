<?php

namespace Claroline\PluginBundle\Installer\Validator\Checker;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\PluginBundle\Installer\Loader;
use Claroline\PluginBundle\Exception\ValidationException;

class ApplicationCheckerTest extends WebTestCase
{
    /** @var Claroline\PluginBundle\Installer\Validator\Checker\CommonChecker */
    private $checker;
    
    /** @var Claroline\PluginBundle\Installer\Loader */
    private $loader;
    
    public function setUp()
    {
        $container = self::createClient()->getContainer();
        $this->checker = $container->get('claroline.plugin.application_checker');
        $this->loader = $container->get('claroline.plugin.loader');
        $this->overrideDefaultPluginDirectories($this->loader);
    }
    
        /**
     * @dataProvider unexpectedGetLauncherReturnValueProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedApplicationGetLauncherReturnValue($pluginFQCN)
    {
        $this->assertValidationExceptionIsThrown($pluginFQCN, ValidationException::INVALID_APPLICATION_GET_LAUNCHER_METHOD);
    }
    
    /**
     * @dataProvider unexpectedLauncherTypeProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedApplicationLauncherType($pluginFQCN)
    {
        $this->assertValidationExceptionIsThrown($pluginFQCN, ValidationException::INVALID_APPLICATION_LAUNCHER);
    }

    /**
     * @dataProvider noLauncherProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationDoesntProvideLaunchers($pluginFQCN)
    {
        $this->assertValidationExceptionIsThrown($pluginFQCN, ValidationException::INVALID_APPLICATION_LAUNCHER);
    }

    /**
     * @dataProvider unexpectedIndexRouteProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationReturnsAnUnexpectedIndexRoute($pluginFQCN)
    {
        $this->assertValidationExceptionIsThrown($pluginFQCN, ValidationException::INVALID_APPLICATION_INDEX);
    }
    
    /**
     * @dataProvider unexpectedIsEligibleIndexReturnTypeProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationDoesntReturnABooleanInTheIsEligibleForIndexMethod($pluginFQCN)
    {
        $this->assertValidationExceptionIsThrown($pluginFQCN, ValidationException::INVALID_APPLICATION_IS_ELIGIBLE_INDEX_METHOD);
    }
    
    /**
     * @dataProvider unexpectedIsEligibleTargetReturnTypeProvider
     */
    public function testCheckThrowsAnExceptionIfAnApplicationDoesntReturnABooleanInTheIsEligibleForConnectionTargetMethod($pluginFQCN)
    {
        $this->assertValidationExceptionIsThrown($pluginFQCN, ValidationException::INVALID_APPLICATION_IS_ELIGIBLE_TARGET_METHOD);
    }
    
    /**
     * @dataProvider validApplicationProvider
     */
    public function testCheckDoesntThrowAnyExceptionOnValidApplicationArgument($pluginFQCN)
    {
        $plugin = $this->loader->load($pluginFQCN);
        
        try
        {
            $this->checker->check($plugin);
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
            array('Invalid\UnexpectedGetLauncherReturnValue1\InvalidUnexpectedGetLauncherReturnValue1')
        );
    }
    
    public function noLauncherProvider()
    {
        return array(
            array('Invalid\NoLauncher\InvalidNoLauncher')
        );
    }
    
    public function unexpectedLauncherTypeProvider()
    {
        return array(
            array('Invalid\UnexpectedLauncherType1\InvalidUnexpectedLauncherType1')
        );
    }
    
    public function unexpectedIndexRouteProvider()
    {
        return array(
            array('Invalid\UnexpectedIndexRoute1\InvalidUnexpectedIndexRoute1'),
            array('Invalid\UnexpectedIndexRoute2\InvalidUnexpectedIndexRoute2'),
            array('Invalid\UnexpectedIndexRoute3\InvalidUnexpectedIndexRoute3')
        );
    }
    
    public function unexpectedIsEligibleIndexReturnTypeProvider()
    {
        return array(
            array('Invalid\UnexpectedIsEligibleForPlatformIndexReturnType1\InvalidUnexpectedIsEligibleForPlatformIndexReturnType1')
        );
    }
    
    public function unexpectedIsEligibleTargetReturnTypeProvider()
    {
        return array(
            array('Invalid\UnexpectedIsEligibleForConnectionTargetReturnType1\InvalidUnexpectedIsEligibleForConnectionTargetReturnType1')
        );
    }
    
    public function validApplicationProvider()
    {
        return array(
            array('Valid\Basic\ValidBasic'),
            array('Valid\TwoLaunchers\ValidTwoLaunchers'),
            array('Valid\EligibleForIndex1\ValidEligibleForIndex1')
        );
    }
    
    private function overrideDefaultPluginDirectories(Loader $loader)
    {
        $ds = DIRECTORY_SEPARATOR;
        $stubDir = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}stub{$ds}plugin{$ds}";
        $loader->setPluginDirectories(
            array(
                'extension' => "{$stubDir}extension",
                'application' => "{$stubDir}application",
                'tool' => "{$stubDir}tool"
            )
        );
    }
    
    private function assertValidationExceptionIsThrown($pluginFQCN, $exceptionCode)
    {
        $plugin = $this->loader->load($pluginFQCN);
        
        try
        {
            $this->checker->check($plugin);
            $this->fail("No exception thrown.");
        }
        catch (ValidationException $ex)
        {
            $this->assertEquals($exceptionCode, $ex->getCode());
        }
    }
}
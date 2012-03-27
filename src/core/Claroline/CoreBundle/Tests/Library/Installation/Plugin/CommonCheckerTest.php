<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use org\bovigo\vfs\vfsStream;
use Claroline\CoreBundle\Exception\InstallationException;

class CommonCheckerTest extends WebTestCase
{
    /** @var CommonChecker */
    private $checker;
    
    /** @var Loader */
    private $loader;
    
    protected function setUp()
    {
        $container = self::createClient()->getContainer();
        $this->checker = $container->get('claroline.plugin.common_checker');
        $this->loader = $container->get('claroline.plugin.loader');
        $stubDir = $container->getParameter('claroline.stub_plugin_directory');
        $this->overrideDefaultPluginDirectories($stubDir, $this->loader, $this->checker);
    }
    
    /**
     * @dataProvider invalidFQCNProvider
     */
    public function testCheckerThrowsAnExceptionOnInvalidFQCN($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_FQCN);
    }
    
    /**
     * @dataProvider invalidPluginTypeProvider
     */
    public function testCheckerThrowsAnExceptionIfBundleClassDoesntExtendAClarolinePluginSubType($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_PLUGIN_TYPE);
    }

    /**
     * @dataProvider invalidPluginLocationProvider
     */
    public function testCheckerThrowsAnExceptionIfPluginIsNotLocatedInTheAppropriateDirectory($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_PLUGIN_LOCATION);
    }
    
    /**
     * @dataProvider invalidRoutingPrefixProvider
     */
    public function testCheckerThrowsAnExceptionOnInvalidRoutingPrefix($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_ROUTING_PREFIX);
    }
    
    /**
     * @dataProvider invalidAlreadyRegisteredPrefixProvider
     */
    public function testCheckerThrowsAnExceptionIfRoutingPrefixIsAlreadyRegistered($pluginFQCN)
    {
        $pluginRoutingEntry = "FakePluginBundle:\n"
            . "    resource: '@FakePluginBundle/Resources/config/routing.yml'\n"
            . "    prefix: sharedPrefix\n";
        vfsStream::setup('virtual', null, array('routing.yml' => $pluginRoutingEntry));
        $this->checker->setPluginRoutingFilePath(vfsStream::url('virtual/routing.yml'));
       
        $locator = $this->getMockBuilder('\Symfony\Component\HttpKernel\Config\FileLocator')
                ->disableOriginalConstructor()
                ->getMock();
        
        $locator->expects($this->once())
                ->method('locate')
                ->with($this->equalTo("@FakePluginBundle/Resources/config/routing.yml"))
                ->will($this->returnValue("Fake/PluginBundle/Resources/Resources/config/routing.yml"));
        $this->checker->setFileLocator($locator);
        
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_ALREADY_REGISTERED_PREFIX);
    }
    
    /**
     * @dataProvider nonExistentRoutingResourceTypeProvider
     */
    public function testCheckThrowsAnExceptionOnNonExistentRoutingResource($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_ROUTING_PATH);
    }

    /**
     * @dataProvider unexpectedRoutingResourceLocationProvider
     */
    public function testCheckThrowsAnExceptionOnUnexpectedRoutingResourceLocation($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_ROUTING_LOCATION);
    }

    /**
     * @dataProvider nonYamlRoutingResourceProvider
     */
    public function testCheckThrowsAnExceptionOnNonYamlRoutingFile($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_ROUTING_EXTENSION);
    }

    /**
     * @dataProvider unloadableYamlRoutingResourceProvider
     */
    public function testCheckThrowsAnExceptionOnUnloadableYamlRoutingFile($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_YAML_RESOURCE);
    }
    
    /**
     * @dataProvider unexpectedTranslationKeyProvider
     */
    public function testCheckThrowsAnExceptionOnInvalidTranslationKey($pluginFQCN)
    {
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_TRANSLATION_KEY);
    }

    public function testCheckThrowsAnExceptionOnInvalidResourceKey()
    {
        $pluginFQCN = 'Invalid\MissingResourceKey\InvalidMissingResourceKey';
        $plugin = $this->loader->load($pluginFQCN);
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_RESOURCE_KEY);
    }
    
    public function testCheckThrowsAnExceptionOnInvalidResourceValue()
    {
        $pluginFQCN = 'Invalid\UnexpectedResourceValue\InvalidUnexpectedResourceValue';
        $plugin = $this->loader->load($pluginFQCN);
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_RESOURCE_VALUE);
    }
    
    public function testCheckThrowsAnExceptionOnInvalidResourceLocation()
    {
        $pluginFQCN = 'Invalid\UnloadableResourceClass\InvalidUnloadableResourceClass';
        $plugin = $this->loader->load($pluginFQCN);
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_RESOURCE_LOCATION);
    }
    
    public function testCheckThrowsAnExceptionOnInvalidResourceType()
    {
        $pluginFQCN = 'Invalid\UnexpectedResourceType\InvalidUnexpectedResourceType';
        $plugin = $this->loader->load($pluginFQCN);
        $this->assertInstallationExceptionIsThrown($pluginFQCN, InstallationException::INVALID_RESOURCE_TYPE);
    }
    
    /**
     * @dataProvider validPluginProvider
     */
    public function testCheckDoesntThrowAnyExceptionOnValidPluginArgument($pluginFQCN)
    {
        $plugin = $this->loader->load($pluginFQCN);
        
        try
        {
            $this->checker->check($plugin);
            $this->assertTrue(true);
        }
        catch (InstallationException $ex)
        {
            $this->fail("A validation exception was thrown with code {$ex->getCode()}.");
        }
    }
    
    public function invalidFQCNProvider()
    {
        return array(
            array('Invalid\NonConventionalFQCN1\AdditionalNamespaceSegment\InvalidNonConventionalFQCN1'),
            array('Invalid\NonConventionalFQCN2\UnexpectedBundleClassName')
        );
    }
    
    public function invalidPluginTypeProvider()
    {
        return array(
            array('Invalid\ClarolinePluginDirectInheritance\InvalidClarolinePluginDirectInheritance')
        );
    }
    
    public function invalidPluginLocationProvider()
    {
        return array(
            array('Invalid\UnexpectedPluginLocation\InvalidUnexpectedPluginLocation')
        );
    }
    
    public function invalidRoutingPrefixProvider()
    {
        return array(
            array('Invalid\UnexpectedRoutingPrefix1\InvalidUnexpectedRoutingPrefix1'),
            array('Invalid\UnexpectedRoutingPrefix2\InvalidUnexpectedRoutingPrefix2'),
            array('Invalid\UnexpectedRoutingPrefix3\InvalidUnexpectedRoutingPrefix3')
        );
    }
    
    public function invalidAlreadyRegisteredPrefixProvider()
    {
        return array(
            array('Incompatible\AlreadyRegisteredRoutingPrefix\IncompatibleAlreadyRegisteredRoutingPrefix')
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
            array('Valid\Basic\ValidBasic'),
            array('Valid\Simple\ValidSimple'),
            array('Valid\Custom\ValidCustom'),
            array('Valid\WithCustomResources\ValidWithCustomResources')
        );
    }
    
    private function overrideDefaultPluginDirectories($stubDir, Loader $loader, CommonChecker $checker)
    {
        $ds = DIRECTORY_SEPARATOR;
        $pluginDirs = array(
            'extension' => "{$stubDir}{$ds}extension",
            'tool' => "{$stubDir}{$ds}tool"
        );
        
        $loader->setPluginDirectories($pluginDirs);
        $checker->setPluginDirectories($pluginDirs);
    }
    
    private function assertInstallationExceptionIsThrown($pluginFQCN, $exceptionCode)
    {
        $plugin = $this->loader->load($pluginFQCN);
        
        try
        {
            $this->checker->check($plugin);
            $this->fail("No exception thrown.");
        }
        catch (InstallationException $ex)
        {
            $this->assertEquals($exceptionCode, $ex->getCode());
        }
    }
}
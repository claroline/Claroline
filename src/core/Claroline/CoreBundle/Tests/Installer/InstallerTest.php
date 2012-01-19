<?php

namespace InstallerTest;

class StubPlugin extends \Claroline\CoreBundle\AbstractType\ClarolineExtension
{
}

namespace Claroline\CoreBundle\Installer;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use InstallerTest\StubPlugin;

class InstallerTest extends WebTestCase
{
    /** @var Claroline\CoreBundle\Installer\Installer */
    private $installer;
    
    /** @var InstallerTest\StubPlugin */
    private $stubPlugin;
    
    private $mockedLoader;
    private $mockedValidator;
    private $mockedMigrator;
    private $mockedRecorder;
    
    public function setUp()
    {
        $this->installer = self::createClient()->getContainer()->get('claroline.plugin.installer');
        $this->stubPlugin = new StubPlugin();
        $this->initMockedHelpers();
        $this->installer->setLoader($this->mockedLoader);
        $this->installer->setValidator($this->mockedValidator);
        $this->installer->setMigrator($this->mockedMigrator);
        $this->installer->setRecorder($this->mockedRecorder);     
    }
    
    public function testInstallProperlyDelegatesToHelpers()
    {
        $this->mockedRecorder->expects($this->once())
            ->method('isRegistered')
            ->with(get_class($this->stubPlugin))
            ->will($this->returnValue(false));
        $this->mockedLoader->expects($this->once())
            ->method('load')
            ->with(get_class($this->stubPlugin))
            ->will($this->returnValue($this->stubPlugin));
        $this->mockedValidator->expects($this->once())
            ->method('validate')
            ->with($this->stubPlugin);
        $this->mockedMigrator->expects($this->once())
            ->method('install')
            ->with($this->stubPlugin);
        $this->mockedRecorder->expects($this->once())
            ->method('register')
            ->with($this->stubPlugin);
        
        $this->installer->install(get_class($this->stubPlugin));
    }
    
    public function testInstallThrowsAnExceptionIfPluginIsAlreadyRegistered()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\InstallationException');
      
        $pluginFQCN = 'Imaginary\Fake\Plugin';
        
        $this->mockedRecorder->expects($this->once())
            ->method('isRegistered')
            ->with($pluginFQCN)
            ->will($this->returnValue(true));
        
        $this->installer->install($pluginFQCN);
    }
    
    public function testUninstallProperlyDelegatesToHelpers()
    {
        $this->mockedRecorder->expects($this->once())
            ->method('isRegistered')
            ->with(get_class($this->stubPlugin))
            ->will($this->returnValue(true));
        $this->mockedLoader->expects($this->once())
            ->method('load')
            ->with(get_class($this->stubPlugin))
            ->will($this->returnValue($this->stubPlugin));
        $this->mockedRecorder->expects($this->once())
            ->method('unregister')
            ->with($this->stubPlugin);
        $this->mockedMigrator->expects($this->once())
            ->method('remove')
            ->with($this->stubPlugin);
        
        $this->installer->uninstall(get_class($this->stubPlugin));
    }
    
    public function testUninstallThrowsAnExceptionIfPluginIsNotRegistered()
    {
        $this->setExpectedException('Claroline\CoreBundle\Exception\InstallationException');
      
        $pluginFQCN = 'Imaginary\Fake\Plugin';
        
        $this->mockedRecorder->expects($this->once())
            ->method('isRegistered')
            ->with($pluginFQCN)
            ->will($this->returnValue(false));
        
        $this->installer->uninstall($pluginFQCN);
    }
    
    private function initMockedHelpers()
    {
        $this->mockedLoader = $this->getMockBuilder('Claroline\CoreBundle\Installer\Loader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedValidator = $this->getMockBuilder('Claroline\CoreBundle\Installer\Validator\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedMigrator = $this->getMockBuilder('Claroline\CoreBundle\Installer\Migrator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedRecorder = $this->getMockBuilder('Claroline\CoreBundle\Installer\Recorder\Recorder')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
<?php

namespace Claroline\InstallBundle\Service;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\HttpKernel\Kernel;

class CoreInstallerTest extends TestCase
{
    /** @var Kernel */
    private $kernelMock;
    /** @var BundleMigrator */
    private $migratorMock;
    
    /** @var CoreInstaller */
    private $coreInstaller;
    
    protected function setUp() 
    {
        $this->initKernelMock();
        $this->initMigratorMock();
        $this->coreInstaller = new CoreInstaller($this->kernelMock, $this->migratorMock);
    }
    
    private function initKernelMock()
    {
        $this->kernelMock =
                $this->getMockBuilder('\\Symfony\\Component\\HttpKernel\\Kernel')
                     ->disableOriginalConstructor()
                     ->getMock();
            
        $bundles = array(
            new Stubs\DummyBundle('core/FirstCoreBundle', 1),
            new Stubs\DummyBundle('core/SecondCoreBundle', 2),
            new Stubs\DummyBundle('plugin/FirstPluginBundle', 3),
            new Stubs\DummyBundle('foo/FirstFoOBundle', 4),
        );
        
        // Configure the stub.
        $this->kernelMock->expects($this->any())
             ->method('getBundles')
             ->will($this->returnValue($bundles));
    }
    
    private function initMigratorMock()
    {   
        $this->migratorMock =
                $this->getMockBuilder('\\Claroline\\InstallBundle\\Service\\BundleMigrator')
                     ->disableOriginalConstructor()
                     ->getMock();
        
    }
    
    public function testInstallWillCallMigratorOnlyForCorePlugins()
    {
        $this->migratorMock->expects($this->exactly(2))
                ->method('createSchemaForBundle');                
        
        $this->coreInstaller->install();
    }
}

namespace Claroline\InstallBundle\Service\Stubs;

class DummyBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    private $path;
    private $installationIndex;
    
    public function __construct($path, $installationIndex) 
    {
        $this->path = $path;
        $this->installationIndex = $installationIndex;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getInstallationIndex()
    {
        return $this->installationIndex;
    }
}
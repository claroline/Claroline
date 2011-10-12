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
    
    protected function setUp() {
        parent::setUp();
        
        $this->initKernelMock();
        $this->initMigratorMock();
        $this->coreInstaller = 
                new CoreInstaller($this->kernelMock, $this->migratorMock);
    }
    
    private function initKernelMock()
    {
        $this->kernelMock =
                $this->getMockBuilder('\\Symfony\\Component\\HttpKernel\\Kernel')
                     ->disableOriginalConstructor()
                     ->getMock();
        
        
        $bundles = array(
            new Stubs\DummyBundle('core/FirstCoreBundle'),
            new Stubs\DummyBundle('core/SecondCoreBundle'),
            new Stubs\DummyBundle('plugin/FirstPluginBundle'),
            new Stubs\DummyBundle('foo/FirstFoOBundle'),
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
    public function __construct($path) {
        $this->path = $path;
    }
    public function getPath()
    {
        return $this->path;
    }
}
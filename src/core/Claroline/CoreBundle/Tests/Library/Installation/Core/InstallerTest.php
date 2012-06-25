<?php

namespace Claroline\CoreBundle\Library\Installation\Core;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Claroline\CoreBundle\Tests\Stub\Misc\DummyBundle;

class InstallerTest extends TestCase
{
    /** @var Installer */
    private $installer;

    /** @var Symfony\Component\HttpKernel\Kernel */
    private $kernelMock;

    /** @var Claroline\CoreBundle\Library\Installation\BundleMigrator */
    private $migratorMock;

    protected function setUp()
    {
        $this->initKernelMock();
        $this->initMigratorMock();
        $this->installer = new Installer($this->kernelMock, $this->migratorMock);
    }

    public function testInstallWillCallMigratorOnlyForCorePlugins()
    {
        $this->migratorMock->expects($this->exactly(2))
            ->method('createSchemaForBundle');

        $this->installer->install();
    }

    private function initKernelMock()
    {
        $this->kernelMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')
            ->disableOriginalConstructor()
            ->getMock();

        $bundles = array(
            new DummyBundle('core/FirstCoreBundle', 1),
            new DummyBundle('core/SecondCoreBundle', 2),
            new DummyBundle('plugin/FirstPluginBundle', 3),
            new DummyBundle('foo/FirstFooBundle', 4),
        );

        // Configure the stub.
        $this->kernelMock->expects($this->any())
            ->method('getBundles')
            ->will($this->returnValue($bundles));
    }

    private function initMigratorMock()
    {
        $this->migratorMock = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\BundleMigrator')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
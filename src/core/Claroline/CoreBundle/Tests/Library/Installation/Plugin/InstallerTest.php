<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

class InstallerTest extends \PHPUnit_Framework_TestCase
{
    private $plugin;
    private $loader;
    private $validator;
    private $migrator;
    private $recorder;
    private $kernel;
    private $installer;
    private $container;
    private $mappingLoader;
    private $fixtureLoader;

    protected function setUp()
    {
        $this->plugin = $this->getMock('Claroline\CoreBundle\Library\PluginBundle');
        $this->loader = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Loader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->migrator = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Migrator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->recorder = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Recorder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mappingLoader = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\MappingLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fixtureLoader = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\FixtureLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->kernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\KernelInterface');

        $this->installer = new Installer(
            $this->loader,
            $this->validator,
            $this->migrator,
            $this->recorder,
            $this->kernel
        );
    }

    public function testInstallProperlyDelegatesToHelpers()
    {
        $this->recorder->expects($this->once())
            ->method('isRegistered')
            ->with(get_class($this->plugin))
            ->will($this->returnValue(false));
        $this->loader->expects($this->once())
            ->method('load')
            ->with(get_class($this->plugin), 'plugin/path')
            ->will($this->returnValue($this->plugin));
        $this->validator->expects($this->any())
            ->method('getPluginConfiguration')
            ->will($this->returnValue(array()));
        $this->migrator->expects($this->once())
            ->method('install')
            ->with($this->plugin);
        $this->recorder->expects($this->once())
            ->method('register')
            ->with($this->plugin, array());
        $this->kernel->expects($this->once())
            ->method('shutdown');
        $this->kernel->expects($this->once())
            ->method('boot');
        $this->kernel->expects($this->once())
            ->method('getContainer')
            ->will($this->returnValue($this->container));
        $this->container->expects($this->at(0))
            ->method('get')
            ->with('claroline.installation.mapping_loader')
            ->will($this->returnValue($this->mappingLoader));
        $this->container->expects($this->at(1))
            ->method('get')
            ->with('claroline.installation.fixture_loader')
            ->will($this->returnValue($this->fixtureLoader));
        $this->mappingLoader->expects($this->once())
            ->method('registerMapping')
            ->with($this->plugin);
        $this->fixtureLoader->expects($this->once())
            ->method('load')
            ->with($this->plugin);

        $this->installer->install(get_class($this->plugin), 'plugin/path');
    }

    public function testInstallThrowsAnExceptionIfPluginIsAlreadyRegistered()
    {
        $this->setExpectedException('LogicException');

        $pluginFQCN = 'Imaginary\Fake\Plugin';

        $this->recorder->expects($this->once())
            ->method('isRegistered')
            ->with($pluginFQCN)
            ->will($this->returnValue(true));

        $this->installer->install($pluginFQCN, 'plugin/path');
    }

    public function testUninstallProperlyDelegatesToHelpers()
    {
        $this->recorder->expects($this->once())
            ->method('isRegistered')
            ->with(get_class($this->plugin))
            ->will($this->returnValue(true));
        $this->loader->expects($this->once())
            ->method('load')
            ->with(get_class($this->plugin))
            ->will($this->returnValue($this->plugin));
        $this->recorder->expects($this->once())
            ->method('unregister')
            ->with($this->plugin);
        $this->migrator->expects($this->once())
            ->method('remove')
            ->with($this->plugin);

        $this->installer->uninstall(get_class($this->plugin));
    }

    public function testUninstallThrowsAnExceptionIfPluginIsNotRegistered()
    {
        $this->setExpectedException('LogicException');

        $pluginFQCN = 'Imaginary\Fake\Plugin';

        $this->recorder->expects($this->once())
            ->method('isRegistered')
            ->with($pluginFQCN)
            ->will($this->returnValue(false));

        $this->installer->uninstall($pluginFQCN);
    }

    public function testMigrate()
    {
        $this->recorder->expects($this->once())
            ->method('isRegistered')
            ->with(get_class($this->plugin))
            ->will($this->returnValue(true));
        $this->loader->expects($this->once())
            ->method('load')
            ->with(get_class($this->plugin))
            ->will($this->returnValue($this->plugin));
        $this->migrator->expects($this->once())
            ->method('migrate')
            ->with($this->plugin);

        $this->installer->migrate(get_class($this->plugin), '123');
    }
}

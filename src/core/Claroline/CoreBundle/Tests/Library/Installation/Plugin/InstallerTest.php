<?php

namespace Claroline\CoreBundle\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InstallerTest extends WebTestCase
{
    /** @var Installer */
    private $installer;
    private $mockedPlugin;
    private $mockedLoader;
    private $mockedValidator;
    private $mockedMigrator;
    private $mockedRecorder;

    protected function setUp()
    {
        $this->installer = self::createClient()->getContainer()->get('claroline.plugin.installer');
        $this->mockedPlugin = $this->getMock('Claroline\CoreBundle\Library\PluginBundle');
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
            ->with(get_class($this->mockedPlugin))
            ->will($this->returnValue(false));
        $this->mockedLoader->expects($this->once())
            ->method('load')
            ->with(get_class($this->mockedPlugin))
            ->will($this->returnValue($this->mockedPlugin));
        $this->mockedValidator->expects($this->any())
            ->method('getPluginConfiguration')
            ->will($this->returnValue(array()));
        $this->mockedMigrator->expects($this->once())
            ->method('install')
            ->with($this->mockedPlugin);
        $this->mockedRecorder->expects($this->once())
            ->method('register')
            ->with($this->mockedPlugin, array());

        $this->installer->install(get_class($this->mockedPlugin));
    }

    public function testInstallThrowsAnExceptionIfPluginIsAlreadyRegistered()
    {
        $this->setExpectedException('LogicException');

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
            ->with(get_class($this->mockedPlugin))
            ->will($this->returnValue(true));
        $this->mockedLoader->expects($this->once())
            ->method('load')
            ->with(get_class($this->mockedPlugin))
            ->will($this->returnValue($this->mockedPlugin));
        $this->mockedRecorder->expects($this->once())
            ->method('unregister')
            ->with($this->mockedPlugin);
        $this->mockedMigrator->expects($this->once())
            ->method('remove')
            ->with($this->mockedPlugin);

        $this->installer->uninstall(get_class($this->mockedPlugin));
    }

    public function testUninstallThrowsAnExceptionIfPluginIsNotRegistered()
    {
        $this->setExpectedException('LogicException');

        $pluginFQCN = 'Imaginary\Fake\Plugin';

        $this->mockedRecorder->expects($this->once())
            ->method('isRegistered')
            ->with($pluginFQCN)
            ->will($this->returnValue(false));

        $this->installer->uninstall($pluginFQCN);
    }

    private function initMockedHelpers()
    {
        $this->mockedLoader = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Loader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedValidator = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedMigrator = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Migrator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedRecorder = $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\Recorder')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
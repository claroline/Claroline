<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecorderTest extends WebTestCase
{
    /** @var Recorder */
    private $recorder;
    private $mockedPlugin;
    private $mockedDbWriter;
    private $mockedConfigWriter;

    protected function setUp()
    {
        $this->recorder = self::createClient()->getContainer()->get('claroline.plugin.recorder');
        $this->initMockObjects();
        $this->recorder->setConfigurationFileWriter($this->mockedConfigWriter);
        $this->recorder->setDatabaseWriter($this->mockedDbWriter);
    }

    public function testRecorderProperlyDelegatesToWritersOnRegister()
    {
        $this->mockedDbWriter->expects($this->once())
            ->method('insert')
            ->with($this->mockedPlugin);
        $this->mockedConfigWriter->expects($this->once())
            ->method('registerNamespace')
            ->with($this->mockedPlugin->getVendorName());
        $this->mockedConfigWriter->expects($this->once())
            ->method('addInstantiableBundle')
            ->with(get_class($this->mockedPlugin));
        $this->mockedConfigWriter->expects($this->once())
            ->method('importRoutingResources')
            ->with(
                get_class($this->mockedPlugin),
                $this->mockedPlugin->getRoutingResourcesPaths(),
                $this->mockedPlugin->getRoutingPrefix()
            );

        $this->recorder->register($this->mockedPlugin, array());
    }

    public function testRecorderProperlyDelegatesToWritersOnUnregister()
    {
        $this->mockedDbWriter->expects($this->once())
            ->method('delete')
            ->with(get_class($this->mockedPlugin));
        $this->mockedConfigWriter->expects($this->once())
            ->method('removeNamespace')
            ->with($this->mockedPlugin->getVendorName());
        $this->mockedConfigWriter->expects($this->once())
            ->method('removeInstantiableBundle')
            ->with(get_class($this->mockedPlugin));
        $this->mockedConfigWriter->expects($this->once())
            ->method('removeRoutingResources')
            ->with(get_class($this->mockedPlugin));

        $this->recorder->unregister($this->mockedPlugin);
    }

    public function testIsRecordedReturnsExpectedValues()
    {
        $pluginFQCN = get_class($this->mockedPlugin);

        $this->assertFalse($this->recorder->isRegistered($pluginFQCN));

        $this->mockedConfigWriter->expects($this->any())
            ->method('isRecorded')
            ->with($pluginFQCN)
            ->will($this->returnValue(true));
        $this->mockedDbWriter->expects($this->any())
            ->method('isSaved')
            ->with($pluginFQCN)
            ->will($this->returnValue(true));

        $this->assertTrue($this->recorder->isRegistered($pluginFQCN));
    }

    private function initMockObjects()
    {
        $this->mockedPlugin = $this->getMock('Claroline\CoreBundle\Library\PluginBundle');
        $this->mockedConfigWriter =
            $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\ConfigurationFileWriter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockedDbWriter =
            $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\DatabaseWriter')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
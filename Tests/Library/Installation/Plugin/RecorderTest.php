<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

class RecorderTest extends \PHPUnit_Framework_TestCase
{
    private $recorder;
    private $plugin;
    private $dbWriter;

    protected function setUp()
    {
        $this->plugin = $this->getMock('Claroline\CoreBundle\Library\PluginBundle');
        $this->dbWriter =
            $this->getMockBuilder('Claroline\CoreBundle\Library\Installation\Plugin\DatabaseWriter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->recorder = new Recorder($this->dbWriter);
    }

    public function testRecorderProperlyDelegatesToWritersOnRegister()
    {
        $this->dbWriter->expects($this->once())
            ->method('insert')
            ->with($this->plugin);
        $this->recorder->register($this->plugin, array());
    }

    public function testRecorderProperlyDelegatesToWritersOnUnregister()
    {
        $this->dbWriter->expects($this->once())
            ->method('delete')
            ->with(get_class($this->plugin));
        $this->recorder->unregister($this->plugin);
    }

    public function testIsRecordedReturnsExpectedValues()
    {
        $this->dbWriter->expects($this->any())
            ->method('isSaved')
            ->with($this->plugin)
            ->will($this->returnValue(true));
        $this->assertTrue($this->recorder->isRegistered($this->plugin));
    }
}

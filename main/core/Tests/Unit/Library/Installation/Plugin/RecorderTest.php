<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RecorderTest extends MockeryTestCase
{
    private $recorder;
    private $plugin;
    private $dbWriter;

    protected function setUp()
    {
        $this->plugin = $this->mock('Claroline\CoreBundle\Library\DistributionPluginBundle');
        $this->dbWriter = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\DatabaseWriter');
        $this->validator = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\Validator');

        $this->recorder = new Recorder($this->dbWriter, $this->validator);
    }

    public function testRecorderProperlyDelegatesToWritersOnRegister()
    {
        $this->dbWriter->shouldReceive('insert')->once()->with($this->plugin, []);
        $this->validator->shouldReceive('validate')->once()->with($this->plugin);
        $this->validator->shouldReceive('deactivateUpdateMode')->once();
        $this->validator->shouldReceive('getPluginConfiguration')->once()->andReturn([]);
        $this->recorder->register($this->plugin, []);
    }

    public function testRecorderProperlyDelegatesToWritersOnUnregister()
    {
        $this->dbWriter->shouldReceive('delete')->once()->with(get_class($this->plugin));
        $this->recorder->unregister($this->plugin);
    }

    public function testIsRecordedReturnsExpectedValues()
    {
        $this->dbWriter->shouldReceive('isSaved')->andReturn(true);
        $this->assertTrue($this->recorder->isRegistered($this->plugin));
    }
}

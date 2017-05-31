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

class InstallerTest extends MockeryTestCase
{
    private $plugin;
    private $validator;
    private $recorder;
    private $baseInstaller;
    private $installer;

    protected function setUp()
    {
        parent::setUp();

        $this->plugin = $this->mock('Claroline\CoreBundle\Library\PluginBundle');
        $this->validator = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\Validator');
        $this->recorder = $this->mock('Claroline\CoreBundle\Library\Installation\Plugin\Recorder');
        $this->baseInstaller = $this->mock('Claroline\InstallationBundle\Manager\InstallationManager');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->pm = $this->mock('Claroline\CoreBundle\Manager\PluginManager');
        $this->trans = $this->mock('Symfony\Component\Translation\TranslatorInterface');

        $this->installer = new Installer(
            $this->validator,
            $this->recorder,
            $this->baseInstaller,
            $this->om,
            $this->pm,
            $this->trans
        );
    }

    public function testInstallProperlyDelegatesToHelpers()
    {
        $pluginEntity = 'ClarolineFooBundle';

        $this->recorder->shouldReceive('isRegistered')
            ->once()
            ->with($this->plugin)
            ->andReturn(false);
        $this->validator->shouldReceive('validate')
            ->once()
            ->with($this->plugin)
            ->andReturn([]);
        $this->validator->shouldReceive('getPluginConfiguration')
            ->andReturn(['foo' => 'bar']);
        $this->baseInstaller->shouldReceive('install')
            ->once()
            ->with($this->plugin, false);
        $this->recorder->shouldReceive('register')
            ->with($this->plugin, ['foo' => 'bar'])
            ->andReturn($pluginEntity);
        $this->pm->shouldReceive('isReady')
            ->with($pluginEntity)
            ->andReturn(true);
        $this->pm->shouldReceive('isActivatedByDefault')
            ->with($pluginEntity)
            ->andReturn(true);

        $this->installer->install($this->plugin);
    }

    /**
     * @expectedException \LogicException
     */
    public function testInstallThrowsAnExceptionIfPluginIsAlreadyRegistered()
    {
        $this->recorder->shouldReceive('isRegistered')
            ->once()
            ->with($this->plugin)
            ->andReturn(true);
        $this->installer->install($this->plugin);
    }

    public function testUninstallProperlyDelegatesToHelpers()
    {
        $this->recorder->shouldReceive('isRegistered')
            ->once()
            ->with($this->plugin)
            ->andReturn(true);
        $this->baseInstaller->shouldReceive('uninstall')
            ->once()
            ->with($this->plugin);
        $this->recorder->shouldReceive('unregister')
            ->with($this->plugin);

        $this->installer->uninstall($this->plugin);
    }

    /**
     * @expectedException \LogicException
     */
    public function testUninstallThrowsAnExceptionIfPluginIsNotRegistered()
    {
        $this->recorder->shouldReceive('isRegistered')
            ->once()
            ->with($this->plugin)
            ->andReturn(false);
        $this->installer->uninstall($this->plugin);
    }

    public function testUpdate()
    {
        $this->recorder->shouldReceive('isRegistered')
            ->once()
            ->with($this->plugin)
            ->andReturn(true);
        $this->validator->shouldReceive('activeUpdateMode')->once();
        $this->validator->shouldReceive('validate')
            ->once()
            ->with($this->plugin)
            ->andReturn([]);
        $this->validator->shouldReceive('deactivateUpdateMode')->once();
        $this->validator->shouldReceive('getPluginConfiguration')
            ->andReturn(['foo' => 'bar']);
        $this->baseInstaller->shouldReceive('update')
            ->once()
            ->with($this->plugin, '1.0', '2.0');
        $this->recorder->shouldReceive('update')
            ->with($this->plugin, ['foo' => 'bar']);

        $this->installer->update($this->plugin, '1.0', '2.0');
    }
}

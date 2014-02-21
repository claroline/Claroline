<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\HomeImporter;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Widgets\TextImporter;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\ResourceManagerImporter;
use Symfony\Component\Yaml\Yaml;

class TransfertManagerTest extends MockeryTestCase
{
    private $manager;
    private $om;
    private $workspacePropertiesImporter;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');

        //workspace properties
        $this->workspacePropertiesImporter = $this
            ->mock(
                'Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\WorkspacePropertiesImporter',
                array($this->om)
            );

        $this->manager = new TransfertManager();
        $homeImporter = new HomeImporter();
        $textImporter = new TextImporter();
        $resourceImporter = new ResourceManagerImporter();
        $this->manager->addImporter($homeImporter);
        $this->manager->addImporter($textImporter);
        $this->manager->addImporter($resourceImporter);
        $this->manager->addImporter($this->workspacePropertiesImporter);
    }

    public function testValidateGoesWell()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__.'/../../Stub/transfert/valid/full';
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));

        //workspace properties
        $properties['properties'] = $data['properties'];
        $this->workspacePropertiesImporter->shouldReceive('validate')->with($properties)->andReturn(true);
        $this->workspacePropertiesImporter->shouldReceive('getName')->andReturn('workspace_properties');
        $this->workspacePropertiesImporter->shouldReceive('setRootPath')->once()->with($path);

        $this->manager->validate($path);
    }
} 
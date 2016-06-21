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
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Symfony\Component\Yaml\Yaml;

class TransfertManagerTest extends MockeryTestCase
{
    private $manager;
    private $om;

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

        //users importer
        $this->usersImporter = $this
            ->mock(
                'Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\UsersImporter',
                array($this->om)
            );

        //groups importer
        $this->groupsImporter = $this
            ->mock(
                'Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsImporter',
                array($this->om)
            );

        //roles importer
        $this->rolesImporter = $this
            ->mock(
                'Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\RolesImporter',
                array($this->om)
            );

        //roles importer
        $this->toolsImporter = $this
            ->mock(
                'Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\ToolsImporter',
                array($this->om)
            );

        //roles importer
        $this->ownerImporter = $this
            ->mock(
                'Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\OwnerImporter',
                array($this->om)
            );

        $this->manager = new TransferManager();
        $homeImporter = new HomeImporter();
        $textImporter = new TextImporter();
        $resourceImporter = new ResourceManagerImporter();

        $this->manager->addImporter($homeImporter);
        $this->manager->addImporter($textImporter);
        $this->manager->addImporter($resourceImporter);
        $this->manager->addImporter($this->workspacePropertiesImporter);
        $this->manager->addImporter($this->usersImporter);
        $this->manager->addImporter($this->rolesImporter);
        $this->manager->addImporter($this->groupsImporter);
        $this->manager->addImporter($this->toolsImporter);
        $this->manager->addImporter($this->ownerImporter);
    }

    public function testValidateGoesWell()
    {
        //@todo mock merger
//        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__.'/../../Stub/transfert/valid/full';
        $resolver = new Resolver($path);
        $data = $resolver->resolve();
//        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));

        //workspace properties
        $properties['properties'] = $data['properties'];
        $this->workspacePropertiesImporter->shouldReceive('validate')->with($properties)->andReturn(true);
        $this->workspacePropertiesImporter->shouldReceive('getName')->andReturn('workspace_properties');
        $this->workspacePropertiesImporter->shouldReceive('setRootPath')->once()->with($path);
        $this->workspacePropertiesImporter->shouldReceive('setConfiguration')->once()->with($data);

        //owner
        $owner['owner'] = $data['members']['owner'];
        $this->ownerImporter->shouldReceive('validate')->with($owner)->andReturn(true);
        $this->ownerImporter->shouldReceive('getName')->andReturn('owner');
        $this->ownerImporter->shouldReceive('setRootPath')->once()->with($path);
        $this->ownerImporter->shouldReceive('setConfiguration')->once()->with($data);

        //users
        //@todo check what does the validate get
        $this->usersImporter->shouldReceive('validate')->andReturn(true);
        $this->usersImporter->shouldReceive('getName')->andReturn('user');
        $this->usersImporter->shouldReceive('setRootPath')->once()->with($path);
        $this->usersImporter->shouldReceive('setConfiguration')->once()->with($data);

        //roles
        //@todo check what does the validate get
        $this->rolesImporter->shouldReceive('validate')->andReturn(true);
        $this->rolesImporter->shouldReceive('getName')->andReturn('roles');
        $this->rolesImporter->shouldReceive('setRootPath')->once()->with($path);
        $this->rolesImporter->shouldReceive('setConfiguration')->once()->with($data);

        //groups
        //@todo check what does the validate get
        $this->groupsImporter->shouldReceive('validate')->andReturn(true);
        $this->groupsImporter->shouldReceive('getName')->andReturn('groups');
        $this->groupsImporter->shouldReceive('setRootPath')->once()->with($path);
        $this->groupsImporter->shouldReceive('setConfiguration')->once()->with($data);

        //groups
        //@todo check what does the validate get
        $this->toolsImporter->shouldReceive('validate')->andReturn(true);
        $this->toolsImporter->shouldReceive('getName')->andReturn('tools');
        $this->toolsImporter->shouldReceive('setRootPath')->once()->with($path);
        $this->toolsImporter->shouldReceive('setConfiguration')->once()->with($data);

        $this->manager->validate($path);
    }
}

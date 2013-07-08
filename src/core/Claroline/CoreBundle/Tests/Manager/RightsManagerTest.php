<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RightsManagerTest extends MockeryTestCase
{
    private $writer;
    private $rightsRepo;
    private $resourceRepo;
    private $roleRepo;
    private $resourceTypeRepo;
    private $translator;

    public function setUp()
    {
        parent::setUp();

        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->translator = m::mock('Symfony\Component\Translation\Translator');
    }

//    public function testAddMissingForDescendants()
//    {
//        $manager = $this->getManager();
//
//        $role = m::mock('Claroline\CoreBundle\Entity\Role');
//        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
//        $descendant1 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
//        $descendant2 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
//        $rightsParent = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
//        $rightsDescendant1 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
//        $rightsDescendant2 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
//        $rightsParent->shouldReceive('getResource')->andReturn($resource );
//        $rightsDescendant2->shouldReceive('getResource')->andReturn($descendant2);
//
//        $this->rightsRepo
//            ->shouldReceive('findRecursiveByResourceAndRole')
//            ->once()
//            ->with($resource , $role)
//            ->andReturn(array($rightsParent, $rightsDescendant2));
//
//        $this->resourceRepo
//            ->shouldReceive('findDescendants')
//            ->once()
//            ->with($resource , true)
//            ->andReturn(array($resource, $descendant1, $descendant2));
//
//        $this->writer
//            ->shouldReceive('create')
//            ->once()
//            ->andReturn($rightsDescendant1);
//
//
//        $results = $manager->addMissingForDescendants($role, $resource);
//        $expectedResults = array($rightsDescendant1, $rightsDescendant2, $rightsParent);
//
//        var_dump(count($results));
//        $this->assertEquals($expectedResults, $results);
//    }
//
    public function testCreate()
    {
        $manager = $this->getManager(array('getEntity'));
        $newRights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $manager->shouldReceive('getEntity')->andReturn($rights);
        $resource = new \Claroline\CoreBundle\Entity\AbstractResource\Directory();
        $role = new \Claroline\CoreBundle\Entity\Role();
    }

    public function testEditPerms()
    {
        $manager = $this->getManager(array('getOneByRoleAndResource', 'setPermissions'));

        $perms = array(
            'canCopy' => true,
            'canOpen' => false,
            'canDelete' => true,
            'canEdit' => false,
            'canExport' => true
        );

        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $rights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $this->writer->shouldReceive('suspendFlush')->once();
        $manager->shouldReceive('getOneByRoleAndResource')->once()->with($role, $resource)->andReturn($rights);
        $manager->shouldReceive('setPermissions')->once()->with($rights, $perms);
        $this->writer->shouldReceive('update')->once();
        $this->writer->shouldReceive('forceFlush')->once();
        $manager->editPerms($perms, $role, $resource, false);
    }

    public function testEditCreationRights()
    {
        $manager = $this->getManager(array('getOneByRoleAndResource', 'setPermissions'));

        $types = array(
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType()
        );

        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $rights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $this->writer->shouldReceive('suspendFlush')->once();
        $manager->shouldReceive('getOneByRoleAndResource')->once()->with($role, $resource)->andReturn($rights);
        $rights->shouldReceive('setCreatableResourceTypes')->once()->with($types);
        $this->writer->shouldReceive('update')->once();
        $this->writer->shouldReceive('forceFlush')->once();
        $manager->editCreationRights($types, $role, $resource, false);
    }

    public function testCopy()
    {
        $manager = $this->getManager(array('getEntity'));
        $originalRights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $newRights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $original = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $role = new \Claroline\CoreBundle\Entity\Role();

        $manager->shouldReceive('getEntity')->once()->andReturn($newRights);

        $this->rightsRepo
            ->shouldReceive('findBy')
            ->once()
            ->with(array('resource' => $original))
            ->andReturn(array($originalRights));

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $resource->shouldReceive('getResourceType->getName')->once()->andReturn('directory');
        $originalRights->shouldReceive('getCreatableResourceTypes->toArray')->once()->andReturn(array());
        $originalRights->shouldReceive('getRole')->once()->andReturn($role);
        $newRights->shouldReceive('setRole')->once()->with($role);
        $newRights->shouldReceive('setResource')->once()->with($resource);
        $newRights->shouldReceive('setRightsFrom')->once()->with($originalRights);
        $newRights->shouldReceive('setCreatableResourceTypes')->once()->with(array());
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->writer->shouldReceive('forceFlush')->once();
        $this->writer->shouldReceive('create')->once()->with(anInstanceOf('Claroline\CoreBundle\Entity\Resource\ResourceRights'));

        $manager->copy($original, $resource);
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new RightsManager(
                $this->rightsRepo,
                $this->resourceRepo,
                $this->roleRepo,
                $this->resourceTypeRepo,
                $this->writer,
                $this->translator
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return m::mock(
                'Claroline\CoreBundle\Manager\RightsManager' . $stringMocked,
                array(
                    $this->rightsRepo,
                    $this->resourceRepo,
                    $this->roleRepo,
                    $this->resourceTypeRepo,
                    $this->writer,
                    $this->translator
                )
            );
        }
    }
}
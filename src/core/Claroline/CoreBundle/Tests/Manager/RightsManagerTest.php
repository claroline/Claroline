<?php

namespace Claroline\CoreBundle\Manager;

use Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RightsManagerTest extends MockeryTestCase
{
    private $rightsRepo;
    private $resourceRepo;
    private $roleManager;
    private $roleRepo;
    private $resourceTypeRepo;
    private $translator;
    private $om;
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->translator = m::mock('Symfony\Component\Translation\Translator');
        $this->dispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    public function testUpdateRightsTree()
    {
        $manager = $this->getManager();

        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $descendantA = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $descendantB = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $rightsParent = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendantA = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendantB = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsParent->shouldReceive('getResource')->andReturn($resource);
        $rightsDescendantB->shouldReceive('getResource')->andReturn($descendantB);
        $rightsParent->shouldReceive('getRole')->andReturn($role);
        $rightsDescendantB->shouldReceive('getRole')->andReturn($role);

        $this->rightsRepo
            ->shouldReceive('findRecursiveByResourceAndRole')
            ->once()
            ->with($resource, $role)
            ->andReturn(array($rightsParent, $rightsDescendantB));

        $this->resourceRepo
            ->shouldReceive('findDescendants')
            ->once()
            ->with($resource, true)
            ->andReturn(array($resource, $descendantA, $descendantB));

        $this->om->shouldReceive('factory')->once()->andReturn($rightsDescendantA);
        $rightsDescendantA->shouldReceive('setRole')->once()->with($role);
        $rightsDescendantA->shouldReceive('setResource')->once()->with($descendantA);
        $this->om->shouldReceive('persist')->once()->with($rightsDescendantA);
        $this->om->shouldReceive('flush')->once();

        $results = $manager->updateRightsTree($role, $resource);
        $this->assertEquals(3, count($results));
    }

    public function testNonRecursiveCreate()
    {
        $manager = $this->getManager(array('getEntity', 'setPermissions'));

        $perms = array(
            'canCopy' => true,
            'canOpen' => false,
            'canDelete' => true,
            'canEdit' => false,
            'canExport' => true
        );

        $types = array(
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType()
        );
        $newRights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $this->om->shouldReceive('factory')->with('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->andReturn($newRights);
        $resource = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $newRights->shouldReceive('setRole')->once()->with($role);
        $newRights->shouldReceive('setResource')->once()->with($resource);
        $newRights->shouldReceive('setCreatableResourceTypes')->once()->with($types);
        $manager->shouldReceive('setPermissions')->once()->with($newRights, $perms);
        $this->om->shouldReceive('persist')->once()->with($newRights);
        $this->om->shouldReceive('flush')->once();
        $manager->create($perms, $role, $resource, false, $types);
    }

    public function testEditPerms()
    {
        $manager = $this->getManager(array('getOneByRoleAndResource', 'setPermissions', 'logChangeSet'));

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
        $manager->shouldReceive('getOneByRoleAndResource')->once()->with($role, $resource)->andReturn($rights);
        $manager->shouldReceive('setPermissions')->once()->with($rights, $perms);
        $this->om->shouldReceive('persist')->once()->with($rights);
        $manager->shouldReceive('logChangeSet')->once()->with($rights);

        $manager->editPerms($perms, $role, $resource, false);
    }

    public function testEditCreationRights()
    {
        $manager = $this->getManager(array('getOneByRoleAndResource', 'setPermissions', 'logChangeSet'));

        $types = array(
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType()
        );

        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $rights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('getOneByRoleAndResource')->once()->with($role, $resource)->andReturn($rights);
        $rights->shouldReceive('setCreatableResourceTypes')->once()->with($types);
        $this->om->shouldReceive('persist')->once()->with($rights);
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('logChangeSet')->once()->with($rights);

        $manager->editCreationRights($types, $role, $resource, false);
    }

    public function testCopy()
    {
        $manager = $this->getManager(array('create'));
        $originalRights = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $original = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $role = new \Claroline\CoreBundle\Entity\Role();

        $perms = array(
            'canCopy' => true,
            'canOpen' => false,
            'canDelete' => true,
            'canEdit' => false,
            'canExport' => true
        );

        $this->rightsRepo
            ->shouldReceive('findBy')
            ->once()
            ->with(array('resource' => $original))
            ->andReturn(array($originalRights));

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $originalRights->shouldReceive('getCreatableResourceTypes->toArray')->once()->andReturn(array());
        $originalRights->shouldReceive('getRole')->once()->andReturn($role);
        $originalRights->shouldReceive('getPermissions')->once()->andReturn($perms);
        $manager->shouldReceive('create')->once()->with($perms, $role, $resource, false, array());
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();

        $manager->copy($original, $resource);
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceRights')
            ->andReturn($this->rightsRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\AbstractResource')
            ->andReturn($this->resourceRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')
            ->andReturn($this->roleRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceType')
            ->andReturn($this->resourceTypeRepo);

        if (count($mockedMethods) === 0) {
            return new RightsManager(
                $this->translator,
                $this->om,
                $this->dispatcher,
                $this->roleManager
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
                    $this->translator,
                    $this->om,
                    $this->dispatcher,
                    $this->roleManager
                )
            );
        }
    }
}

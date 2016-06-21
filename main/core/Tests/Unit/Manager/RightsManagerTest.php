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

use Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RightsManagerTest extends MockeryTestCase
{
    private $rightsRepo;
    private $maskManager;
    private $resourceNodeRepo;
    private $roleManager;
    private $roleRepo;
    private $resourceTypeRepo;
    private $translator;
    private $om;
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->rightsRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->maskManager = $this->mock('Claroline\CoreBundle\Manager\MaskManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->resourceNodeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceNodeRepository');
        $this->roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->resourceTypeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->dispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    public function testUpdateRightsTree()
    {
        $manager = $this->getManager();

        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $descendantA = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $descendantB = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $rightsParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendantA = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendantB = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsParent->shouldReceive('getResourceNode')->andReturn($node);
        $rightsDescendantB->shouldReceive('getResourceNode')->andReturn($descendantB);
        $rightsParent->shouldReceive('getRole')->andReturn($role);
        $rightsDescendantB->shouldReceive('getRole')->andReturn($role);

        $this->rightsRepo
            ->shouldReceive('findRecursiveByResourceAndRole')
            ->once()
            ->with($node, $role)
            ->andReturn(array($rightsParent, $rightsDescendantB));

        $this->resourceNodeRepo
            ->shouldReceive('findDescendants')
            ->once()
            ->with($node, true)
            ->andReturn(array($node, $descendantA, $descendantB));

        $this->om->shouldReceive('factory')->once()->andReturn($rightsDescendantA);
        $rightsDescendantA->shouldReceive('setRole')->once()->with($role);
        $rightsDescendantA->shouldReceive('setResourceNode')->once()->with($descendantA);
        $this->om->shouldReceive('persist')->once()->with($rightsDescendantA);
        $this->om->shouldReceive('flush')->once();

        $results = $manager->updateRightsTree($role, $node);
        $this->assertEquals(3, count($results));
    }

    public function testNonRecursiveCreate()
    {
        $manager = $this->getManager(array('getEntity', 'setPermissions'));

        $perms = array(
            'copy' => true,
            'open' => false,
            'delete' => true,
            'edit' => false,
            'export' => true,
        );

        $types = array(
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
        );
        $newRights = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $this->om->shouldReceive('factory')->with('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->andReturn($newRights);
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $newRights->shouldReceive('setRole')->once()->with($role);
        $newRights->shouldReceive('setResourceNode')->once()->with($node);
        $newRights->shouldReceive('setCreatableResourceTypes')->once()->with($types);
        $manager->shouldReceive('setPermissions')->once()->with($newRights, $perms);
        $this->om->shouldReceive('persist')->once()->with($newRights);
        $this->om->shouldReceive('flush')->once();
        $manager->create($perms, $role, $node, false, $types);
    }

    public function testEditPerms()
    {
        $manager = $this->getManager(array('getOneByRoleAndResource', 'setPermissions', 'logChangeSet'));

        $perms = array(
            'copy' => true,
            'open' => false,
            'delete' => true,
            'edit' => false,
            'export' => true,
        );

        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $rights = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $manager->shouldReceive('getOneByRoleAndResource')->once()->with($role, $node)->andReturn($rights);
        $manager->shouldReceive('setPermissions')->once()->with($rights, $perms);
        $this->om->shouldReceive('persist')->once()->with($rights);
        $manager->shouldReceive('logChangeSet')->once()->with($rights);

        $manager->editPerms($perms, $role, $node, false);
    }

    public function testEditCreationRights()
    {
        $manager = $this->getManager(array('getOneByRoleAndResource', 'setPermissions', 'logChangeSet'));

        $types = array(
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
            new \Claroline\CoreBundle\Entity\Resource\ResourceType(),
        );

        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $rights = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('getOneByRoleAndResource')->once()->with($role, $node)->andReturn($rights);
        $rights->shouldReceive('setCreatableResourceTypes')->once()->with($types);
        $this->om->shouldReceive('persist')->once()->with($rights);
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('logChangeSet')->once()->with($rights);

        $manager->editCreationRights($types, $role, $node, false);
    }

    public function testCopy()
    {
        $manager = $this->getManager(array('create'));
        $originalRights = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $newRights = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $original = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $role = new \Claroline\CoreBundle\Entity\Role();

        $perms = array(
            'copy' => true,
            'open' => false,
            'delete' => true,
            'edit' => false,
            'export' => true,
        );

        $this->rightsRepo
            ->shouldReceive('findBy')
            ->once()
            ->with(array('resourceNode' => $original))
            ->andReturn(array($originalRights));

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $originalRights->shouldReceive('getCreatableResourceTypes->toArray')->once()->andReturn(array());
        $originalRights->shouldReceive('getRole')->once()->andReturn($role);
        $originalRights->shouldReceive('getMask')->once()->andReturn(123);
        $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->andReturn($newRights);
        $newRights->shouldReceive('setResourceNode')->once()->with($resource);
        $newRights->shouldReceive('setRole')->once()->with($role);
        $newRights->shouldReceive('setMask')->once()->with(123);
        $newRights->shouldReceive('setCreatableResourceTypes')->once()->with(array());
        $this->om->shouldReceive('persist')->once()->with($newRights);
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();

        $manager->copy($original, $resource);
    }

    public function testSetPermissions()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $type = new \Claroline\CoreBundle\Entity\Resource\ResourceType();
        $rights = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rights->shouldReceive('getResourceNode->getResourceType')->once()->andReturn($type);

        $perms = array(
            'copy' => true,
            'open' => false,
            'delete' => true,
            'edit' => false,
            'export' => true,
        );

        $this->maskManager->shouldReceive('encodeMask')->once()->with($perms, $type)->andReturn(123);
        $rights->shouldReceive('setMask')->once()->with(123);

        $this->assertEquals($rights, $this->getManager()->setPermissions($rights, $perms));
    }

    public function testAddRolesToPermsArray()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $baseRoles = array($role);
        $perms = array(
            'ROLE_WS_MANAGER' => array('perms' => 'perms'),
        );

        $this->roleManager->shouldReceive('getRoleBaseName')
            ->with('ROLE_WS_MANAGER_GUID')
            ->andReturn('ROLE_WS_MANAGER');
        $role->shouldReceive('getName')->andReturn('ROLE_WS_MANAGER_GUID');

        $result = array('ROLE_WS_MANAGER' => array('perms' => 'perms', 'role' => $role));
        $this->assertEquals($result, $this->getManager()->addRolesToPermsArray($baseRoles, $perms));
    }

    public function testGetOneExistingByRoleAndResource()
    {
        $role = new \Claroline\CoreBundle\Entity\Role();
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $rr = new \Claroline\CoreBundle\Entity\Resource\ResourceRights();

        $this->rightsRepo->shouldReceive('findOneBy')->with(array('resourceNode' => $node, 'role' => $role))
            ->once()->andReturn($rr);

        $this->assertEquals($rr, $this->getManager()->getOneByRoleAndResource($role, $node));
    }

    public function testGetOneFictiveByRoleAndResource()
    {
        $role = new \Claroline\CoreBundle\Entity\Role();
        $resource = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $rr = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');

        $this->rightsRepo->shouldReceive('findOneBy')->with(array('resourceNode' => $resource, 'role' => $role))
            ->once()->andReturn(null);

        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->andReturn($rr);

        $rr->shouldReceive('setRole')->once()->with($role);
        $rr->shouldReceive('setResourceNode')->once()->with($resource);

        $this->assertEquals($rr, $this->getManager()->getOneByRoleAndResource($role, $resource));
    }

    public function testGetCreatableTypes()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($role);
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $types = array(array('name' => 'directory'));
        $this->rightsRepo->shouldReceive('findCreationRights')->once()
            ->with($roles, $node)->andReturn($types);

        $this->translator->shouldReceive('trans')->once()->with('directory', array(), 'resource')
            ->andReturn('dossier');

        $res = array('directory' => 'dossier');

        $this->assertEquals($res, $this->getManager()->getCreatableTypes($roles, $node));
    }

    public function testRecursiveCreation()
    {
        $manager = $this->getManager(array('updateRightsTree', 'setPermissions'));
        $role = new \Claroline\CoreBundle\Entity\Role();
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $rr = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $resourceRights = array($rr);

        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('updateRightsTree')->once()->with($role, $node)->andReturn($resourceRights);
        $manager->shouldReceive('setPermissions')->once()->with($rr, array());
        $rr->shouldReceive('setCreatableResourceTypes')->once()->with(array());
        $this->om->shouldReceive('persist')->once()->with($rr);
        $this->om->shouldReceive('endFlushSuite')->once();

        $manager->recursiveCreation(array(), $role, $node, array());
    }

    public function testLogChangeSet()
    {
        $uow = $this->mock('Doctrine\ORM\UnitOfWork');
        $rr = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $role = new \Claroline\CoreBundle\Entity\Role();
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $rr->shouldReceive('getRole')->once()->andReturn($role);
        $rr->shouldReceive('getResourceNode')->once()->andReturn($node);
        $this->om->shouldReceive('getUnitOfWork')->andReturn($uow);
        $uow->shouldReceive('computeChangeSets')->once();
        $uow->shouldReceive('getEntityChangeSet')->once()->with($rr)->andReturn(array());
        $this->dispatcher->shouldReceive('dispatch')->once()
            ->with('log', 'Log\LogWorkspaceRoleChangeRight', array($role, $node, array()));
        $this->getManager()->logChangeSet($rr);
    }

    public function testGetNonAdminRights()
    {
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $this->rightsRepo->shouldReceive('findNonAdminRights')->once()->with($node)->andReturn(array());
        $this->assertEquals(array(), $this->getManager()->getNonAdminRights($node));
    }

    public function testGetResourceTypes()
    {
        $this->resourceTypeRepo->shouldReceive('findAll')->once()->andReturn(array());
        $this->assertEquals(array(), $this->getManager()->getResourceTypes());
    }

    public function testGetMaximumRights()
    {
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);

        $this->rightsRepo->shouldReceive('findMaximumRights')->once()->with($roles, $node)->andReturn(array());
        $this->assertEquals(array(), $this->getManager()->getMaximumRights($roles, $node));
    }

    public function testGetCreationRights()
    {
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);

        $this->rightsRepo->shouldReceive('findCreationRights')->once()->with($roles, $node)->andReturn(array());
        $this->assertEquals(array(), $this->getManager()->getCreationRights($roles, $node));
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceRights')
            ->andReturn($this->rightsRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceNode')
            ->andReturn($this->resourceNodeRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')
            ->andReturn($this->roleRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceType')
            ->andReturn($this->resourceTypeRepo);

        if (count($mockedMethods) === 0) {
            return new RightsManager(
                $this->translator,
                $this->om,
                $this->dispatcher,
                $this->roleManager,
                $this->maskManager
            );
        } else {
            $stringMocked = '[';
            $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Manager\RightsManager'.$stringMocked,
                array(
                    $this->translator,
                    $this->om,
                    $this->dispatcher,
                    $this->roleManager,
                    $this->maskManager,
                )
            );
        }
    }
}

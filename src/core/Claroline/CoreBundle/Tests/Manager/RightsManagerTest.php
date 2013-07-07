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

    public function setUp()
    {
        parent::setUp();

        $this->markTestSkipped('This test case is completely broken');

        $this->writer = m::mock('Claroline\CoreBundle\Writer\RightsWriter');
        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
    }

    public function testAddMissingForDescendants()
    {
        $manager = $this->getManager();

        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $descendant1 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $descendant2 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $rightsParent = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendant1 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendant2 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsParent->shouldReceive('getResource')->andReturn($resource );
        $rightsDescendant2->shouldReceive('getResource')->andReturn($descendant2);

        $this->rightsRepo
            ->shouldReceive('findRecursiveByResourceAndRole')
            ->once()
            ->with($resource , $role)
            ->andReturn(array($rightsParent, $rightsDescendant2));

        $this->resourceRepo
            ->shouldReceive('findDescendants')
            ->once()
            ->with($resource , true)
            ->andReturn(array($resource, $descendant1, $descendant2));

        $this->writer
            ->shouldReceive('create')
            ->once()
            ->andReturn($rightsDescendant1);


        $results = $manager->addMissingForDescendants($role, $resource);
        $expectedResults = array($rightsParent, $rightsDescendant1, $rightsDescendant2);
        $this->assertEquals($expectedResults, $results);
    }

    public function testCreate()
    {

    }

    public function testEdit()
    {
        $creations = array();
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
        $this->rightsRepo->shouldReceive('findOneBy')->once()->andReturn($rights);
        $this->writer->shouldReceive('edit')->once()->with($rights, $perms, $creations)->andReturn($rights);
        $this->getManager()->edit($resource, $role, $perms, $creations);
    }

    public function testCopy()
    {
        $rights1 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rights2 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $original = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $this->rightsRepo
            ->shouldReceive('findBy')
            ->once()
            ->with(array('resource' => $original))
            ->andReturn(array($rights1, $rights2));
        $this->writer->shouldReceive('createFrom')->times(2);
        $this->getManager()->copy($original, $resource);
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new RightsManager(
                $this->writer,
                $this->rightsRepo,
                $this->resourceRepo,
                $this->roleRepo,
                $this->resourceTypeRepo
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
                    $this->writer,
                    $this->rightsRepo,
                    $this->resourceRepo,
                    $this->roleRepo,
                    $this->resourceTypeRepo
                )
            );
        }
    }
}
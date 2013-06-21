<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RightsManagerTest extends MockeryTestCase
{
    private $writer;
    private $rightsRepo;
    private $resourceRepo;

    public function setUp()
    {
        parent::setUp();

        $this->writer = m::mock('Claroline\CoreBundle\Writer\RightsWriter');
        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
    }

    public function testaddMissingForDescendants()
    {
        $manager = $this->getManager();

        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $descendant1 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $descendant2 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');

        $rightsParent = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendant1 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');
        $rightsDescendant2 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceRights');

        $rightsParent->shouldReceive('getResource')->once()->andReturn($resource );
        $rightsDescendant2->shouldReceive('getResource')->once()->andReturn($descendant2);

        $this->rightsRepo
            ->shouldReceive('findRecursiveByResourceAndRole')
            ->once()
            ->with($resource , $role)
            ->andReturn(array($rightsParent, $rightsDescendant2));

        $this->resourceRepo
            ->shouldReceive('findDescendants')
            ->once()
            ->with($resource , true)
            ->andReturn(array($parent, $descendant1, $descendant2));

        $this->writer
            ->shouldReceive('create')
            ->once()
            ->with($manager->getFalsePermissions(), array(), $descendant1, $role)
            ->andReturn($rightsDescendant1);

        //~
        $results = $manager->addMissingForDescendants($role, $resource );
        $expectedResults =
        array(

        );
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new RightsManager(
                $this->writer,
                $this->rightsRepo,
                $this->resourceRepo
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
                    $this->resourceRepo
                )
            );
        }
    }
}
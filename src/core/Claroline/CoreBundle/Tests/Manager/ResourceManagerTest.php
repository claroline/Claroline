<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ResourceManagerTest extends MockeryTestCase
{
    private $writer;
    private $resourceRepo;
    private $rightsManager;
    private $resourceTypeRepo;
    private $iconManager;
    private $rightsRepo;

    public function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\CoreBundle\Writer\ResourceWriter');
        $this->rightsManager = m::mock('Claroline\CoreBundle\Manager\RightsManager');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->iconManager = m::mock('Claroline\CoreBundle\Manager\IconManager');
    }

    /**
     * @dataProvider uniqueNameProvider
     */
    public function testGetUniqueName($child1Name, $child2Name, $generatedName)
    {
        $manager = $this->getManager(array('getSiblings'));
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $child1 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $child2 = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $manager->shouldReceive('getSiblings')->once()->andReturn(array($child1, $child2));
        $resource->shouldReceive('getName')->once()->andReturn('uniquename.txt');
        $child1->shouldReceive('getName')->once()->andReturn($child1Name);
        $child2->shouldReceive('getName')->once()->andReturn($child2Name);

        $uniquename = $manager->getUniqueName($resource);
        $this->assertEquals($uniquename, $generatedName);
    }

    public function testGetSiblings()
    {
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $parent->shouldReceive('getChildren')->once();
        $this->getManager()->getSiblings($parent);

        $this->resourceRepo->shouldReceive('findBy')->once()->with(array('parent' => null));
        $this->getManager()->getSiblings(null);
    }

    /**
     * @dataProvider parentAsArrayProvider
     */
    public function testSameParents($parents, $result)
    {
        $this->assertEquals($result, $this->getManager()->sameParents($parents));
    }

    public function testFindAndSortChildren()
    {
        $resources = array(
            array('previous_id' => 1, 'id' => 2),
            array('previous_id' => null, 'id' => 1),
            array('previous_id' => 3, 'id' => 4),
            array('previous_id' => 2, 'id' => 3),
        );

        $result = array(
            array('previous_id' => null, 'id' => 1),
            array('previous_id' => 1, 'id' => 2),
            array('previous_id' => 2, 'id' => 3),
            array('previous_id' => 3, 'id' => 4),
        );

        $parent = $parent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $this->resourceRepo->shouldReceive('findChildren')->once()->andReturn($resources);
        $sorted = $this->getManager()->findAndSortChildren($parent);
        $this->assertEquals($sorted, $result);
    }

    public function testSort()
    {
        $fullSort = array(
            array('previous_id' => null, 'id' => 1),
            array('previous_id' => 1, 'id' => 2),
            array('previous_id' => 2, 'id' => 3),
            array('previous_id' => 3, 'id' => 4),
        );

        $resources = array(
            array('previous_id' => 2, 'id' => 3, 'parent_id' => 42),
            array('previous_id' => null, 'id' => 1)
        );

        $result = array(
            array('previous_id' => null, 'id' => 1),
            array('previous_id' => 2, 'id' => 3, 'parent_id' => 42)
        );

        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $this->resourceRepo->shouldReceive('find')->once()->andReturn($parent);
        $manager = $this->getManager(array('sameParents', 'findAndSortChildren'));
        $manager->shouldReceive('sameParents')->once()->andReturn(true);
        $manager->shouldReceive('findAndSortChildren')->once()->andReturn($fullSort);
        $sorted = $manager->sort($resources);
        $this->assertEquals($sorted, $result);
    }

    public function parentAsArrayProvider()
    {
        return array(
            array(array(array('parent_id' => 1), array('parent_id' => 2)), false),
            array(array(array('parent_id' => 1), array('parent_id' => 1)), true)
        );
    }

    public function uniqueNameProvider()
    {
        return array(
            array('uniquename.txt', 'uniquename1.txt', 'uniquename~1.txt'),
            array('uniquename1.txt', 'uniquename2.txt', 'uniquename.txt'),
        );
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new ResourceManager(
                $this->resourceTypeRepo,
                $this->resourceRepo,
                $this->rightsRepo,
                $this->iconManager,
                $this->writer,
                $this->rightsManager
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';
            return m::mock(
                'Claroline\CoreBundle\Manager\ResourceManager' . $stringMocked,
                array(
                    $this->resourceTypeRepo,
                    $this->resourceRepo,
                    $this->rightsRepo,
                    $this->iconManager,
                    $this->writer,
                    $this->rightsManager
                )
            );
        }
    }
}

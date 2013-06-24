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
    private $roleRepo;
    private $iconManager;
    private $rightsRepo;

    public function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\CoreBundle\Writer\ResourceWriter');
        $this->rightsManager = m::mock('Claroline\CoreBundle\Manager\RightsManager');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
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
    public function testhaveSameParents($parents, $result)
    {
        $this->assertEquals($result, $this->getManager()->haveSameParents($parents));
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
        $manager = $this->getManager(array('haveSameParents', 'findAndSortChildren'));
        $manager->shouldReceive('haveSameParents')->once()->andReturn(true);
        $manager->shouldReceive('findAndSortChildren')->once()->andReturn($fullSort);
        $sorted = $manager->sort($resources);
        $this->assertEquals($sorted, $result);
    }

    public function testCheckResourceTypes()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $fileType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceTypes = array(array('name' => 'dir'), array('name' => 'file'));
        $this->resourceTypeRepo->shouldReceive('findOneByName')->once()->with('dir')->andReturn($dirType);
        $this->resourceTypeRepo->shouldReceive('findOneByName')->once()->with('file')->andReturn($fileType);
        $types = $this->getManager()->checkResourceTypes($resourceTypes);
        $this->assertEquals(array($dirType, $fileType), $types);
        m::getConfiguration()->allowMockingNonExistentMethods(false);
    }

    public function testCheckResourceTypesThrowsException()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->setExpectedException('\Claroline\CoreBundle\Manager\Exception\ResourceTypeNotFoundException');
        $resourceTypes = array(array('name' => 'idontexist'));
        $this->resourceTypeRepo->shouldReceive('findOneByName')->once()->with('idontexist')->andReturn(null);
        $this->getManager()->checkResourceTypes($resourceTypes);
        m::getConfiguration()->allowMockingNonExistentMethods(false);
    }

    public function testCheckResourcePrepared()
    {
        $this->setExpectedException('\Claroline\CoreBundle\Manager\Exception\MissingResourceNameException');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource->shouldReceive('getName')->andReturn(null);
        $this->getManager()->checkResourcePrepared($resource);
    }

    public function testSetRights()
    {

    }

    public function testSetRightsThrowsException()
    {

    }

    public function testCreateRights()
    {
        
    }

    public function testMakeShortcut()
    {
        $manager = $this->getManager(array('create'));
        $target = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $shortcut = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceShortcut');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $creator = m::mock('Claroline\CoreBundle\Entity\User');
        $manager->shouldReceive('create')->once()->andReturn($shortcut);
        $target->shouldReceive('getResourceType')->once()->andReturn($dirType);
        $target->shouldReceive('getName')->once()->andReturn('name');
        $parent->shouldReceive('getWorkspace')->once()->andReturn($workspace);
        $shortcut->shouldReceive('setName')->once();
        $shortcut->shouldReceive('setResource')->once()->with($target);
        $manager->makeShortcut($target, $parent, $creator, $shortcut);
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
                $this->roleRepo,
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
                    $this->roleRepo,
                    $this->iconManager,
                    $this->writer,
                    $this->rightsManager
                )
            );
        }
    }
}

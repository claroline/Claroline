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
    private $shortcutRepo;
    private $iconManager;
    private $rightsRepo;
    private $eventDispatcher;

    public function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\CoreBundle\Writer\ResourceWriter');
        $this->rightsManager = m::mock('Claroline\CoreBundle\Manager\RightsManager');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->shortcutRepo = m::mock('Claroline\CoreBundle\Repository\ResourceShortcutRepository');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->iconManager = m::mock('Claroline\CoreBundle\Manager\IconManager');
        $this->eventDispatcher = m::mock('Symfony\Component\EventDispatcher\EventDispatcher');
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

    /**
     * @dataProvider setRightsProvider
     */
    public function testSetRights($parent, $rights, $isExceptionExpected, $timesCopy, $timesCreate)
    {
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $manager = $this->getManager(array('createRights'));

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\RightsException');
        }

        $manager->shouldReceive('createRights')->times($timesCreate);
        $this->rightsManager->shouldReceive('copy')->times($timesCopy);
        $manager->setRights($resource, $parent, $rights);
    }

    public function testCreateRights()
    {
        $manager = $this->getManager(array('checkResourceTypes'));

        $role1 = m::mock('Claroline\CoreBundle\Entity\Role');
        $role2 = m::mock('Claroline\CoreBundle\Entity\Role');
        $type1 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $type2 = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $res = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');

        $rights = array(
            array('role' => $role1, 'canCreate' => array()),
            array('role' => $role2, 'canCreate' => array())
        );

        $manager->shouldReceive('checkResourceTypes')->times(2)->andReturn(array($type1, $type2));
        $this->resourceTypeRepo->shouldReceive('findAll')->once()->andReturn(array($type1, $type2));
        $this->roleRepo->shouldReceive('findOneBy')->times(2)->andReturn($role1);
        $this->rightsManager->shouldReceive('create')->times(count($rights) + 2);
        $manager->createRights($res, $rights);
    }

    /**
     * @dataProvider areAncestorsDirectoryProvider
     */
    public function testAreAncestorsDirectory($ancestors, $expected)
    {
        $result = $this->getManager()->areAncestorsDirectory($ancestors);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider isPathValidProvider
     */
    public function testIsPathValid($breadcrumbs, $expectedResult)
    {
        $manager = $this->getManager(array('hasLinkTo'));
        $manager->shouldReceive('hasLinkTo')->andReturn($expectedResult);
        $result = $manager->isPathValid($breadcrumbs);
        $this->assertEquals($result, $expectedResult);
    }

    public function testBuildSearchArray()
    {
        $queryParameters = array(
            'name' => 'name',
            'types' =>  array('directory'),
            'randomstuff' => 'notgonnabehere'
        );

        $expectedResult = array(
            'name' => 'name',
            'types' => array('directory')
        );

        $result = $this->getManager()->buildSearchArray($queryParameters);
        $this->assertEquals($result, $expectedResult);
    }

    public function testInsertBefore()
    {
        $this->markTestSkipped('find a way to test this properly');
    }

    public function testRemovePosition()
    {
        $this->markTestSkipped('find a way to test this properly');
    }

    public function testSetLastPosition()
    {
        $this->markTestSkipped('find a way to test this properly');
    }

    public function testMove()
    {
        $manager = $this->getManager(array('getUniqueName', 'removePosition', 'setLastPosition'));
        $manager->shouldReceive('getUniqueName')->andReturn('name');
        $manager->shouldReceive('removePosition')->once();
        $manager->shouldReceive('setLastPosition')->once();
        $child = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $this->writer->shouldReceive('move')->once()->with($child, $parent, 'name');
        $manager->move($child, $parent);
    }

    public function testDelete()
    {
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resource->shouldReceive('getResourceType')->andReturn($dirType);
        $dirType->shouldReceive('getName')->andReturn('directory');
        $manager = $this->getManager(array('removePosition'));
        $manager->shouldReceive('removePosition')->once()->with($resource);
        $this->eventDispatcher->shouldReceive('dispatch')->once()->with('delete_directory', m::any());
        $this->writer->shouldReceive('remove')->once()->with($resource);
        $manager->delete($resource);
    }

    public function testCopy()
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

    public function testGenerateGuid()
    {
        $manager = $this->getManager();
        $guid1 = $manager->generateGuid();
        $guid2 = $manager->generateGuid();
        $this->assertNotEquals($guid1, $guid2);
    }

    public function isPathValidProvider()
    {
        $grandParent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $dirParent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $child = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $linkToDirParent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $child->shouldReceive('getParent')->andReturn($dirParent);
        $dirParent->shouldReceive('getParent')->andReturn($grandParent);
        $linkToDirParent->shouldReceive('getParent')->andReturn($grandParent);
        $grandParent->shouldReceive('getParent')->andReturn(null);

        return array(
            array(array($grandParent, $dirParent, $child), true),
            array(array($grandParent, $grandParent, $child), false),
            array(array($grandParent, $linkToDirParent, $child), true),
        );
    }

    public function areAncestorsDirectoryProvider()
    {
        $child = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $dirParent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $grandParent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $fileParent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $fileType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $dirType->shouldReceive('getName')->andReturn('directory');
        $fileType->shouldReceive('getName')->andReturn('file');
        $child->shouldReceive('getResourceType')->andReturn($fileType);
        $dirParent->shouldReceive('getResourceType')->andReturn($dirType);
        $fileParent->shouldReceive('getResourceType')->andReturn($fileType);
        $grandParent->shouldReceive('getResourceType')->andReturn($dirType);

        return array(
            array(array($fileParent, $grandParent, $child), false),
            array(array($dirParent, $grandParent, $child), true)
        );
    }

    public function setRightsProvider()
    {
        return array(
            array(null, array(), true, 0, 0),
            array(m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource'), array('sthg'), false, 0, 1),
            array(m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource'), array(), false, 1, 0),
            array(null, array('sthg'), false, 0, 1)
        );
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
                $this->shortcutRepo,
                $this->iconManager,
                $this->writer,
                $this->rightsManager,
                $this->eventDispatcher
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
                    $this->shortcutRepo,
                    $this->iconManager,
                    $this->writer,
                    $this->rightsManager,
                    $this->eventDispatcher
                )
            );
        }
    }
}

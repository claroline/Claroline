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
    private $roleManager;
    private $shortcutRepo;
    private $iconManager;
    private $rightsRepo;
    private $eventDispatcher;
    private $genericRepo;

    public function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->rightsManager = m::mock('Claroline\CoreBundle\Manager\RightsManager');
        $this->resourceRepo = m::mock('Claroline\CoreBundle\Repository\AbstractResourceRepository');
        $this->resourceTypeRepo = m::mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->shortcutRepo = m::mock('Claroline\CoreBundle\Repository\ResourceShortcutRepository');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->rightsRepo = m::mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->iconManager = m::mock('Claroline\CoreBundle\Manager\IconManager');
        $this->eventDispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->genericRepo = m::mock('Claroline\CoreBundle\Database\GenericRepository');
    }

    /**
     * @dataProvider uniqueNameProvider
     * @group resource
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

    /**
     * @group resource
     */
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
     * @group resource
     */
    public function testhaveSameParents($parents, $result)
    {
        $this->assertEquals($result, $this->getManager()->haveSameParents($parents));
    }

    /**
     * @group resource
     */
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

    /**
     * @group resource
     */
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

    /**
     * @group resource
     */
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

    /**
     * @group resource
     */
    public function testCheckResourceTypesThrowsException()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->setExpectedException('\Claroline\CoreBundle\Manager\Exception\ResourceTypeNotFoundException');
        $resourceTypes = array(array('name' => 'idontexist'));
        $this->resourceTypeRepo->shouldReceive('findOneByName')->once()->with('idontexist')->andReturn(null);
        $this->getManager()->checkResourceTypes($resourceTypes);
        m::getConfiguration()->allowMockingNonExistentMethods(false);
    }

    /**
     * @group resource
     */
    public function testCheckResourcePrepared()
    {
        $this->setExpectedException('\Claroline\CoreBundle\Manager\Exception\MissingResourceNameException');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource->shouldReceive('getName')->andReturn(null);
        $this->getManager()->checkResourcePrepared($resource);
    }

    /**
     * @dataProvider setRightsProvider
     * @group resource
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

    /**
     * @group resource
     */
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
     * @group resource
     */
    public function testAreAncestorsDirectory($ancestors, $expected)
    {
        $result = $this->getManager()->areAncestorsDirectory($ancestors);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider isPathValidProvider
     * @group resource
     */
    public function testIsPathValid($breadcrumbs, $expectedResult)
    {
        $manager = $this->getManager(array('hasLinkTo'));
        $manager->shouldReceive('hasLinkTo')->andReturn($expectedResult);
        $result = $manager->isPathValid($breadcrumbs);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @group resource
     */
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

    /**
     * @dataProvider insertBeforeProvider
     * @group resource
     */
    public function testInsertBefore($previous, $next, $oldPrev, $oldNext)
    {
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $manager = $this->getManager(array('findPreviousOrLastRes'));
        $manager->shouldReceive('findPreviousOrLastRes')->once()->andReturn($previous);
        $resource->shouldReceive('setNext')->with($next)->once();
        $resource->shouldReceive('setPrevious')->with($previous)->once();
        $resource->shouldReceive('getParent')->once()->andReturn($parent);
        $resource->shouldReceive('getPrevious')->once()->andReturn($oldPrev);
        $resource->shouldReceive('getNext')->once()->andReturn($oldNext);
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->writer->shouldReceive('forceFlush')->once();
        $this->writer->shouldReceive('update');
        $manager->insertBefore($resource, $next);

        if ($previous) {
            $this->assertEquals($previous->getNext(), $resource);
        }
        if ($next) {
            $this->assertEquals($next->getPrevious(), $resource);
        }
        if ($oldNext) {
            $this->assertEquals($oldNext->getPrevious(), $oldPrev);
        }
        if ($oldPrev) {
            $this->assertEquals($oldPrev->getNext(), $oldNext);
        }
    }

    /**
     * @group resource
     */
    public function testRemovePosition()
    {
        $this->markTestSkipped('find a way to test this properly');
    }

    /**
     * @group resource
     */
    public function testSetLastPosition()
    {
        $this->markTestSkipped('find a way to test this properly');
    }

    /**
     * @group resource
     */
    public function testMove()
    {
        $manager = $this->getManager(array('getUniqueName', 'removePosition', 'setLastPosition'));
        $manager->shouldReceive('getUniqueName')->andReturn('name');
        $manager->shouldReceive('removePosition')->once();
        $manager->shouldReceive('setLastPosition')->once();
        $child = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $child->shouldReceive('setParent')->once()->with($parent);
        $child->shouldReceive('setName')->once()->with('name');
        $this->writer->shouldReceive('update')->once()->with($child);
        $manager->move($child, $parent);
    }

    /**
     * @group resource
     */
    public function testDelete()
    {
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resource->shouldReceive('getResourceType')->andReturn($dirType);
        $dirType->shouldReceive('getName')->andReturn('directory');
        $manager = $this->getManager(array('removePosition'));
        $manager->shouldReceive('removePosition')->once()->with($resource);
        $this->eventDispatcher->shouldReceive('dispatch')->once()->with('delete_directory', 'DeleteResource', m::any());
        $this->writer->shouldReceive('delete')->once()->with($resource);
        $manager->delete($resource);
    }

    /**
     * @group resource
     */
    public function testSimpleCopy()
    {
        $manager = $this->getManager(array('getUniqueName'));
        $manager->shouldReceive('getUniqueName')->andReturn('uniquename');

        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $last = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $event = m::mock('Claroline\CoreBundle\Event\Event\CopyResourceEvent');
        $resourceType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $icon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');

        $resource->shouldReceive('getResourceType')->andReturn($resourceType);
        $resource->shouldReceive('getIcon')->andReturn($icon);
        $resourceType->shouldReceive('getName')->andReturn('type_name');
        $copy = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $this->resourceRepo->shouldReceive('findOneBy')->once()->andReturn($last);
        $this->eventDispatcher->shouldReceive('dispatch')->andReturn($event);
        $event->shouldReceive('getCopy')->andReturn($copy);
        $parent->shouldReceive('getWorkspace')->andReturn($workspace);
        $last->shouldReceive('setNext')->once()->with(anInstanceOf('Claroline\CoreBundle\Entity\Resource\AbstractResource'));
        $this->writer->shouldReceive('update')->times(2);
        $this->rightsManager->shouldReceive('copy')->once()->with($resource, $copy);

        $manager->copy($resource, $parent, $user);
    }

    /**
     * @group resource
     */
    public function testMakeShortcut()
    {
        $manager = $this->getManager(array('create'));
        $target = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $shortcut = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceShortcut');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $icon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $shortcutIcon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $creator = m::mock('Claroline\CoreBundle\Entity\User');
        $manager->shouldReceive('create')->once()->andReturn($shortcut);
        $target->shouldReceive('getResourceType')->once()->andReturn($dirType);
        $target->shouldReceive('getName')->once()->andReturn('name');
        $target->shouldReceive('getIcon')->andReturn($icon);
        $icon->shouldReceive('getShortcutIcon')->andReturn($shortcutIcon);
        $parent->shouldReceive('getWorkspace')->once()->andReturn($workspace);
        $shortcut->shouldReceive('setName')->once();
        $shortcut->shouldReceive('setResource')->once()->with($target);
        $manager->makeShortcut($target, $parent, $creator, $shortcut);
    }

    public function testExport()
    {

    }
    
    /**
     * @group resource
     */
    public function testRename()
    {
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource->shouldReceive('setName')->once()->with('name');
        $this->writer->shouldReceive('update')->once()->with($resource);
        
        $this->assertEquals($resource, $this->getManager()->rename($resource, 'name'));
    }
    
    /**
     * @group resource
     */
    public function testChangeIcon()
    {
        $resource = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $file = m::mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        $icon = new \Claroline\CoreBundle\Entity\Resource\ResourceIcon();
        $this->iconManager->shouldReceive('createCustomIcon')->once()->with($file)->andReturn($icon);
        $this->iconManager->shouldReceive('replace')->once()->with($resource, $icon);
        
        $this->assertEquals($icon, $this->getManager()->changeIcon($resource, $file));
    }

    /**
     * @group resource
     */
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

    public function insertBeforeProvider()
    {
        $previous = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $next = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $oldPrev = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $oldNext = new \Claroline\CoreBundle\Entity\Resource\Directory();

        return array(
            array('previous' => $previous, 'next' => $next, 'oldPrev' => $oldPrev ,'oldNext' => $oldNext),
            array('previous' => $previous, 'next' => null, 'oldPrev' => null, 'oldNext' => $oldNext),
            array('previous' => null, 'next' => $next, 'oldPrev' => $oldPrev, 'oldNext' => $oldNext),
        );
    }

    public function copyProvider()
    {
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $parent = m::mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $resourceType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resource->shouldReceive('getResourceType')->andReturn($resourceType);
        $resourceType->shouldReceive('getName')->andReturn('type_name');

        return array(
            array('resource' => $resource, 'parent' => $parent, 'user' => $user)
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
                $this->roleManager,
                $this->shortcutRepo,
                $this->iconManager,
                $this->rightsManager,
                $this->eventDispatcher,
                $this->writer,
                $this->genericRepo
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
                    $this->roleManager,
                    $this->shortcutRepo,
                    $this->iconManager,
                    $this->rightsManager,
                    $this->eventDispatcher,
                    $this->writer,
                    $this->genericRepo
                )
            );
        }
    }
}

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

class ResourceManagerTest extends MockeryTestCase
{
    private $resourceNodeRepo;
    private $rightsManager;
    private $resourceTypeRepo;
    private $roleRepo;
    private $roleManager;
    private $shortcutRepo;
    private $iconManager;
    private $rightsRepo;
    private $eventDispatcher;
    private $om;
    private $ut;
    private $sc;

    public function setUp()
    {
        parent::setUp();

        $this->rightsManager = $this->mock('Claroline\CoreBundle\Manager\RightsManager');
        $this->resourceNodeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceNodeRepository');
        $this->resourceTypeRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceTypeRepository');
        $this->shortcutRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceShortcutRepository');
        $this->roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->rightsRepo = $this->mock('Claroline\CoreBundle\Repository\ResourceRightsRepository');
        $this->iconManager = $this->mock('Claroline\CoreBundle\Manager\IconManager');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->ut = $this->mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
        $this->sc = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
    }

    public function testCreate()
    {
        $manager = $this->getManager(array('checkResourcePrepared', 'getUniqueName', 'setRights'));

        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceType->shouldReceive('getName')->once()->andReturn('directory');
        $user = new \Claroline\CoreBundle\Entity\User();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $icon = new \Claroline\CoreBundle\Entity\Resource\ResourceIcon();
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $name = 'name';
        $parent->shouldReceive('getPathForDisplay')->once()->andReturn('path');
        $resource->shouldReceive('getName')->once()->andReturn($name);
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceNode')->andReturn($node);
        $prev = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');

        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('checkResourcePrepared')->once()->with($resource);
        $manager->shouldReceive('getUniqueName')->once()->with($node, $parent)->andReturn($name);
        $resource->shouldReceive('getMimeType')->once()->andReturn(null);
        $node->shouldReceive('setMimeType')->once()->with('custom/directory');
        $this->resourceNodeRepo->shouldReceive('findOneBy')->once()
            ->with(array('parent' => $parent, 'next' => null))->andReturn($prev);
        $prev->shouldReceive('setNext')->once()->with($node);
        $this->iconManager->shouldReceive('getIcon')->once()->with($resource)->andReturn($icon);
        $node->shouldReceive('setCreator')->once()->with($user);
        $node->shouldReceive('setWorkspace')->once()->with($workspace);
        $node->shouldReceive('setResourceType')->once()->with($resourceType);
        $node->shouldReceive('setParent')->once()->with($parent);
        $node->shouldReceive('setName')->once()->with($name);
        $node->shouldReceive('setPrevious')->once()->with($prev);
        $node->shouldReceive('setIcon')->once()->with($icon);
        $node->shouldReceive('setClass')->once()->with(get_class($resource));
        $node->shouldReceive('setPathForCreationLog')->once()->with('path / name');
        $resource->shouldReceive('setResourceNode')->once()->with($node);
        $manager->shouldReceive('setRights')->once()->with($node, $parent, null);
        $this->om->shouldReceive('persist')->once()->with($resource);
        $this->om->shouldReceive('persist')->once()->with($node);
        $this->eventDispatcher->shouldReceive('dispatch')->once()->with('log', 'Log\LogResourceCreate', array($node));
        $this->om->shouldReceive('endFlushSuite')->once();

        $this->assertEquals($resource, $manager->create($resource, $resourceType, $user, $workspace, $parent));
    }

    public function testCreateResource()
    {
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $this->om->shouldReceive('factory')->once()
             ->with('\Claroline\CoreBundle\Entity\Resource\Directory')->andReturn($resource);
        $resource->shouldReceive('setName')->once()->with('name');

        $this->assertEquals(
             $resource,
             $this->getManager()->createResource('\Claroline\CoreBundle\Entity\Resource\Directory', 'name')
         );
    }

    /**
     * @expectedException Claroline\CoreBundle\Manager\Exception\WrongClassException
     */
    public function testCreateResourceThrowsAnException()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $this->om->shouldReceive('factory')->once()
             ->with('\Claroline\CoreBundle\Entity\Role')->andReturn($role);

        $this->getManager()->createResource('\Claroline\CoreBundle\Entity\Role', 'name');
    }

    /**
     * @dataProvider uniqueNameProvider
     */
    public function testGetUniqueName($childAName, $childBName, $generatedName)
    {
        $manager = $this->getManager();
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $childA = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $childB = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $resource->shouldReceive('getName')->once()->andReturn('uniquename.txt');
        $childA->shouldReceive('getName')->once()->andReturn($childAName);
        $childB->shouldReceive('getName')->once()->andReturn($childBName);

        $uniquename = $manager->getUniqueName($resource);
        $this->assertEquals($uniquename, $generatedName);
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

        $parent = $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->resourceNodeRepo->shouldReceive('findChildren')->once()->andReturn($resources);
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
            array('previous_id' => null, 'id' => 1),
        );

        $result = array(
            array('previous_id' => null, 'id' => 1),
            array('previous_id' => 2, 'id' => 3, 'parent_id' => 42),
        );

        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->resourceNodeRepo->shouldReceive('find')->once()->andReturn($parent);
        $manager = $this->getManager(array('haveSameParents', 'findAndSortChildren'));
        $manager->shouldReceive('haveSameParents')->once()->andReturn(true);
        $manager->shouldReceive('findAndSortChildren')->once()->andReturn($fullSort);
        $sorted = $manager->sort($resources);
        $this->assertEquals($sorted, $result);
    }

    public function testCheckResourceTypes()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $dirType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $fileType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
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
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource->shouldReceive('getName')->andReturn(null);
        $this->getManager()->checkResourcePrepared($resource);
    }

    /**
     * @dataProvider setRightsProvider
     */
    public function testSetRights($parent, $rights, $isExceptionExpected, $timesCopy, $timesCreate)
    {
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
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

        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $typeA = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $typeB = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $res = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');

        $rights = array(
            array('role' => $roleA, 'create' => array()),
            array('role' => $roleB, 'create' => array()),
        );

        $manager->shouldReceive('checkResourceTypes')->times(2)->andReturn(array($typeA, $typeB));
        $this->resourceTypeRepo->shouldReceive('findAll')->once()->andReturn(array($typeA, $typeB));
        $this->roleRepo->shouldReceive('findOneBy')->times(2)->andReturn($roleA);
        $this->rightsManager->shouldReceive('create')->times(count($rights) + 2);
        $manager->createRights($res, $rights);
    }

    /**
     * @dataProvider areAncestorsDirectoryProvider
     */
    public function testAreAncestorsDirectory($ancestors, $expected)
    {
        $this->markTestSkipped('Something wrong with the data provider...');

        $result = $this->getManager()->areAncestorsDirectory($ancestors);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider isPathValidProvider
     */
    public function testIsPathValid($breadcrumbs, $expectedResult)
    {
        $this->markTestSkipped('Something wrong with the data provider...');

        $manager = $this->getManager(array('hasLinkTo'));
        $manager->shouldReceive('hasLinkTo')->andReturn($expectedResult);
        $result = $manager->isPathValid($breadcrumbs);
        $this->assertEquals($result, $expectedResult);
    }

    public function testBuildSearchArray()
    {
        $queryParameters = array(
            'name' => 'name',
            'types' => array('directory'),
            'randomstuff' => 'notgonnabehere',
        );

        $expectedResult = array(
            'name' => 'name',
            'types' => array('directory'),
        );

        $result = $this->getManager()->buildSearchArray($queryParameters);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @dataProvider insertBeforeProvider
     */
    public function testInsertBefore($previous, $next, $oldPrev, $oldNext, $rmNext, $rmPrev)
    {
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $manager = $this->getManager(
            array(
                'findPreviousOrLastRes',
                'removePreviousWherePreviousIs',
                'removeNextWhereNextIs',
            )
        );

        if (!$previous) {
            $resource->shouldReceive('setNext')->with(null)->once();
            $resource->shouldReceive('setPrevious')->with(null)->once();
        }

        if ($next) {
            $resource->shouldReceive('setNext')->with(null)->once();
        }

        $manager->shouldReceive('removePreviousWherePreviousIs')->times($rmPrev);
        $manager->shouldReceive('removeNextWhereNextIs')->times($rmNext);
        $manager->shouldReceive('findPreviousOrLastRes')->once()->andReturn($previous);
        $resource->shouldReceive('setNext')->with($next)->once();
        $resource->shouldReceive('setPrevious')->with($previous)->once();
        $resource->shouldReceive('getParent')->once()->andReturn($parent);
        $resource->shouldReceive('getPrevious')->once()->andReturn($oldPrev);
        $resource->shouldReceive('getNext')->once()->andReturn($oldNext);
        $this->om->shouldReceive('persist');
        $this->om->shouldReceive('flush')->once();
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

    public function testRemovePosition()
    {
        $manager = $this->getManager(array('removePreviousWherePreviousIs', 'removeNextWhereNextIs'));
        $next = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $previous = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $manager->shouldReceive('removeNextWhereNextIs')->with($next)->once();
        $manager->shouldReceive('removePreviousWherePreviousIs')->with($previous)->once();
        $resource->shouldReceive('getNext')->once()->andReturn($next);
        $resource->shouldReceive('getPrevious')->once()->andReturn($previous);
        $resource->shouldReceive('setNext')->once()->with(null);
        $resource->shouldReceive('setPrevious')->once()->with(null);
        $next->shouldReceive('setPrevious')->once()->with($previous);
        $previous->shouldReceive('setNext')->once()->with($next);
        $this->om->shouldReceive('persist')->times(3);
        $this->om->shouldReceive('flush');
        $manager->removePosition($resource);
    }

    public function testSetLastPosition()
    {
        $lastChild = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $this->resourceNodeRepo->shouldReceive('findOneBy')->once()
            ->with(array('parent' => $parent, 'next' => null))->andReturn($lastChild);
        $resource->shouldReceive('setPrevious')->once()->with($lastChild);
        $resource->shouldReceive('setNext')->once()->with(null);
        $lastChild->shouldReceive('setNext')->once()->with($resource);
        $this->om->shouldReceive('persist')->times(2);
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->setLastPosition($parent, $resource);
    }

    public function testMove()
    {
        $manager = $this->getManager(array('getUniqueName', 'removePosition', 'setLastPosition'));
        $manager->shouldReceive('getUniqueName')->andReturn('name');
        $manager->shouldReceive('removePosition')->once();
        $manager->shouldReceive('setLastPosition')->once();
        $child = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $child->shouldReceive('setParent')->once()->with($parent);
        $child->shouldReceive('setName')->once()->with('name');
        $this->om->shouldReceive('persist')->once()->with($child);
        $this->om->shouldReceive('flush')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once()
            ->with('log', 'Log\LogResourceMove', array($child, $parent));
        $manager->move($child, $parent);
    }

    public function testDelete()
    {
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $descendant = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $node->shouldReceive('getParent')->andReturn($parent);
        $resource = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $dirType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $node->shouldReceive('getResourceType')->andReturn($dirType);
        $dirType->shouldReceive('getName')->andReturn('directory');
        $manager = $this->getManager(array('removePosition', 'getResourceFromNode', 'getDescendants'));
        $manager->shouldReceive('removePosition')->once()->with($node);
        $manager->shouldReceive('getResourceFromNode')->times(2);
        $manager->shouldReceive('getDescendants')->once()->andReturn(array($descendant));
        $this->eventDispatcher->shouldReceive('dispatch')
            ->times(2);
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $node->shouldReceive('getIcon')->once()->andReturn($icon);
        $this->iconManager->shouldReceive('delete')->once()->with($icon);
        $this->om->shouldReceive('remove')->once()->with($node);
        $this->om->shouldReceive('remove')->once()->with($descendant);
        $this->om->shouldReceive('remove')->times(2);
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once()
            ->with('log', 'Log\LogResourceDelete', array($node));
        $manager->delete($node);
    }

    //@todo doing some assertions on the $copy
    public function testSimpleCopy()
    {
        $manager = $this->getManager(array('getUniqueName', 'getResourceFromNode'));
        $manager->shouldReceive('getUniqueName')->andReturn('uniquename');

        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $newNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $last = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $event = $this->mock('Claroline\CoreBundle\Event\CopyResourceEvent');
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');

        $manager->shouldReceive('getResourceFromNode')->once()
            ->with($node)->andReturn(new \Claroline\CoreBundle\Entity\Resource\Directory());
        $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Resource\ResourceNode')
            ->andReturn($newNode);

        $node->shouldReceive('getResourceType')->andReturn($resourceType);
        $node->shouldReceive('getIcon')->andReturn($icon);
        $node->shouldReceive('getClass')->once()->andReturn('class');
        $node->shouldReceive('getMimeType')->once()->andReturn('mime');
        $resourceType->shouldReceive('getName')->andReturn('type_name');
        $copy = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $newNode->shouldReceive('setMimeType')->once()->with('mime');
        $newNode->shouldReceive('setResourceType')->once()->with($resourceType);
        $newNode->shouldReceive('setCreator')->once()->with($user);
        $newNode->shouldReceive('setWorkspace')->once()->with($workspace);
        $newNode->shouldReceive('setParent')->once()->with($parent);
        $newNode->shouldReceive('setName')->once()->with();
        $newNode->shouldReceive('setPrevious')->once()->with($last);
        $newNode->shouldReceive('setNext')->once()->with(null);
        $newNode->shouldReceive('setIcon')->once()->with($icon);
        $newNode->shouldReceive('setClass')->once()->with('class');
        $this->resourceNodeRepo->shouldReceive('findOneBy')->once()->andReturn($last);
        $this->eventDispatcher->shouldReceive('dispatch')->andReturn($event);
        $event->shouldReceive('getCopy')->andReturn($copy);
        $parent->shouldReceive('getWorkspace')->andReturn($workspace);

        $last->shouldReceive('setNext')
            ->once()
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Resource\ResourceNode'));
        $this->om->shouldReceive('persist')->times(2);
        $this->rightsManager->shouldReceive('copy')->once()->with($node, $newNode);
        $this->om->shouldReceive('flush')->once();

        $manager->copy($node, $parent, $user);
    }

    public function testShortcutCopy()
    {
        $manager = $this->getManager(array('getUniqueName', 'getResourceFromNode'));
        $manager->shouldReceive('getUniqueName')->andReturn('uniquename');

        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $target = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $last = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $event = $this->mock('Claroline\CoreBundle\Event\CopyResourceEvent');
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $newNode = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');

        $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Resource\ResourceNode')
            ->andReturn($newNode);
        $node->shouldReceive('getResourceType')->andReturn($resourceType);
        $node->shouldReceive('getIcon')->andReturn($icon);
        $node->shouldReceive('getClass')->once()->andReturn('class');
        $node->shouldReceive('getMimeType')->once()->andReturn('mime');
        $manager->shouldReceive('getResourceFromNode')->once()
            ->with($node)->andReturn(new \Claroline\CoreBundle\Entity\Resource\Directory());

        $newNode->shouldReceive('setMimeType')->once()->with('mime');
        $newNode->shouldReceive('setResourceType')->once()->with($resourceType);
        $newNode->shouldReceive('setCreator')->once()->with($user);
        $newNode->shouldReceive('setWorkspace')->once()->with($workspace);
        $newNode->shouldReceive('setParent')->once()->with($parent);
        $newNode->shouldReceive('setName')->once()->with();
        $newNode->shouldReceive('setPrevious')->once()->with($last);
        $newNode->shouldReceive('setNext')->once()->with(null);
        $newNode->shouldReceive('setIcon')->once()->with($icon);
        $newNode->shouldReceive('setClass')->once()->with('class');

        $resourceType->shouldReceive('getName')->andReturn('type_name');
        $copy = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceShortcut');
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceShortcut')->andReturn($copy);
        $copy->shouldReceive('setResourceNode')->once()->with($newNode);
        $copy->shouldReceive('setTarget')->once()->with($target);
        $this->resourceNodeRepo->shouldReceive('findOneBy')->once()->andReturn($last);
        $this->eventDispatcher->shouldReceive('dispatch')->andReturn($event);
        $event->shouldReceive('getCopy')->andReturn($copy);
        $parent->shouldReceive('getWorkspace')->andReturn($workspace);
        $last->shouldReceive('setNext')
            ->once()
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Resource\ResourceNode'));
        $this->om->shouldReceive('persist')->times(2);
        $this->rightsManager->shouldReceive('copy')->once()->with($node, $newNode);
        $this->om->shouldReceive('flush')->once();

        $manager->copy($node, $parent, $user);
    }

    public function testMakeShortcut()
    {
        $manager = $this->getManager(array('create'));
        $target = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $parent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $dirType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $shortcut = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceShortcut');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $shortcutIcon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $shortcutNode = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $shortcut->shouldReceive('getResourceNode')->once()->andReturn($shortcutNode);
        $creator = $this->mock('Claroline\CoreBundle\Entity\User');
        $manager->shouldReceive('create')->once()->andReturn($shortcut);
        $target->shouldReceive('getResourceType')->once()->andReturn($dirType);
        $target->shouldReceive('getName')->once()->andReturn('name');
        $target->shouldReceive('getIcon')->andReturn($icon);
        $icon->shouldReceive('getShortcutIcon')->andReturn($shortcutIcon);
        $parent->shouldReceive('getWorkspace')->once()->andReturn($workspace);
        $shortcut->shouldReceive('setName')->once();
        $shortcut->shouldReceive('setTarget')->once()->with($target);
        $this->eventDispatcher->shouldReceive('dispatch')->once()
            ->with('log', 'Log\LogResourceCreate', array($shortcutNode));
        $manager->makeShortcut($target, $parent, $creator, $shortcut);
    }

    public function testExport()
    {
        $this->markTestSkipped();
    }

    public function testRename()
    {
        $manager = $this->getManager(array('logChangeSet'));
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $node->shouldReceive('setName')->once()->with('name');
        $this->om->shouldReceive('persist')->once()->with($node);
        $this->om->shouldReceive('flush')->once();
        $manager->shouldReceive('logChangeSet')->once()->with($node);

        $this->assertEquals($node, $manager->rename($node, 'name'));
    }

    public function testChangeIcon()
    {
        $manager = $this->getManager(array('logChangeSet'));
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $file = $this->mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        $icon = new \Claroline\CoreBundle\Entity\Resource\ResourceIcon();
        $this->iconManager->shouldReceive('createCustomIcon')->once()->with($file)->andReturn($icon);
        $this->iconManager->shouldReceive('replace')->once()->with($node, $icon);
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('logChangeSet')->once()->with($node);

        $this->assertEquals($icon, $manager->changeIcon($node, $file));
    }

    public function testLogChangeSet()
    {
        $uow = $this->mock('Doctrine\ORM\UnitOfWork');
        $node = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $this->om->shouldReceive('getUnitOfWork')->andReturn($uow);
        $uow->shouldReceive('computeChangeSets')->once();
        $uow->shouldReceive('getEntityChangeSet')->once()->with($node)->andReturn(array());
        $this->eventDispatcher->shouldReceive('dispatch')->once()
            ->with('log', 'Log\LogResourceUpdate', array($node, array()));
        $this->getManager()->logChangeSet($node);
    }

    public function testGetResourceTypeByName()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->resourceTypeRepo->shouldReceive('findOneByName')->with('name')->once()->andReturn('result');
        $this->assertEquals('result', $this->getManager()->getResourceTypeByName('name'));
    }

    public function testGetAllResourceTypes()
    {
        $this->resourceTypeRepo->shouldReceive('findAll')->once()->andReturn('result');
        $this->assertEquals('result', $this->getManager()->getAllResourceTypes());
    }

    public function testGetByIds()
    {
        $this->om->shouldReceive('findByIds')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceNode', array(1, 2))->andReturn('result');

        $this->assertEquals('result', $this->getManager()->getByIds(array(1, 2)));
    }

    public function isPathValidProvider()
    {
        $grandParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $dirParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $child = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $linkToDirParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
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
        $child = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $dirParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $grandParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $fileParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $dirType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $fileType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $dirType->shouldReceive('getName')->andReturn('directory');
        $fileType->shouldReceive('getName')->andReturn('file');
        $child->shouldReceive('getResourceType')->andReturn($fileType);
        $dirParent->shouldReceive('getResourceType')->andReturn($dirType);
        $fileParent->shouldReceive('getResourceType')->andReturn($fileType);
        $grandParent->shouldReceive('getResourceType')->andReturn($dirType);

        return array(
            array(array($fileParent, $grandParent, $child), false),
            array(array($dirParent, $grandParent, $child), true),
        );
    }

    public function setRightsProvider()
    {
        return array(
            array(null, array(), true, 0, 0),
            //array($this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode'), array('sthg'), false, 0, 1),
            //array($this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode'), array(), false, 1, 0),
            array(null, array('sthg'), false, 0, 1),
        );
    }

    public function parentAsArrayProvider()
    {
        return array(
            array(array(array('parent_id' => 1), array('parent_id' => 2)), false),
            array(array(array('parent_id' => 1), array('parent_id' => 1)), true),
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
        $previous = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $next = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $oldPrev = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $oldNext = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();

        return array(
            array(
                'previous' => $previous,
                'next' => $next,
                'oldPrev' => $oldPrev,
                'oldNext' => $oldNext,
                'rmNext' => 1,
                'rmPrev' => 3,
            ),
            array(
                'previous' => $previous,
                'next' => null,
                'oldPrev' => null,
                'oldNext' => $oldNext,
                'rmNext' => 1,
                'rmPrev' => 1,
            ),
            array(
                'previous' => null,
                'next' => $next,
                'oldPrev' => $oldPrev,
                'oldNext' => $oldNext,
                'rmNext' => 0,
                'rmPrev' => 3,
            ),
        );
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceType')
            ->andReturn($this->resourceTypeRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceNode')
            ->andReturn($this->resourceNodeRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceRights')
            ->andReturn($this->rightsRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')
            ->andReturn($this->roleRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceShortcut')
            ->andReturn($this->shortcutRepo);

        if (count($mockedMethods) === 0) {
            return new ResourceManager(
                $this->roleManager,
                $this->iconManager,
                $this->rightsManager,
                $this->eventDispatcher,
                $this->om,
                $this->ut,
                $this->sc
            );
        } else {
            $stringMocked = '[';
            $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Manager\ResourceManager'.$stringMocked,
                array(
                    $this->roleManager,
                    $this->iconManager,
                    $this->rightsManager,
                    $this->eventDispatcher,
                    $this->om,
                    $this->ut,
                    $this->sc,
                )
            );
        }
    }
}

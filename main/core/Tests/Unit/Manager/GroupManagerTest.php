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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\ORM\Query;
use Mockery as m;

class GroupManagerTest extends MockeryTestCase
{
    private $om;
    private $groupRepo;
    private $userRepo;
    private $pagerFactory;
    private $translator;
    private $eventDispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->groupRepo = $this->mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->pagerFactory = $this->mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
    }

    public function testInsertGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->insertGroup($group);
    }

    public function testDeleteGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $this->om->shouldReceive('remove')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteGroup($group);
    }

    public function testUpdateGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $unitOfWork = $this->mock('Doctrine\ORM\UnitOfWork');
        $changeSet = [];

        $this->om->shouldReceive('getUnitOfWork')->once()->andReturn($unitOfWork);
        $unitOfWork->shouldReceive('computeChangeSets')->once();
        $unitOfWork->shouldReceive('getEntityChangeSet')->with($group)->once()->andReturn($changeSet);
        $group->shouldReceive('getPlatformRole')->once()->andReturn($role);
        $role->shouldReceive('getTranslationKey')->once()->andReturn('new_key');
        $changeSet['platformRole'] = ['old_key', 'new_key'];
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupUpdate', [$group, $changeSet])
            ->once();
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->updateGroup($group, 'old_key');
    }

    public function testAddUsersToGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $userA = $this->mock('Claroline\CoreBundle\Entity\User');
        $userB = $this->mock('Claroline\CoreBundle\Entity\User');
        $users = [$userA, $userB];

        $group->shouldReceive('containsUser')->with($userA)->once()->andReturn(false);
        $group->shouldReceive('containsUser')->with($userB)->once()->andReturn(false);
        $group->shouldReceive('addUser')->with($userA)->once();
        $group->shouldReceive('addUser')->with($userB)->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupAddUser', [$group, $userA])
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupAddUser', [$group, $userB])
            ->once();

        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->addUsersToGroup($group, $users);
    }

    public function testRemoveUsersFromGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $userA = $this->mock('Claroline\CoreBundle\Entity\User');
        $userB = $this->mock('Claroline\CoreBundle\Entity\User');
        $users = [$userA, $userB];

        $group->shouldReceive('removeUser')->with($userA)->once();
        $group->shouldReceive('removeUser')->with($userB)->once();
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->removeUsersFromGroup($group, $users);
    }

    public function testImportUsers()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $manager = $this->getManager(['addUsersToGroup']);
        $users = [
            [
                'firstname1',
                'lastname1',
                'username1',
                'password1',
                'email1@claroline.net',
                'code1',
            ],
            [
                'firstname2',
                'lastname2',
                'username2',
                'password2',
                'email2@claroline.net',
                'code2',
            ],
        ];

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->userRepo->shouldReceive('findOneBy')
            ->with(['username' => 'username1', 'firstName' => 'firstname1', 'lastName' => 'lastname1'])
            ->once()
            ->andReturn(null);
        $this->userRepo->shouldReceive('findOneBy')
            ->with(['username' => 'username2', 'firstName' => 'firstname2', 'lastName' => 'lastname2'])
            ->once()
            ->andReturn($user);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $manager->shouldReceive('addUsersToGroup')
            ->with($group, [$user])
            ->once();

        $manager->importUsers($group, $users);
    }

    public function testGetWorkspaceOutsiders()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);

        $this->groupRepo->shouldReceive('findWorkspaceOutsiders')
            ->with($workspace, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getWorkspaceOutsiders($workspace, 1, 50));
    }

    public function testGetWorkspaceOutsidersByName()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);

        $this->groupRepo->shouldReceive('findWorkspaceOutsidersByName')
            ->with($workspace, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getWorkspaceOutsidersByName($workspace, 'search', 1));
    }

    public function testGetGroupsByWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);

        $this->groupRepo->shouldReceive('findByWorkspace')
            ->with($workspace, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByWorkspace($workspace, 1));
    }

    public function testGetGroupsByWorkspaceAndName()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);

        $this->groupRepo->shouldReceive('findByWorkspaceAndName')
            ->with($workspace, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByWorkspaceAndName($workspace, 'search', 1));
    }

    public function testGetGroups()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);

        $this->groupRepo->shouldReceive('findAll')
            ->with(false, 'id')
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroups(1));
    }

    public function testGetGroupsByName()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);

        $this->groupRepo->shouldReceive('findByName')
            ->with('search', false, 'id')
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByName('search', 1));
    }

    public function testGetGroupsByRoles()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);
        $role = new Role();
        $roles = [$role];

        $this->groupRepo->shouldReceive('findByRoles')
            ->with($roles, true, 'id')
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByRoles($roles, 1));
    }

    public function testGetOutsidersByWorkspaceRoles()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);
        $role = new Role();
        $roles = [$role];
        $workspace = new Workspace();

        $this->groupRepo->shouldReceive('findOutsidersByWorkspaceRoles')
            ->with($roles, $workspace, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getOutsidersByWorkspaceRoles($roles, $workspace, 1));
    }

    public function testGetGroupsByRolesAndName()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);
        $role = new Role();
        $roles = [$role];

        $this->groupRepo->shouldReceive('findByRolesAndName')
            ->with($roles, 'name', true, 'id')
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByRolesAndName($roles, 'name', 1));
    }

    public function testGetOutsidersByWorkspaceRolesAndName()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new Query($em);
        $role = new Role();
        $roles = [$role];
        $workspace = new Workspace();

        $this->groupRepo->shouldReceive('findOutsidersByWorkspaceRolesAndName')
            ->with($roles, 'name', $workspace, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 50)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            'pager',
            $this->getManager()->getOutsidersByWorkspaceRolesAndName($roles, 'name', $workspace, 1)
        );
    }

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:Group')->andReturn($this->groupRepo);
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:User')->andReturn($this->userRepo);

        if (0 === count($mockedMethods)) {
            return new GroupManager(
                $this->om,
                $this->pagerFactory,
                $this->translator,
                $this->eventDispatcher
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\GroupManager'.$stringMocked,
            [
                $this->om,
                $this->pagerFactory,
                $this->translator,
                $this->eventDispatcher,
            ]
        );
    }
}

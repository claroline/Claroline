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

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class GroupManagerTest extends MockeryTestCase
{
    private $om;
    private $groupRepo;
    private $eventDispatcher;
    private $roleManager;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->groupRepo = $this->mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
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

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:Group')->andReturn($this->groupRepo);

        if (0 === count($mockedMethods)) {
            return new GroupManager(
                $this->om,
                $this->eventDispatcher,
                $this->roleManager
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
                $this->eventDispatcher,
                $this->roleManager,
            ]
        );
    }
}

<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\Common\Collections\ArrayCollection;

class GroupManagerTest extends MockeryTestCase
{
    private $om;
    private $groupRepo;
    private $userRepo;
    private $pagerFactory;
    private $translator;

    public function setUp()
    {
        parent::setUp();

        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->groupRepo = m::mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->pagerFactory = m::mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->translator = m::mock('Symfony\Component\Translation\Translator');
    }

    public function testInsertGroup()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->insertGroup($group);
    }

    public function testDeleteGroup()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $this->om->shouldReceive('remove')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteGroup($group);
    }

    public function testUpdateGroup()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->updateGroup($group);
    }

    public function testAddUsersToGroup()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $userA = m::mock('Claroline\CoreBundle\Entity\User');
        $userB = m::mock('Claroline\CoreBundle\Entity\User');
        $users = array($userA, $userB);

        $group->shouldReceive('containsUser')->with($userA)->once()->andReturn(false);
        $group->shouldReceive('containsUser')->with($userB)->once()->andReturn(false);
        $group->shouldReceive('addUser')->with($userA)->once();
        $group->shouldReceive('addUser')->with($userB)->once();
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->addUsersToGroup($group, $users);
    }

    public function testRemoveUsersFromGroup()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $userA = m::mock('Claroline\CoreBundle\Entity\User');
        $userB = m::mock('Claroline\CoreBundle\Entity\User');
        $users = array($userA, $userB);

        $group->shouldReceive('removeUser')->with($userA)->once();
        $group->shouldReceive('removeUser')->with($userB)->once();
        $this->om->shouldReceive('persist')->with($group)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->removeUsersFromGroup($group, $users);
    }

    public function testImportUsers()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $manager = $this->getManager(array('addUsersToGroup'));
        $users = array(
            array(
                'firstname1',
                'lastname1',
                'username1',
                'password1',
                'email1@claroline.net',
                'code1'
            ),
            array(
                'firstname2',
                'lastname2',
                'username2',
                'password2',
                'email2@claroline.net',
                'code2'
            )
        );

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->userRepo->shouldReceive('findOneBy')
            ->with( array('username' => 'username1', 'firstName' => 'firstname1', 'lastName' => 'lastname1'))
            ->once()
            ->andReturn(null);
        $this->userRepo->shouldReceive('findOneBy')
            ->with( array('username' => 'username2', 'firstName' => 'firstname2', 'lastName' => 'lastname2'))
            ->once()
            ->andReturn($user);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $manager->shouldReceive('addUsersToGroup')
            ->with($group, array($user))
            ->once();

        $manager->importUsers($group, $users);
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:Group')->andReturn($this->groupRepo);
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:User')->andReturn($this->userRepo);

        if (count($mockedMethods) === 0) {
            return new GroupManager(
                $this->om,
                $this->pagerFactory,
                $this->translator
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return m::mock(
            'Claroline\CoreBundle\Manager\GroupManager' . $stringMocked,
            array(
                $this->om,
                $this->pagerFactory,
                $this->translator
            )
        );
    }
}
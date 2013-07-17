<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Role;
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

    public function testConvertGroupsToArray()
    {
        $groupA = m::mock('Claroline\CoreBundle\Entity\Group');
        $groupB = m::mock('Claroline\CoreBundle\Entity\Group');
        $roleAA = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleAB = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleBA = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleBB = m::mock('Claroline\CoreBundle\Entity\Role');

        $groupA->shouldReceive('getId')->once()->andReturn(1);
        $groupA->shouldReceive('getName')->once()->andReturn('group_1');
        $groupA->shouldReceive('getEntityRoles')->once()->andReturn(array($roleAA, $roleAB));
        $roleAA->shouldReceive('getTranslationKey')->once()->andReturn('ROLE_AA');
        $this->translator->shouldReceive('trans')
            ->with('ROLE_AA', array(), 'platform')
            ->once()
            ->andReturn('ROLE_AA_TRAD');
        $roleAB->shouldReceive('getTranslationKey')->once()->andReturn('ROLE_AB');
        $this->translator->shouldReceive('trans')
            ->with('ROLE_AB', array(), 'platform')
            ->once()
            ->andReturn('ROLE_AB_TRAD');
        $groupB->shouldReceive('getId')->once()->andReturn(2);
        $groupB->shouldReceive('getName')->once()->andReturn('group_2');
        $groupB->shouldReceive('getEntityRoles')->once()->andReturn(array($roleBA, $roleBB));
        $roleBA->shouldReceive('getTranslationKey')->once()->andReturn('ROLE_BA');
        $this->translator->shouldReceive('trans')
            ->with('ROLE_BA', array(), 'platform')
            ->once()
            ->andReturn('ROLE_BA_TRAD');
        $roleBB->shouldReceive('getTranslationKey')->once()->andReturn('ROLE_BB');
        $this->translator->shouldReceive('trans')
            ->with('ROLE_BB', array(), 'platform')
            ->once()
            ->andReturn('ROLE_BB_TRAD');

        $this->getManager()->convertGroupsToArray(array($groupA, $groupB));
    }

    public function testGetWorkspaceOutsiders()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = m::mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->groupRepo->shouldReceive('findWorkspaceOutsiders')
            ->with($workspace, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getWorkspaceOutsiders($workspace, 1));
    }

    public function testGetWorkspaceOutsidersByName()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = m::mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->groupRepo->shouldReceive('findWorkspaceOutsidersByName')
            ->with($workspace, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getWorkspaceOutsidersByName($workspace, 'search', 1));
    }

    public function testGetGroupsByWorkspace()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = m::mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->groupRepo->shouldReceive('findByWorkspace')
            ->with($workspace, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByWorkspace($workspace, 1));
    }

    public function testGetGroupsByWorkspaceAndName()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = m::mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->groupRepo->shouldReceive('findByWorkspaceAndName')
            ->with($workspace, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByWorkspaceAndName($workspace, 'search', 1));
    }

    public function testGetGroups()
    {
        $em = m::mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->groupRepo->shouldReceive('findAll')
            ->with(false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroups(1));
    }

    public function testGetGroupsByName()
    {
        $em = m::mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->groupRepo->shouldReceive('findByName')
            ->with('search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupsByName('search', 1));
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
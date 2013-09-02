<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Event\Log\LogUserCreateEvent;
use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;

class UserManagerTest extends MockeryTestCase
{
    private $userRepo;
    private $roleManager;
    private $workspaceManager;
    private $toolManager;
    private $strictDispatcher;
    private $dispatcher;
    private $personalWsTemplateFile;
    private $translator;
    private $ch;
    private $pagerFactory;
    private $om;

    public function setUp()
    {
        parent::setUp();
        $this->userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->strictDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->personalWsTemplateFile = 'template';
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->ch = $this->mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->pagerFactory = $this->mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    public function testInsertUser()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $this->om->shouldReceive('persist')->with($user)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->insertUser($user);
    }

    public function testCreateUser()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $manager->shouldReceive('setPersonalWorkspace')
            ->with($user)
            ->once()
            ->andReturn($workspace);
        $this->toolManager->shouldReceive('addRequiredToolsToUser')
            ->with($user)
            ->once();
        $this->roleManager->shouldReceive('setRoleToRoleSubject')
            ->with($user, PlatformRoles::USER)
            ->once();
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->om->shouldReceive('persist')->with($user)->once();
        $this->dispatcher->shouldReceive('dispatch')
            ->with('log', new LogUserCreateEvent($user))
            ->once();

        $manager->createUser($user);
    }

    public function testDeleteUser()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $this->om->shouldReceive('remove')->with($user)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->deleteUser($user);
    }

    public function testCreateUserWithRole()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('setPersonalWorkspace')->with($user)->once()->andReturn($workspace);
        $this->toolManager->shouldReceive('addRequiredToolsToUser')->with($user)->once();
        $this->roleManager->shouldReceive('setRoleToRoleSubject')->with($user, 'MY_ROLE')->once();
        $this->om->shouldReceive('persist')->with($user)->once();
        $this->dispatcher->shouldReceive('dispatch')->with('log', new LogUserCreateEvent($user))->once();

        $manager->createUserWithRole($user, 'MY_ROLE');
    }

    public function testInsertUserWithRoles()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleOne = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = new ArrayCollection(array($roleOne, $roleTwo));

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();

        $manager->shouldReceive('setPersonalWorkspace')
            ->with($user)
            ->once()
            ->andReturn($workspace);
        $this->toolManager->shouldReceive('addRequiredToolsToUser')
            ->with($user)
            ->once();
        $this->roleManager->shouldReceive('associateRoles')
            ->with($user, $roles)
            ->once();
        $this->om->shouldReceive('persist')
            ->with($user)
            ->once();
        $this->strictDispatcher->shouldReceive('dispatch')
            ->with('log', new LogUserCreateEvent($user))
            ->once();

        $manager->insertUserWithRoles($user, $roles);
    }

    public function testImportUsers()
    {
        $roleName = PlatformRoles::USER;
        $manager = $this->getManager(array('setPersonalWorkspace'));

        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $existingUser = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $users = array(
            array(
                'first_name_1',
                'last_name_1',
                'username_1',
                'pwd_1',
                'email_1',
                'code_1'
            ),
            array(
                'first_name_2',
                'last_name_2',
                'username_2',
                'pwd_2',
                'email_2',
                'code_2'
            )
        );

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->om->shouldReceive('factory')
            ->with('Claroline\CoreBundle\Entity\User')
            ->once()
            ->andReturn($user);

        $this->userRepo->shouldReceive('findUserByUsernameOrEmail')
            ->with('username_1', 'email_1')
            ->once()
            ->andReturn($existingUser);
        $this->userRepo->shouldReceive('findUserByUsernameOrEmail')
            ->with('username_2', 'email_2')
            ->once()
            ->andReturn(null);

        $user->shouldReceive('setFirstName')
            ->with('first_name_2')
            ->once();
        $user->shouldReceive('setLastName')
            ->with('last_name_2')
            ->once();
        $user->shouldReceive('setUsername')
            ->with('username_2')
            ->once();
        $user->shouldReceive('setPlainPassword')
            ->with('pwd_2')
            ->once();
        $user->shouldReceive('setMail')
            ->with('email_2')
            ->once();
        $user->shouldReceive('setAdministrativeCode')
            ->with('code_2')
            ->once();
        $user->shouldReceive('setPhone')
            ->with(null)
            ->once();
        $this->toolManager->shouldReceive('addRequiredToolsToUser')
            ->with($user)
            ->once();
        $this->roleManager->shouldReceive('setRoleToRoleSubject')
            ->with($user, $roleName)
            ->once();
        $this->om->shouldReceive('persist')
            ->with($user)
            ->once();
        $this->strictDispatcher->shouldReceive('dispatch')
            ->with('log', new LogUserCreateEvent($user))
            ->once();

        $manager->importUsers($users);
    }

    public function testConvertUsersToArray()
    {
        $userA = $this->mock('Claroline\CoreBundle\Entity\User');
        $userB = $this->mock('Claroline\CoreBundle\Entity\User');
        $roleAA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleAB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleBA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleBB = $this->mock('Claroline\CoreBundle\Entity\Role');

        $userA->shouldReceive('getId')->once()->andReturn(1);
        $userA->shouldReceive('getUsername')->once()->andReturn('username_1');
        $userA->shouldReceive('getLastName')->once()->andReturn('lastname_1');
        $userA->shouldReceive('getFirstName')->once()->andReturn('firstname_1');
        $userA->shouldReceive('getAdministrativeCode')->once()->andReturn('code_1');
        $userA->shouldReceive('getEntityRoles')->once()->andReturn(array($roleAA, $roleAB));
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
        $userB->shouldReceive('getId')->once()->andReturn(2);
        $userB->shouldReceive('getUsername')->once()->andReturn('username_2');
        $userB->shouldReceive('getLastName')->once()->andReturn('lastname_2');
        $userB->shouldReceive('getFirstName')->once()->andReturn('firstname_2');
        $userB->shouldReceive('getAdministrativeCode')->once()->andReturn('code_2');
        $userB->shouldReceive('getEntityRoles')->once()->andReturn(array($roleBA, $roleBB));
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

        $this->getManager()->convertUsersToArray(array($userA, $userB));
    }

    public function testGetUserByUserName()
    {
        $this->userRepo->shouldReceive('loadUserByUsername')
            ->once()
            ->with('john')
            ->andReturn('User');
        $manager = $this->getManager();
        $this->assertEquals('User', $manager->getUserByUsername('john'));
    }

    public function testRefreshUser()
    {
        $user = $this->mock('Symfony\Component\Security\Core\User\UserInterface');

        $this->userRepo->shouldReceive('refreshUser')
            ->once()
            ->with($user);

        $this->getManager()->refreshUser($user);
    }

    public function testGetUserByWorkspaceAndRole()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $userA = $this->mock('Claroline\CoreBundle\Entity\User');
        $userB = $this->mock('Claroline\CoreBundle\Entity\User');
        $users = array($userA, $userB);

        $this->userRepo->shouldReceive('findByWorkspaceAndRole')
            ->once()
            ->with($workspace, $role)
            ->andReturn($users);

        $this->assertEquals($users, $this->getManager()->getUserByWorkspaceAndRole($workspace, $role));
    }

    public function testGetWorkspaceOutsidersByName()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findWorkspaceOutsidersByName')
            ->with($workspace, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getWorkspaceOutsidersByName($workspace, 'search', 1));
    }

    public function testGetWorkspaceOutsiders()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findWorkspaceOutsiders')
            ->with($workspace, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getWorkspaceOutsiders($workspace, 1));
    }

    public function testGetAllUsers()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findAll')
            ->with(false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getAllUsers(1));
    }

    public function testGetUsersByName()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByName')
            ->with('search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByName('search', 1));
    }

    public function testGetUsersByGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByGroup')
            ->with($group, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByGroup($group, 1));
    }

    public function testGetUsersByNameAndGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByNameAndGroup')
            ->with('search', $group, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByNameAndGroup('search', $group, 1));
    }

    public function testGetUsersByWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByWorkspace')
            ->with($workspace, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByWorkspace($workspace, 1));
    }

    public function testGetUsersByWorkspaceAndName()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByWorkspaceAndName')
            ->with($workspace, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByWorkspaceAndName($workspace, 'search', 1));
    }

    public function testGetGroupOutsiders()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findGroupOutsiders')
            ->with($group, false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupOutsiders($group, 1));
    }

    public function testGetGroupOutsidersByName()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findGroupOutsidersByName')
            ->with($group, 'search', false)
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getGroupOutsidersByName($group, 1, 'search'));
    }

    public function testGetAllUsersExcept()
    {
        $excludedUser = $this->mock('Claroline\CoreBundle\Entity\User');
        $users = array('userA', 'userB');

        $this->userRepo->shouldReceive('findAllExcept')
            ->with($excludedUser)
            ->once()
            ->andReturn($users);

        $this->assertEquals($users, $this->getManager()->getAllUsersExcept($excludedUser));
    }

    public function testGetUsersByUsernames()
    {
        $users = array('userA', 'userB');
        $usernames = array('username_a', 'username_b');

        $this->userRepo->shouldReceive('findByUsernames')
            ->with($usernames)
            ->once()
            ->andReturn($users);

        $this->assertEquals($users, $this->getManager()->getUsersByUsernames($usernames));
    }

    public function testGetNbUsers()
    {
        $this->userRepo->shouldReceive('count')
            ->once()
            ->andReturn(4);

        $this->assertEquals(4, $this->getManager()->getNbUsers());
    }

    public function testGetUsersByIds()
    {
        $ids = array(1, 3, 4);
        $users = array('userA', 'userC', 'userD');

        $this->om->shouldReceive('findByIds')
            ->with('Claroline\CoreBundle\Entity\User', $ids)
            ->once()
            ->andReturn($users);

        $this->assertEquals($users, $this->getManager()->getUsersByIds($ids));
    }

    public function testGetUsersEnrolledInMostWorkspaces()
    {
        $max = 3;
        $users = array('userA', 'userB', 'userC');

        $this->userRepo->shouldReceive('findUsersEnrolledInMostWorkspaces')
            ->with($max)
            ->once()
            ->andReturn($users);

        $this->assertEquals($users, $this->getManager()->getUsersEnrolledInMostWorkspaces($max));
    }

    public function testGetUsersOwnersOfMostWorkspaces()
    {
        $max = 3;
        $users = array('userA', 'userB', 'userC');

        $this->userRepo->shouldReceive('findUsersOwnersOfMostWorkspaces')
            ->with($max)
            ->once()
            ->andReturn($users);

        $this->assertEquals($users, $this->getManager()->getUsersOwnersOfMostWorkspaces($max));
    }

    public function testGetUserById()
    {
        $userId = 1;
        $user = 'User';

        $this->userRepo->shouldReceive('find')
            ->with($userId)
            ->once()
            ->andReturn($user);

        $this->assertEquals($user, $this->getManager()->getUserById($userId));
    }

    public function testSetPersonalWorkspaceUser()
    {
        $this->markTestSkipped('How to test the Configuration::fromTemplate ?');
    }

    public function testGetUsersByRoles()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);

        $this->userRepo->shouldReceive('findByRoles')
            ->with($roles, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByRoles($roles, 1));
    }

    public function testGetOutsidersByWorkspaceRole()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();

        $this->userRepo->shouldReceive('findOutsidersByWorkspaceRoles')
            ->with($roles, $workspace, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getOutsidersByWorkspaceRoles($roles, $workspace, 1));
    }

    public function testGetUsersByRoleAndName()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);

        $this->userRepo->shouldReceive('findByRolesAndName')
            ->with($roles, 'name', true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByRolesAndName($roles, 'name', 1));
    }

    public function testGetOutsidersByWorkspaceAndRole()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();

        $this->userRepo->shouldReceive('findOutsidersByWorkspaceRolesAndName')
            ->with($roles, 'name', $workspace, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            'pager',
            $this->getManager()->getOutsidersByWorkspaceRolesAndName($roles, 'name', $workspace, 1)
        );
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:User')->andReturn($this->userRepo);

        if (count($mockedMethods) === 0) {
            return new UserManager(
                $this->roleManager,
                $this->workspaceManager,
                $this->toolManager,
                $this->strictDispatcher,
                $this->dispatcher,
                $this->personalWsTemplateFile,
                $this->translator,
                $this->ch,
                $this->pagerFactory,
                $this->om
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\UserManager' . $stringMocked,
            array(
                $this->roleManager,
                $this->workspaceManager,
                $this->toolManager,
                $this->strictDispatcher,
                $this->dispatcher,
                $this->personalWsTemplateFile,
                $this->translator,
                $this->ch,
                $this->pagerFactory,
                $this->om
            )
        );
    }
}

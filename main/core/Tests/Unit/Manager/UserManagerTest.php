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
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;

class UserManagerTest extends MockeryTestCase
{
    private $userRepo;
    private $roleManager;
    private $workspaceManager;
    private $toolManager;
    private $strictDispatcher;
    private $personalWsTemplateFile;
    private $translator;
    private $ch;
    private $sc;
    private $pagerFactory;
    private $om;
    private $mailManager;
    private $validator;

    public function setUp()
    {
        parent::setUp();
        $this->userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->mailManager = $this->mock('Claroline\CoreBundle\Manager\MailManager');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->strictDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->personalWsTemplateFile = 'template';
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->ch = $this->mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->sc = $this->mock('Symfony\Component\Security\Core\SecurityContext');
        $this->pagerFactory = $this->mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->validator = $this->mock('Symfony\Component\Validator\ValidatorInterface');
    }

    public function testCreateUser()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');

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
        $this->mailManager->shouldReceive('isMailerAvailable')->once()->andReturn(true);
        $this->mailManager->shouldReceive('sendCreationMessage')->once()->with($user);

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->om->shouldReceive('persist')->with($user)->once();
        $this->strictDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserCreate', array($user))
            ->once();

        $this->mailManager->shouldReceive('isMailerAvailable')->andReturn(false);

        $manager->createUser($user);
    }

    public function testDeleteUser()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $user->shouldReceive('setMail')->once();
        $user->shouldReceive('setFirstName')->once();
        $user->shouldReceive('setLastName')->once();
        $user->shouldReceive('setPlainPassword')->once();
        $user->shouldReceive('setUsername')->once();
        $user->shouldReceive('setIsEnabled')->once()->with(false);
        $user->shouldReceive('getId')->andReturn('1');
        $this->strictDispatcher->shouldReceive('dispatch')->with('delete_user', 'DeleteUser', array($user))->once();
        $this->om->shouldReceive('persist')->once()->with($user);
        $this->om->shouldReceive('flush');

        $this->getManager()->deleteUser($user);
    }

    public function testInsertUserWithRoles()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
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
        $this->roleManager
            ->shouldReceive('setRoleToRoleSubject')
            ->with($user, PlatformRoles::USER)
            ->once();
        $this->roleManager->shouldReceive('associateRoles')
            ->with($user, $roles)
            ->once();
        $this->mailManager->shouldReceive('isMailerAvailable')->once()->andReturn(true);
        $this->mailManager->shouldReceive('sendCreationMessage')->once()->with($user);
        $this->om->shouldReceive('persist')
            ->with($user)
            ->once();
        $this->strictDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserCreate', array($user))
            ->once();

        $this->mailManager->shouldReceive('isMailerAvailable')->andReturn(false);

        $manager->insertUserWithRoles($user, $roles);
    }

    public function testImportUsers()
    {
        $manager = $this->getManager(array('createUser'));

        $user = $this->mock('Claroline\CoreBundle\Entity\User');

        $users = array(
            array(
                'first_name_2',
                'last_name_2',
                'username_2',
                'pwd_2',
                'email_2',
                'code_2',
            ),
        );

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->om->shouldReceive('factory')
            ->with('Claroline\CoreBundle\Entity\User')
            ->once()
            ->andReturn($user);

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
        $manager->shouldReceive('createUser')->once()->with($user);
        $this->strictDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserCreate', array($user))
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

    public function testGetAllUsers()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findAll')
            ->with(false, 'id')
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 20)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getAllUsers(1));
    }

    public function testGetUsersByName()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByName')
            ->with('search', false, 'id')
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 20)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByName('search', 1));
    }

    public function testGetUsersByNameAndGroup()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);

        $this->userRepo->shouldReceive('findByNameAndGroup')
            ->with('search', $group, false, 'id')
            ->once()
            ->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 20)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getUsersByNameAndGroup('search', $group, 1));
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
            ->with($query, 1, 20)
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
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();

        $this->userRepo->shouldReceive('findOutsidersByWorkspaceRoles')
            ->with($roles, $workspace, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 20)
            ->once()
            ->andReturn('pager');

        $this->assertEquals('pager', $this->getManager()->getOutsidersByWorkspaceRoles($roles, $workspace, 1));
    }

    public function testGetOutsidersByWorkspaceAndRole()
    {
        $em = $this->mock('Doctrine\ORM\EntityManager');
        $query = new \Doctrine\ORM\Query($em);
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();

        $this->userRepo->shouldReceive('findOutsidersByWorkspaceRolesAndName')
            ->with($roles, 'name', $workspace, true)
            ->once()
            ->andReturn($query);

        $this->pagerFactory->shouldReceive('createPager')
            ->with($query, 1, 20)
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
                $this->personalWsTemplateFile,
                $this->mailManager,
                $this->om,
                $this->pagerFactory,
                $this->ch,
                $this->roleManager,
                $this->sc,
                $this->strictDispatcher,
                $this->toolManager,
                $this->translator,
                $this->validator,
                $this->workspaceManager
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\UserManager'.$stringMocked,
            array(
                $this->personalWsTemplateFile,
                $this->mailManager,
                $this->om,
                $this->pagerFactory,
                $this->ch,
                $this->roleManager,
                $this->sc,
                $this->strictDispatcher,
                $this->toolManager,
                $this->translator,
                $this->validator,
                $this->workspaceManager,
            )
        );
    }
}

<?php

namespace Claroline\CoreBundle\Manager;

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
    private $ed;
    private $personalWsTemplateFile;
    private $trans;
    private $ch;
    private $pagerFactory;
    private $om;

    public function setUp()
    {
        parent::setUp();

        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->workspaceManager = m::mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->toolManager = m::mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->ed = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->personalWsTemplateFile = 'template';
        $this->trans = m::mock('Symfony\Component\Translation\Translator');
        $this->ch = m::mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->pagerFactory = m::mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    public function testInsertUser()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $this->om->shouldReceive('persist')->with($user)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->insertUser($user);
    }

    public function testCreateUser()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

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
        $this->ed->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserCreate', array($user))
            ->once();

        $manager->createUser($user);
    }

    public function testDeleteUser()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $this->om->shouldReceive('remove')->with($user)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->deleteUser($user);
    }

    public function testCreateUserWithRole()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('setPersonalWorkspace')->with($user)->once()->andReturn($workspace);
        $this->toolManager->shouldReceive('addRequiredToolsToUser')->with($user)->once();
        $this->roleManager->shouldReceive('setRoleToRoleSubject')->with($user, 'MY_ROLE')->once();
        $this->om->shouldReceive('persist')->with($user)->once();
        $this->ed->shouldReceive('dispatch')->with('log', 'Log\LogUserCreate', array($user))->once();

        $manager->createUserWithRole($user, 'MY_ROLE');
    }

    public function testInsertUserWithRoles()
    {
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleOne = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = m::mock('Claroline\CoreBundle\Entity\Role');
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
        $this->ed->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserCreate', array($user))
            ->once();

        $manager->insertUserWithRoles($user, $roles);
    }

    public function testImportUsers()
    {
//        $this->markTestSkipped('Cannot because it contains magic methods');
        $roleName = PlatformRoles::USER;
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $existingUser = m::mock('Claroline\CoreBundle\Entity\User');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
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

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->userRepo->shouldReceive('findOneByUsername')
            ->with('username_1')
            ->once()
            ->andReturn($existingUser);
        $this->userRepo->shouldReceive('findOneByUsername')
            ->with('username_2')
            ->once()
            ->andReturn(null);
        m::getConfiguration()->allowMockingNonExistentMethods(false);

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
        $manager->shouldReceive('setPersonalWorkspace')
            ->with($user)
            ->once()
            ->andReturn($workspace);
        $this->toolManager->shouldReceive('addRequiredToolsToUser')
            ->with($user)
            ->once();
        $this->roleManager->shouldReceive('setRoleToRoleSubject')
            ->with($user, $roleName)
            ->once();
        $this->om->shouldReceive('persist')
            ->with($user)
            ->once();
        $this->ed->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserCreate', array($user))
            ->once();

        $manager->importUsers($users);
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

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:User')->andReturn($this->userRepo);

        if (count($mockedMethods) === 0) {
            return new UserManager(
                $this->roleManager,
                $this->workspaceManager,
                $this->toolManager,
                $this->ed,
                $this->personalWsTemplateFile,
                $this->trans,
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

        return m::mock(
            'Claroline\CoreBundle\Manager\UserManager' . $stringMocked,
            array(
                $this->roleManager,
                $this->workspaceManager,
                $this->toolManager,
                $this->ed,
                $this->personalWsTemplateFile,
                $this->trans,
                $this->ch,
                $this->pagerFactory,
                $this->om
            )
        );
    }
}

<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;

class UserManagerTest extends MockeryTestCase
{
    private $userRepo;
    private $writer;
    private $roleManager;
    private $workspaceManager;
    private $toolManager;
    private $ed;
    private $personalWsTemplateFile;
    private $trans;
    private $ch;

    public function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->workspaceManager = m::mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->toolManager = m::mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->ed = m::mock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->personalWsTemplateFile = 'template';
        $this->trans = m::mock('Symfony\Component\Translation\Translator');
        $this->ch = m::mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
    }

    public function testInsert()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $this->writer->shouldReceive('create')->with($user)->once();

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
        $this->writer->shouldReceive('create')
            ->with($user)
            ->once();
        $user->shouldReceive('getLastName')
            ->once();
        $user->shouldReceive('getFirstName')
            ->once();
        $this->ed->shouldReceive('dispatch')
            ->with('log', anInstanceOf('Claroline\CoreBundle\Library\Event\LogUserCreateEvent'))
            ->once();

        $manager->createUser($user);
    }

    public function testDeleteUser()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $this->writer->shouldReceive('delete')->with($user)->once();

        $this->getManager()->deleteUser($user);
    }

    public function testCreateUserWithRole()
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
            ->with($user, 'MY_ROLE')
            ->once();
        $this->writer->shouldReceive('create')
            ->with($user)
            ->once();
        $user->shouldReceive('getLastName')
            ->once();
        $user->shouldReceive('getFirstName')
            ->once();
        $this->ed->shouldReceive('dispatch')
            ->with('log', anInstanceOf('Claroline\CoreBundle\Library\Event\LogUserCreateEvent'))
            ->once();

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
        $this->writer->shouldReceive('create')
            ->with($user)
            ->once();
        $user->shouldReceive('getLastName')
            ->once();
        $user->shouldReceive('getFirstName')
            ->once();
        $this->ed->shouldReceive('dispatch')
            ->with('log', anInstanceOf('Claroline\CoreBundle\Library\Event\LogUserCreateEvent'))
            ->once();

        $manager->insertUserWithRoles($user, $roles);
    }

    public function testImportUsers()
    {
        $this->markTestSkipped('Not tested thoroughly');
        $roleName = PlatformRoles::USER;
        $manager = $this->getManager(array('setPersonalWorkspace'));
        $workspaceA = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $workspaceB = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $users = array(
            array(
                'first_name_1',
                'last_name_1',
                'username_1',
                'pwd_1',
                'code_1',
                'email_1',
            ),
            array(
                'first_name_2',
                'last_name_2',
                'username_2',
                'pwd_2',
                'code_2'
            )
        );

        $manager->shouldReceive('setPersonalWorkspace')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\User'))
            ->once()
            ->andReturn($workspaceA);
        $manager->shouldReceive('setPersonalWorkspace')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\User'))
            ->once()
            ->andReturn($workspaceB);
        $this->toolManager->shouldReceive('addRequiredToolsToUser')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\User'))
            ->times(2);
        $this->roleManager->shouldReceive('setRoleToRoleSubject')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\User'), $roleName)
            ->times(2);
        $this->writer->shouldReceive('create')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\User'))
            ->times(2);
        $this->ed->shouldReceive('dispatch')
            ->with('log', anInstanceOf('Claroline\CoreBundle\Library\Event\LogUserCreateEvent'))
            ->times(2);

        $manager->importUsers($users);
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new UserManager(
                $this->userRepo,
                $this->writer,
                $this->roleManager,
                $this->workspaceManager,
                $this->toolManager,
                $this->ed,
                $this->personalWsTemplateFile,
                $this->trans,
                $this->ch
            );
        } else {
            $stringMocked = '[';
            $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return m::mock(
                'Claroline\CoreBundle\Manager\UserManager' . $stringMocked,
                array(
                    $this->userRepo,
                    $this->writer,
                    $this->roleManager,
                    $this->workspaceManager,
                    $this->toolManager,
                    $this->ed,
                    $this->personalWsTemplateFile,
                    $this->trans,
                    $this->ch
                )
            );
        }
    }
}
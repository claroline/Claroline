<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;

class RoleManagerTest extends MockeryTestCase
{
    private $roleRepo;
    private $userRepo;
    private $groupRepo;
    private $securityContext;
    private $om;
    private $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->groupRepo = m::mock('Claroline\CoreBundle\Repository\GroupRepository');
        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->dispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
    }

    public function testCreateWorkspaceRole()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Role')
            ->andReturn($role);
        $this->om->shouldReceive('persist')->with($role)->once();
        $role->shouldReceive('setName')->with('ROLE_WS_USER');
        $role->shouldReceive('setTranslationKey')->with('user');
        $role->shouldReceive('setReadOnly')->with(false);
        $role->shouldReceive('setType')->with(Role::WS_ROLE);
        $role->shouldReceive('setWorkspace')->with($workspace);
        $this->om->shouldReceive('flush')->once();

        $this->assertEquals(
            $role,
            $this->getManager()->createWorkspaceRole('ROLE_WS_USER', 'user', $workspace)
        );
    }

    public function testCreateBaseRole()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Role')
            ->andReturn($role);

        $this->om->shouldReceive('persist')->with($role)->once();
        $role->shouldReceive('setName')->with('ROLE_WS_USER');
        $role->shouldReceive('setTranslationKey')->with('user');
        $role->shouldReceive('setReadOnly')->with(true);
        $role->shouldReceive('setType')->with(Role::PLATFORM_ROLE);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->createBaseRole('ROLE_WS_USER', 'user');
    }

    public function testCreateCustomRole()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Role')
            ->andReturn($role);

        $this->om->shouldReceive('persist')->with($role)->once();
        $role->shouldReceive('setName')->with('ROLE_WS_USER');
        $role->shouldReceive('setTranslationKey')->with('user');
        $role->shouldReceive('setReadOnly')->with(false);
        $role->shouldReceive('setType')->with(Role::CUSTOM_ROLE);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->createCustomRole('ROLE_WS_USER', 'user');
    }

    public function testSetRoleToRoleSubject()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $ars = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $roleName = 'ROLE_WS_USER';

        $this->roleRepo->shouldReceive('findOneBy')->with(array('name' => $roleName))
            ->once()->andReturn($role);
        $ars->shouldReceive('addRole')->with($role)->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->setRoleToRoleSubject($ars, $roleName);
    }

    public function testAssociateRole()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $ars = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('addRole')->with($role)->once();
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->dispatcher->shouldReceive('dispatch')->once();

        $this->getManager()->associateRole($ars, $role);
    }

    public function testDissociateRole()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $ars = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('removeRole')->with($role)->once();
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->dispatcher->shouldReceive('dispatch')->once();


        $this->getManager()->dissociateRole($ars, $role);
    }

    public function testAssociateRoles()
    {
        $manager = $this->getManager(array('associateRole'));
        $roleOne = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $roles = new ArrayCollection(array($roleOne, $roleTwo));
        $manager->shouldReceive('associateRole')->times(2);
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush');

        $manager->associateRoles($ars, $roles);
    }

    public function testInitBaseWorkspaceRole()
    {
        $manager = $this->getManager(array('createWorkspaceRole'));
        $roleUser = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleManager = m::mock('Claroline\CoreBundle\Entity\Role');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roles = array(
            'ROLE_WS_USER' => 'user',
            'ROLE_WS_MANAGER' => 'superuser'
        );

        $this->om->shouldReceive('startFlushSuite')->once();
        $workspace->shouldReceive('getGuid')->times(2)->andReturn(1);
        $manager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_USER_1', 'user', $workspace, false)
            ->once()
            ->andReturn($roleUser);
        $manager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_MANAGER_1', 'superuser', $workspace, true)
            ->once()
            ->andReturn($roleManager);
        $this->om->shouldReceive('endFlushSuite')->once();

        $result = $manager->initWorkspaceBaseRole($roles, $workspace);

        $expectedResult = array(
            'ROLE_WS_USER' => $roleUser,
            'ROLE_WS_MANAGER' => $roleManager
        );
        $this->assertEquals($result, $expectedResult);
    }

    public function testRemove()
    {
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $role->shouldReceive('isReadOnly')->once()->andReturn(false);
        $this->om->shouldReceive('remove')->with($role);
        $this->om->shouldReceive('flush');
        $this->getManager()->remove($role);
    }

    /**
     * @expectedException \LogicException
     */
    public function testCheckWorkspaceRoleEditionThrowsExceptionForUser( )
    {
        $this->markTestSkipped();
        $roleManager = $this->getManager(array('getManagerRole'));
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
        $managerRole = m::mock('Claroline\CoreBundle\Entity\Role');
        $collaboratorRole = m::mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($collaboratorRole);
        $user = m::mock('Claroline\CoreBundle\Entity\User');

        $roleManager->shouldReceive('getManagerRole')->once()->with($workspace)->andReturn($managerRole);
        $managerRole->shouldReceive('getName')->andReturn('ROLE_WS_MANAGER');
        $user->shouldReceive('hasRole')->with('ROLE_WS_MANAGER')->andReturn(true);
        $this->groupRepo->shouldReceive('findByRole')->andReturn(array());
        $this->userRepo->shouldReceive('findByRole')->andReturn(array($user));

        $roleManager->checkWorkspaceRoleEditionIsValid(array($user), $workspace, $roles);
    }

    public function testDissociateWorkspaceRole()
    {

    }

    public function testResetWorkspaceRoles()
    {

    }

    public function testEditSubjectWorkspaceRoles()
    {

    }

    /**
     * @expectedException \Claroline\CoreBundle\Manager\Exception\RoleReadOnlyException
     */
    public function testRemoveThrowsExceptionIfReadOnly()
    {
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $role->shouldReceive('isReadOnly')->once()->andReturn(true);
        $this->getManager()->remove($role);
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')
            ->once()->andReturn($this->roleRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:User')
            ->once()->andReturn($this->userRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Group')
            ->once()->andReturn($this->groupRepo);

        if (count($mockedMethods) === 0) {
            return new RoleManager($this->securityContext, $this->om, $this->dispatcher);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\RoleManager' . $stringMocked,
            array($this->securityContext, $this->om, $this->dispatcher)
        );
    }
}

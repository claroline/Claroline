<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;

class RoleManagerTest extends MockeryTestCase
{
    private $roleRepo;
    private $securityContext;
    private $om;

    public function setUp()
    {
        parent::setUp();

        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    public function testCreateWorkspaceRole()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
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
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
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
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
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
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
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
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('addRole')->with($role)->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->associateRole($ars, $role);
    }

    public function testDissociateRole()
    {
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('removeRole')->with($role)->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->dissociateRole($ars, $role);
    }

    public function testAssociateRoles()
    {
        $roleOne = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $roles = new ArrayCollection(array($roleOne, $roleTwo));

        $ars->shouldReceive('addRole')->with($roleOne)->once();
        $ars->shouldReceive('addRole')->with($roleTwo)->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush');

        $this->getManager()->associateRoles($ars, $roles);
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

        if (count($mockedMethods) === 0) {
            return new RoleManager($this->securityContext, $this->om);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return m::mock(
            'Claroline\CoreBundle\Manager\RoleManager' . $stringMocked,
            array($this->securityContext, $this->om)
        );
    }
}

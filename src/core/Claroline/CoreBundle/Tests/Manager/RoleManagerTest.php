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

        $this->roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->securityContext = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
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
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->associateRole($ars, $role);
    }

    public function testDissociateRole()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $ars = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('removeRole')->with($role)->once();
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->dissociateRole($ars, $role);
    }

    public function testAssociateRoles()
    {
        $roleOne = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = $this->mock('Claroline\CoreBundle\Entity\Role');
        $ars = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
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
        $roleUser = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleSuperUser = $this->mock('Claroline\CoreBundle\Entity\Role');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roles = array(
            'ROLE_WS_USER' => 'user',
            'ROLE_WS_SUPERUSER' => 'superuser'
        );

        $this->om->shouldReceive('startFlushSuite')->once();
        $workspace->shouldReceive('getGuid')->times(2)->andReturn(1);
        $manager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_USER_1', 'user', $workspace, false)
            ->once()
            ->andReturn($roleUser);
        $manager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_SUPERUSER_1', 'superuser', $workspace, false)
            ->once()
            ->andReturn($roleSuperUser);
        $this->om->shouldReceive('endFlushSuite')->once();

        $result = $manager->initWorkspaceBaseRole($roles, $workspace);

        $expectedResult = array(
            'ROLE_WS_USER' => $roleUser,
            'ROLE_WS_SUPERUSER' => $roleSuperUser
        );
        $this->assertEquals($result, $expectedResult);
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

        return $this->mock(
            'Claroline\CoreBundle\Manager\RoleManager' . $stringMocked,
            array($this->securityContext, $this->om)
        );
    }
}

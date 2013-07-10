<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;

class RoleManagerTest extends MockeryTestCase
{
    private $writer;
    private $roleRepo;

    public function setUp()
    {
        parent::setUp();
        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
    }

    public function testCreateWorkspaceRole()
    {
        $this->markTestSkipped('Not tested thoroughly');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleName = 'ROLE_WS_USER';
        $roleKey = 'user';

        $this->writer->shouldReceive('create')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Role'))
            ->once();

        $this->getManager()->createWorkspaceRole($roleName, $roleKey, $workspace);
    }

    public function testCreateBaseRole()
    {
        $this->markTestSkipped('Not tested thoroughly');
        $roleName = 'ROLE_WS_USER';
        $roleKey = 'user';

        $this->writer->shouldReceive('create')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Role'))
            ->once();

        $this->getManager()->createBaseRole($roleName, $roleKey);
    }

    public function testCreateCustomRole()
    {
        $this->markTestSkipped('Not tested thoroughly');
        $roleName = 'ROLE_WS_USER';
        $roleKey = 'user';

        $this->writer->shouldReceive('create')
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Role'))
            ->once();

        $this->getManager()->createCustomRole($roleName, $roleKey);
    }

    public function testSetRoleToRoleSubject()
    {
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $roleName = 'ROLE_WS_USER';

        $this->roleRepo->shouldReceive('findOneBy')
            ->with(array('name' => $roleName))
            ->once()
            ->andReturn($role);
        $ars->shouldReceive('addRole')
            ->with($role)
            ->once();
        $this->writer->shouldReceive('update')
            ->with($ars)
            ->once();

        $this->getManager()->setRoleToRoleSubject($ars, $roleName);
    }

    public function testAssociateRole()
    {
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('addRole')
            ->with($role)
            ->once();
        $this->writer->shouldReceive('update')
            ->with($ars)
            ->once();

        $this->getManager()->associateRole($ars, $role);
    }

    public function testDissociateRole()
    {
        $role = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');

        $ars->shouldReceive('removeRole')
            ->with($role)
            ->once();
        $this->writer->shouldReceive('update')
            ->with($ars)
            ->once();

        $this->getManager()->dissociateRole($ars, $role);
    }

    public function testAssociateRoles()
    {
        $roleOne = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $roles = new ArrayCollection(array($roleOne, $roleTwo));

        $ars->shouldReceive('addRole')
            ->with($roleOne)
            ->once();
        $ars->shouldReceive('addRole')
            ->with($roleTwo)
            ->once();
        $this->writer->shouldReceive('update')
            ->with($ars)
            ->once();

        $this->getManager()->associateRoles($ars, $roles);
    }

    public function testInitBaseWorkspaceRole()
    {
        $manager = $this->getManager(array('createWorkspaceRole'));
        $roleUser = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleSuperUser = m::mock('Claroline\CoreBundle\Entity\Role');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roles = array(
            'ROLE_WS_USER' => 'user',
            'ROLE_WS_SUPERUSER' => 'superuser'
        );

        $workspace->shouldReceive('getId')->times(2)->andReturn(1);
        $manager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_USER_1', 'user', $workspace, false)
            ->once()
            ->andReturn($roleUser);
        $manager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_SUPERUSER_1', 'superuser', $workspace, false)
            ->once()
            ->andReturn($roleSuperUser);

        $result = $manager->initWorkspaceBaseRole($roles, $workspace);

        $expectedResult = array(
            'ROLE_WS_USER' => $roleUser,
            'ROLE_WS_SUPERUSER' => $roleSuperUser
        );
        $this->assertEquals($result, $expectedResult);
    }

    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new RoleManager($this->writer, $this->roleRepo);
        } else {
            $stringMocked = '[';
            $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return m::mock(
                'Claroline\CoreBundle\Manager\RoleManager' . $stringMocked,
                array($this->writer, $this->roleRepo)
            );
        }
    }
}
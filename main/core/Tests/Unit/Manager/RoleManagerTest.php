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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;

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
        $this->om = m::mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->dispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
    }

    public function testCreateWorkspaceRole()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
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

        $this->roleRepo->shouldReceive('findOneBy')->with(['name' => $roleName])
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
        $manager = $this->getManager(['associateRole']);
        $roleOne = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleTwo = m::mock('Claroline\CoreBundle\Entity\Role');
        $ars = m::mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $roles = new ArrayCollection([$roleOne, $roleTwo]);
        $manager->shouldReceive('associateRole')->times(2);
        $this->om->shouldReceive('persist')->with($ars)->once();
        $this->om->shouldReceive('flush');

        $manager->associateRoles($ars, $roles);
    }

    public function testAssociateRoleToMultipleSubjects()
    {
        $arsA = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $arsB = $this->mock('Claroline\CoreBundle\Entity\AbstractRoleSubject');
        $subjects = [$arsA, $arsB];
        $role = new Role();

        $arsA->shouldReceive('addRole')->with($role)->once();
        $arsB->shouldReceive('addRole')->with($role)->once();
        $this->om->shouldReceive('persist')->with($arsA)->once();
        $this->om->shouldReceive('persist')->with($arsB)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->associateRoleToMultipleSubjects($subjects, $role);
    }

    public function testInitBaseWorkspaceRole()
    {
        $manager = $this->getManager(['createWorkspaceRole']);
        $roleUser = m::mock('Claroline\CoreBundle\Entity\Role');
        $roleManager = m::mock('Claroline\CoreBundle\Entity\Role');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $roles = [
            'ROLE_WS_USER' => 'user',
            'ROLE_WS_MANAGER' => 'superuser',
        ];

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

        $expectedResult = [
            'ROLE_WS_USER' => $roleUser,
            'ROLE_WS_MANAGER' => $roleManager,
        ];
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
     * @expectedException \Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException
     */
    public function testCheckWorkspaceRoleEditionThrowsExceptionForUser()
    {
        $roleManager = $this->getManager(['getManagerRole']);
        $workspace = new Workspace();
        $managerRole = m::mock('Claroline\CoreBundle\Entity\Role');
        $roles = [$managerRole];
        $user = m::mock('Claroline\CoreBundle\Entity\User');

        $roleManager->shouldReceive('getManagerRole')->once()->with($workspace)->andReturn($managerRole);
        $managerRole->shouldReceive('getName')->andReturn('ROLE_WS_MANAGER');
        $user->shouldReceive('hasRole')->with('ROLE_WS_MANAGER')->andReturn(true);
        $this->groupRepo->shouldReceive('findByRoles')->andReturn([]);
        $this->userRepo->shouldReceive('findByRoles')->andReturn([$user]);

        $roleManager->checkWorkspaceRoleEditionIsValid([$user], $workspace, $roles);
    }

    public function testGetRole()
    {
        $this->roleRepo->shouldReceive('find')->with(1)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRole(1));
    }

    public function testResetRoles()
    {
        $roleUser = new Role();
        $pfRole = new Role();
        $roles = [$pfRole];
        $user = $this->mock('\Claroline\CoreBundle\Entity\User');

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->roleRepo->shouldReceive('findOneByName')->with('ROLE_USER')
            ->andReturn($roleUser);
        $this->roleRepo->shouldReceive('findPlatformRoles')->with($user)
            ->once()->andReturn($roles);

        $user->shouldReceive('removeRole')->once()->with($pfRole);
        $this->om->shouldReceive('persist')->once()->with($user);
        $this->om->shouldReceive('flush');
        $this->getManager()->resetRoles($user);
    }

    public function testDissociateWorkspaceRole()
    {
        $manager = $this->getManager(['checkWorkspaceRoleEditionIsValid', 'dissociateRole']);

        $subject = new User();
        $workspace = new Workspace();
        $role = new Role();

        $manager->shouldReceive('checkWorkspaceRoleEditionIsValid')->once()
            ->with([$subject], $workspace, [$role]);
        $manager->shouldReceive('dissociateRole')->once()->with($subject, $role);
        $manager->dissociateWorkspaceRole($subject, $workspace, $role);
    }

    public function testResetWorkspaceRolesForUser()
    {
        $manager = $this->getManager(['dissociateRole', 'checkWorkspaceRoleEditionIsValid']);
        $managerRole = m::mock('Claroline\CoreBundle\Entity\Role');
        $roles = [$managerRole];
        $subject = new User();
        $workspace = new Workspace();

        $this->roleRepo->shouldReceive('findByUserAndWorkspace')->once()
            ->with($subject, $workspace)->andReturn($roles);
        $manager->shouldReceive('checkWorkspaceRoleEditionIsValid')->once()
            ->with([$subject], $workspace, $roles);
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('dissociateRole')->once()
            ->with($subject, $managerRole);

        $manager->resetWorkspaceRolesForSubject($subject, $workspace);
    }

    public function testResetWorkspaceRolesForGroup()
    {
        $manager = $this->getManager(['dissociateRole', 'checkWorkspaceRoleEditionIsValid']);
        $managerRole = m::mock('Claroline\CoreBundle\Entity\Role');
        $roles = [$managerRole];
        $subject = new Group();
        $workspace = new Workspace();

        $this->roleRepo->shouldReceive('findByGroupAndWorkspace')->once()
            ->with($subject, $workspace)->andReturn($roles);
        $manager->shouldReceive('checkWorkspaceRoleEditionIsValid')->once()
            ->with([$subject], $workspace, $roles);
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->shouldReceive('dissociateRole')->once()
            ->with($subject, $managerRole);

        $manager->resetWorkspaceRolesForSubject($subject, $workspace);
    }

    public function testResetWorkspaceRoleForSubjects()
    {
        $manager = $this->getManager(['resetWorkspaceRolesForSubject']);
        $subject = new Group();
        $workspace = new Workspace();
        $subjects = [$subject];
        $manager->shouldReceive('resetWorkspaceRolesForSubject')->once()
            ->with($subject, $workspace);

        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();

        $manager->resetWorkspaceRoleForSubjects($subjects, $workspace);
    }

    public function testAssociateRolesToSubjects()
    {
        $manager = $this->getManager(['associateRole']);
        $subject = new Group();
        $subjects = [$subject];
        $managerRole = new Role();
        $roles = [$managerRole];

        $this->om->shouldReceive('startFlushSuite')->once();
        $manager->shouldReceive('associateRole')->once()->with($subject, $managerRole);
        $this->om->shouldReceive('endFlushSuite')->once();
        $manager->associateRolesToSubjects($subjects, $roles);
    }

    /**
     * @expectedException \Claroline\CoreBundle\Manager\Exception\RoleReadOnlyException
     */
    public function testRemoveThrowsExceptionIfReadOnly()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $role->shouldReceive('isReadOnly')->once()->andReturn(true);
        $this->getManager()->remove($role);
    }

    public function testFindWorkspaceRoles()
    {
        $wsRole = new Role();
        $anonRole = new Role();
        $workspace = new Workspace();
        $wsRoles = [$wsRole];
        $res = [$wsRole, $anonRole];
        $this->roleRepo->shouldReceive('findByWorkspace')->once()->with($workspace)->andReturn($wsRoles);
        $this->roleRepo->shouldReceive('findBy')->once()->with(['name' => 'ROLE_ANONYMOUS'])
            ->andReturn([$anonRole]);

        $this->assertEquals($res, $this->getManager()->getWorkspaceRoles($workspace));
    }

    public function testGetStringRoleFromToken()
    {
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenRole = $this->mock('Symfony\Component\Security\Core\Role\RoleInterface');
        $tokenRole->shouldReceive('getRole')->once()->andReturn('ROLE');
        $token->shouldReceive('getRoles')->once()->andReturn([$tokenRole]);
        $res = ['ROLE'];
        $this->assertEquals($res, $this->getManager()->getStringRolesFromToken($token));
    }

    public function testGetRoleBaseName()
    {
        $roleName = 'ROLE_WS_MANAGER_GUID';

        $this->assertEquals('ROLE_WS_MANAGER', $this->getManager()->getRoleBaseName($roleName));
    }

    public function testGetRolesByWorkspace()
    {
        $workspace = new Workspace();
        $this->roleRepo->shouldReceive('findByWorkspace')->once()->with($workspace)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRolesByWorkspace($workspace));
    }

    public function testGetCollaboratorRole()
    {
        $workspace = new Workspace();
        $this->roleRepo->shouldReceive('findCollaboratorRole')->once()->with($workspace)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getCollaboratorRole($workspace));
    }

    public function testGetVisitorRole()
    {
        $workspace = new Workspace();
        $this->roleRepo->shouldReceive('findVisitorRole')->once()->with($workspace)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getVisitorRole($workspace));
    }

    public function testGetManagerRole()
    {
        $workspace = new Workspace();
        $this->roleRepo->shouldReceive('findManagerRole')->once()->with($workspace)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getManagerRole($workspace));
    }

    public function testGetPlatformRoles()
    {
        $user = new User();
        $this->roleRepo->shouldReceive('findPlatformRoles')->once()->with($user)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getPlatformRoles($user));
    }

    public function testGetWorkspaceRoleForUser()
    {
        $user = new User();
        $workspace = new Workspace();
        $this->roleRepo->shouldReceive('findWorkspaceRolesForUser')->once()
            ->with($user, $workspace)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getWorkspaceRolesForUser($user, $workspace));
    }

    public function testGetRolesByWorkspaceAndTool()
    {
        $workspace = new Workspace();
        $tool = new Tool();
        $this->roleRepo->shouldReceive('findByWorkspaceAndTool')->once()
            ->with($workspace, $tool)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRolesByWorkspaceAndTool($workspace, $tool));
    }

    public function testGetRolesBySearchOnWorkspaceAndTag()
    {
        $this->markTestIncomplete('Why is there no search on workspace ?');
        $search = 'search';
        $this->roleRepo->shouldReceive('findByWorkspaceCodeTag')->once()
            ->with($search)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRolesBySearchOnWorkspaceAndTag($search));
    }

    public function testGetRoleById()
    {
        $id = 1;
        $this->roleRepo->shouldReceive('find')->once()->with($id)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRoleById($id));
    }

    public function testGetRolesByIds()
    {
        $ids = [1];
        $this->om->shouldReceive('findByIds')->once()
            ->with('Claroline\CoreBundle\Entity\Role', $ids)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRolesByIds($ids));
    }

    public function testGetRoleByName()
    {
        $name = 'name';
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->roleRepo->shouldReceive('findOneByName')->once()->with($name)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getRoleByName($name));
    }

    public function testGetAllRoles()
    {
        $this->roleRepo->shouldReceive('findAll')->once()->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getAllRoles());
    }

    public function testGetStringRolesFromCurrentUser()
    {
        $manager = $this->getManager(['getStringRolesFromToken']);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
        $this->securityContext->shouldReceive('getToken')->once()->andReturn($token);
        $manager->shouldReceive('getStringRolesFromToken')->once()->with($token)->andReturn('return');

        $this->assertEquals('return', $manager->getStringRolesFromCurrentUser());
    }

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Role')
            ->once()->andReturn($this->roleRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:User')
            ->once()->andReturn($this->userRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Group')
            ->once()->andReturn($this->groupRepo);

        if (0 === count($mockedMethods)) {
            return new RoleManager($this->securityContext, $this->om, $this->dispatcher);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\RoleManager'.$stringMocked,
            [$this->securityContext, $this->om, $this->dispatcher]
        );
    }
}

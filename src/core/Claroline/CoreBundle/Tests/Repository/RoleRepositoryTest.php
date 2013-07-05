<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\AltRepositoryTestCase;

class RoleRepositoryTest extends AltRepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Role');

        self::createWorkspace('ws_1');
        self::createWorkspace('ws_2');
        self::createTool('tool_1');
        self::createRole('ROLE_WS_VISITOR_' . self::get('ws_1')->getId(), self::get('ws_1'));
        self::createRole('ROLE_WS_COLLABORATOR_' . self::get('ws_1')->getId(), self::get('ws_1'));
        self::createRole('ROLE_WS_MANAGER_' . self::get('ws_1')->getId(), self::get('ws_1'));
        self::createRole('ROLE_WS_CUSTOM_1', self::get('ws_1'));
        self::createRole('ROLE_WS_CUSTOM_2', self::get('ws_1'));
        self::createRole('ROLE_PLATFORM_CUSTOM');
        self::createWorkspaceTool(self::get('tool_1'), self::get('ws_1'), array(self::get('ROLE_WS_CUSTOM_1')), 1);
        self::createUser('john', array(self::get('ROLE_WS_CUSTOM_1'), self::get('ROLE_PLATFORM_CUSTOM')));
        self::createGroup('group_1', array(self::get('ROLE_WS_CUSTOM_2')));
    }

    public function testFindByWorkspace()
    {
        $roles = self::$repo->findByWorkspace(self::get('ws_1'));
        $this->assertEquals(5, count($roles));
    }

    public function testFindVisitorRole()
    {
        $role = self::$repo->findVisitorRole(self::get('ws_1'));
        $this->assertEquals('ROLE_WS_VISITOR_' . self::get('ws_1')->getId(), $role->getName());
    }

    public function testFindCollaboratorRole()
    {
        $role = self::$repo->findCollaboratorRole(self::get('ws_1'));
        $this->assertEquals('ROLE_WS_COLLABORATOR_' . self::get('ws_1')->getId(), $role->getName());
    }

    public function testFindManagerRole()
    {
        $role = self::$repo->findManagerRole(self::get('ws_1'));
        $this->assertEquals('ROLE_WS_MANAGER_' . self::get('ws_1')->getId(), $role->getName());
    }

    public function testFindPlatformRoles()
    {
        $roles = self::$repo->findPlatformRoles(self::get('john'));
        $this->assertEquals(1, count($roles));
        $this->assertEquals('ROLE_PLATFORM_CUSTOM', $roles[0]->getName());
    }

    public function testFindWorkspaceRole()
    {
        $role = self::$repo->findWorkspaceRole(self::get('john'), self::get('ws_2'));
        $this->assertNull($role);
        $role = self::$repo->findWorkspaceRole(self::get('john'), self::get('ws_1'));
        $this->assertEquals('ROLE_WS_CUSTOM_1', $role->getName());
        $role = self::$repo->findWorkspaceRole(self::get('group_1'), self::get('ws_1'));
        $this->assertEquals('ROLE_WS_CUSTOM_2', $role->getName());
    }

    public function testFindWorkspaceRoleForUser()
    {
        $role = self::$repo->findWorkspaceRole(self::get('john'), self::get('ws_1'));
        $this->assertEquals('ROLE_WS_CUSTOM_1', $role->getName());
    }

    public function testFindByWorkspaceAndTool()
    {
        $roles = self::$repo->findByWorkspaceAndTool(
            self::get('ws_1'),
            self::get('tool_1')
        );
        $this->assertEquals(1, count($roles));
    }

    public function testFindByWorkspaceCodeTag()
    {
        $this->markTestSkipped('This method must be tested');
    }
}
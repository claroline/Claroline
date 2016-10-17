<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class RoleRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Role');

        self::createWorkspace('ws_1');
        self::createWorkspace('ws_2');
        self::createTool('tool_1');
        self::createRole('ROLE_WS_VISITOR_'.self::get('ws_1')->getGuid(), self::get('ws_1'));
        self::createRole('ROLE_WS_COLLABORATOR_'.self::get('ws_1')->getGuid(), self::get('ws_1'));
        self::createRole('ROLE_WS_MANAGER_'.self::get('ws_1')->getGuid(), self::get('ws_1'));
        self::createRole('ROLE_WS_CUSTOM_1', self::get('ws_1'));
        self::createRole('ROLE_WS_CUSTOM_2', self::get('ws_1'));
        self::createRole('ROLE_PLATFORM_CUSTOM');
        self::createWorkspaceTool(self::get('tool_1'), self::get('ws_1'), [self::get('ROLE_WS_CUSTOM_1')], 1);
        self::createUser('john', [self::get('ROLE_WS_CUSTOM_1'), self::get('ROLE_PLATFORM_CUSTOM')]);
        self::createGroup('group_1', [], [self::get('ROLE_WS_CUSTOM_2')]);
    }

    public function testFindByWorkspace()
    {
        $roles = self::$repo->findByWorkspace(self::get('ws_1'));
        $this->assertEquals(5, count($roles));
    }

    public function testFindVisitorRole()
    {
        $role = self::$repo->findVisitorRole(self::get('ws_1'));
        $this->assertEquals('ROLE_WS_VISITOR_'.self::get('ws_1')->getGuid(), $role->getName());
    }

    public function testFindCollaboratorRole()
    {
        $role = self::$repo->findCollaboratorRole(self::get('ws_1'));
        $this->assertEquals('ROLE_WS_COLLABORATOR_'.self::get('ws_1')->getGuid(), $role->getName());
    }

    public function testFindManagerRole()
    {
        $role = self::$repo->findManagerRole(self::get('ws_1'));
        $this->assertEquals('ROLE_WS_MANAGER_'.self::get('ws_1')->getGuid(), $role->getName());
    }

    public function testFindPlatformRoles()
    {
        $roles = self::$repo->findPlatformRoles(self::get('john'));
        $this->assertEquals(2, count($roles));
        $this->assertEquals('ROLE_PLATFORM_CUSTOM', $roles[1]->getName());
    }

    public function testFindByWorkspaceCodeTag()
    {
        $this->markTestSkipped('This method must be tested');
    }
}

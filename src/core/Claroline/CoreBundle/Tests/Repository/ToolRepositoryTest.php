<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ToolRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Tool\Tool');

        self::createUser('john');
        self::createWorkspace('ws_1');
        self::createTool('tool_1');
        self::createTool('tool_2');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_1'));
        self::createWorkspaceTool(self::get('tool_1'), self::get('ws_1'), array(self::get('ROLE_1')), 1);
        self::createWorkspaceTool(self::get('tool_2'), self::get('ws_1'), array(self::get('ROLE_2')), 1);
        self::createDesktopTool(self::get('tool_2'), self::get('john'), 1);
    }

    public function testFindDisplayedByRolesAndWorkspace()
    {
        $result = self::$repo->findDisplayedByRolesAndWorkspace(array('ROLE_1'), self::get('ws_1'));
        $this->assertEquals(1, count($result));
    }

    public function testFindDesktopDisplayedToolsByUser()
    {
        $result = self::$repo->findDesktopDisplayedToolsByUser(self::get('john'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('tool_2', $result[0]->getName());
    }

    public function testFindDesktopUndisplayedToolsByUser()
    {
        $result = self::$repo->findDesktopUndisplayedToolsByUser(self::get('john'));
        $this->assertEquals(1, count($result));
        $this->assertEquals(self::get('tool_1'), $result[0]);
    }

    public function testFindUndisplayedToolsByWorkspace()
    {
        $result = self::$repo->findUndisplayedToolsByWorkspace(self::get('ws_1'));
        $this->assertEquals(0, count($result));
    }

    public function testFindDisplayedToolsByWorkspace()
    {
        $result = self::$repo->findDisplayedToolsByWorkspace(self::get('ws_1'));
        $this->assertEquals(2, count($result));
        $toolNames = array($result[0]->getName(), $result[1]->getName());
        $this->assertContains('tool_1', $toolNames);
        $this->assertContains('tool_2', $toolNames);
    }

    public function testCountDisplayedToolsByWorkspace()
    {
        $this->assertEquals(2, self::$repo->countDisplayedToolsByWorkspace(self::get('ws_1')));
    }
}

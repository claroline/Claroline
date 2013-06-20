<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ToolRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('Claroline\CoreBundle\Entity\Tool\Tool');
        self::loadPlatformRoleData();
        self::loadUserData(array('jane' => 'user'));
    }

    public function testFindDisplayedByRolesAndWorkspace()
    {
        $workspace = self::getWorkspace('jane');
        $role = self::$em->getRepository('Claroline\Corebundle\Entity\Role')
            ->findCollaboratorRole($workspace);
        $result = self::$repo->findDisplayedByRolesAndWorkspace(array($role->getName()), $workspace);
        $this->assertEquals(3, count($result));
        $this->assertEquals('home', $result[0]->getName());
        $this->assertEquals('resource_manager', $result[1]->getName());
        $this->assertEquals('calendar', $result[2]->getName());
    }

    public function testFindDesktopDisplayedToolsByUser()
    {
        $user = self::getUser('jane');
        $result = self::$repo->findDesktopDisplayedToolsByUser($user);
        $this->assertEquals(4, count($result));
        $this->assertEquals('home', $result[0]->getName());
        $this->assertEquals('resource_manager', $result[1]->getName());
        $this->assertEquals('parameters', $result[2]->getName());
        $this->assertEquals('logs', $result[3]->getName());
    }

    public function testFindDesktopUndisplayedToolsByUser()
    {
        $this->markTestSkipped('plugins should be removed from the test database');
        $user = self::getUser('jane');
        $result = self::$repo->findDesktopUndisplayedToolsByUser($user);
    }

    public function testFindUndisplayedToolsByWorkspace()
    {
        $this->markTestSkipped('plugins should be removed from the test database');
        $workspace = self::getWorkspace('jane');
        $result = self::$repo->findUndisplayedToolsByWorkspace($workspace);
    }

    public function testFindDisplayedToolsByWorkspace()
    {
        $workspace = self::getWorkspace('jane');
        $result = self::$repo->findDisplayedToolsByWorkspace($workspace);
        $this->assertEquals(7, count($result));
        $this->assertEquals('home', $result[0]->getName());
        $this->assertEquals('resource_manager', $result[1]->getName());
        $this->assertEquals('calendar', $result[2]->getName());
        $this->assertEquals('parameters', $result[3]->getName());
        $this->assertEquals('group_management', $result[4]->getName());
        $this->assertEquals('user_management', $result[5]->getName());
        $this->assertEquals('logs', $result[6]->getName());
    }

}
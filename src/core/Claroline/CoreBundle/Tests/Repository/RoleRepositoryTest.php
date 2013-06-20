<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class RoleRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\RoleRepository */
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('Claroline\CoreBundle\Entity\Role');
        self::loadPlatformRoleData();
        self::loadUserData(array('john' => 'user'));
        self::loadGroupData(array('group_a' => array()));
        $groupB = self::getGroup('group_a');
        $groupB->addRole(self::$repo->findCollaboratorRole(self::getWorkspace('john')));
        self::$em->persist($groupB);
        self::$em->flush();
    }

    public function testFindByWorkspace()
    {
        $roles = self::$repo->findByWorkspace(self::getWorkspace('john'));
        $this->assertEquals(3, count($roles));
    }

    public function testFindCollaboratorRole()
    {
        $role = self::$repo->findCollaboratorRole(self::getWorkspace('john'));
        $this->assertEquals('ROLE_WS_COLLABORATOR_'.self::getWorkspace('john')->getId(), $role->getName());
    }

    public function testFindVisitorRole()
    {
        $role = self::$repo->findVisitorRole(self::getWorkspace('john'));
        $this->assertEquals('ROLE_WS_VISITOR_'.self::getWorkspace('john')->getId(), $role->getName());
    }

    public function testFindManagerRole()
    {
        $role = self::$repo->findManagerRole(self::getWorkspace('john'));
        $this->assertEquals('ROLE_WS_MANAGER_'.self::getWorkspace('john')->getId(), $role->getName());
    }

    public function testFindPlatformRoles()
    {
        $roles = self::$repo->findPlatformRoles(self::getUser('john'));
        $this->assertEquals(1, count($roles));
        $this->assertEquals('ROLE_USER', $roles[0]->getName());
    }

    public function testFindWorkspaceRole()
    {
        $role = self::$repo->findWorkspaceRole(self::getUser('john'), self::getWorkspace('john'));
        $this->assertEquals('ROLE_WS_MANAGER_'.self::getWorkspace('john')->getId(), $role->getName());
        $role = self::$repo->findWorkspaceRole(self::getGroup('group_a'), self::getWorkspace('john'));
        $this->assertEquals('ROLE_WS_COLLABORATOR_'.self::getWorkspace('john')->getId(), $role->getName());
    }

    public function testFindWorkspaceRoleForUser()
    {
        $role = self::$repo->findWorkspaceRole(self::getUser('john'), self::getWorkspace('john'));
        $this->assertEquals('ROLE_WS_MANAGER_'.self::getWorkspace('john')->getId(), $role->getName());
    }

    public function testFindByWorkspaceAndTool()
    {
        $roles = self::$repo->findByWorkspaceAndTool(
            self::getWorkspace('john'),
            self::$em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('home')
        );
        $this->assertEquals(3, count($roles));
    }

    public function testFindByWorkspaceCodeTag()
    {
        $this->markTestSkipped('will be changed later');
    }
}
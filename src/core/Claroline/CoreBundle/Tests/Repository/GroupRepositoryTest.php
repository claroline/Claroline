<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class GroupRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\GroupRepository */
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::loadPlatformRoleData();
        self::loadUserData(array('user' => 'user', 'user_2' => 'user', 'user_3' => 'user', 'john' => 'ws_creator'));
        self::loadGroupData(array('group_a' => array('user', 'user_2')));
        self::loadGroupData(array('group_b' => array('user_2' => 'user_3')));
        self::loadWorkspaceData(array('ws_a' => 'john'));
        self::getGroup('group_a')
            ->addRole(self::$em->getRepository('ClarolineCoreBundle:Role')->findCollaboratorRole(self::getWorkspace('ws_a')));
        self::$em->persist(self::getGroup('group_a'));
        self::$em->flush();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Group');
    }

    public function testFindWorkspaceOutsiders()
    {
        $groups = self::$repo->findWorkspaceOutsiders(self::getWorkspace('ws_a'));
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_b', $groups[0]->getName());
    }

    public function testFindWorkspaceOutsidersByName()
    {
        $groups = self::$repo->findWorkspaceOutsidersByName(self::getWorkspace('ws_a'), 'GrOuP_B');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_b', $groups[0]->getName());
        $groups = self::$repo->findWorkspaceOutsidersByName(self::getWorkspace('ws_a'), 'GrOuP_A');
        $this->assertEquals(0, count($groups));
    }

    public function testFindByWorkspace()
    {
        $groups = self::$repo->findByWorkspace(self::getWorkspace('ws_a'));
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_a', $groups[0]->getName());
    }

    public function testFindByWorkspaceAndName()
    {
        $groups = self::$repo->findByWorkspaceAndName(self::getWorkspace('ws_a'), 'GrOuP_A');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_a', $groups[0]->getName());
        $groups = self::$repo->findByWorkspaceAndName(self::getWorkspace('ws_a'), 'GrOuP_B');
        $this->assertEquals(0, count($groups));
    }

    public function testFindAll()
    {
        $groups = self::$repo->findAll();
        $this->assertEquals(2, count($groups));
        $query = self::$repo->findAll(true);
        $this->assertEquals(get_class($query), 'Doctrine\ORM\Query');
    }

    public function testFindByName()
    {
        $groups = self::$repo->findByName('GrOuP_A');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_a', $groups[0]->getName());
        $groups = self::$repo->findByName('GrOuP_B');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_b', $groups[0]->getName());
    }
}



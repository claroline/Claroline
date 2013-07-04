<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\AltRepositoryTestCase;

class GroupRepositoryTest extends AltRepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Group');
        self::createWorkspace('ws_1');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createGroup('group_1', array(self::get('ROLE_1')));
        self::createGroup('group_2', array(self::get('ROLE_1')));
        self::createGroup('group_3');
        self::createGroup('group_4');
//        self::loadPlatformRoleData();
//        self::loadUserData(array('user' => 'user', 'user_2' => 'user', 'user_3' => 'user', 'john' => 'ws_creator'));
//        self::loadGroupData(array('group_a' => array('user', 'user_2')));
//        self::loadGroupData(array('group_b' => array('user_2' => 'user_3')));
//        self::loadWorkspaceData(array('ws_a' => 'john'));
//        self::getGroup('group_a')
//            ->addRole(self::$em->getRepository('ClarolineCoreBundle:Role')->findCollaboratorRole(self::getWorkspace('ws_a')));
//        self::$em->persist(self::getGroup('group_a'));
//        self::$em->flush();
//        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Group');
    }

    public function testFindWorkspaceOutsiders()
    {
        $groups = self::$repo->findWorkspaceOutsiders(self::get('ws_1'));
        $this->assertEquals(2, count($groups));
        $this->assertEquals('group_3', $groups[0]->getName());
        $this->assertEquals('group_4', $groups[1]->getName());
    }

    public function testFindWorkspaceOutsidersByName()
    {
        $groups = self::$repo->findWorkspaceOutsidersByName(self::get('ws_1'), 'Roup_4');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_4', $groups[0]->getName());
    }

    public function testFindByWorkspace()
    {
        $groups = self::$repo->findByWorkspace(self::get('ws_1'));
        $this->assertEquals(2, count($groups));
        $this->assertEquals('group_1', $groups[0]->getName());
        $this->assertEquals('group_2', $groups[1]->getName());
    }


//    /** @var \Claroline\CoreBundle\Repository\GroupRepository */
//    private static $repo;
//
//    public static function setUpBeforeClass()
//    {
//        parent::setUpBeforeClass();
//        self::loadPlatformRoleData();
//        self::loadUserData(array('user' => 'user', 'user_2' => 'user', 'user_3' => 'user', 'john' => 'ws_creator'));
//        self::loadGroupData(array('group_a' => array('user', 'user_2')));
//        self::loadGroupData(array('group_b' => array('user_2' => 'user_3')));
//        self::loadWorkspaceData(array('ws_a' => 'john'));
//        self::getGroup('group_a')
//            ->addRole(self::$em->getRepository('ClarolineCoreBundle:Role')->findCollaboratorRole(self::getWorkspace('ws_a')));
//        self::$em->persist(self::getGroup('group_a'));
//        self::$em->flush();
//        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Group');
//    }
//
//    public function testFindWorkspaceOutsiders()
//    {
//        $groups = self::$repo->findWorkspaceOutsiders(self::getWorkspace('ws_a'));
//        $this->assertEquals(1, count($groups));
//        $this->assertEquals('group_b', $groups[0]->getName());
//    }
//
//    public function testFindWorkspaceOutsidersByName()
//    {
//        $groups = self::$repo->findWorkspaceOutsidersByName(self::getWorkspace('ws_a'), 'GrOuP_B');
//        $this->assertEquals(1, count($groups));
//        $this->assertEquals('group_b', $groups[0]->getName());
//        $groups = self::$repo->findWorkspaceOutsidersByName(self::getWorkspace('ws_a'), 'GrOuP_A');
//        $this->assertEquals(0, count($groups));
//    }
//
//    public function testFindByWorkspace()
//    {
//        $groups = self::$repo->findByWorkspace(self::getWorkspace('ws_a'));
//        $this->assertEquals(1, count($groups));
//        $this->assertEquals('group_a', $groups[0]->getName());
//    }
//
//    public function testFindByWorkspaceAndName()
//    {
//        $groups = self::$repo->findByWorkspaceAndName(self::getWorkspace('ws_a'), 'GrOuP_A');
//        $this->assertEquals(1, count($groups));
//        $this->assertEquals('group_a', $groups[0]->getName());
//        $groups = self::$repo->findByWorkspaceAndName(self::getWorkspace('ws_a'), 'GrOuP_B');
//        $this->assertEquals(0, count($groups));
//    }
//
//    public function testFindAll()
//    {
//        $groups = self::$repo->findAll();
//        $this->assertEquals(2, count($groups));
//        $query = self::$repo->findAll(true);
//        $this->assertEquals(get_class($query), 'Doctrine\ORM\Query');
//    }
//
//    public function testFindByName()
//    {
//        $groups = self::$repo->findByName('GrOuP_A');
//        $this->assertEquals(1, count($groups));
//        $this->assertEquals('group_a', $groups[0]->getName());
//        $groups = self::$repo->findByName('GrOuP_B');
//        $this->assertEquals(1, count($groups));
//        $this->assertEquals('group_b', $groups[0]->getName());
//    }
}



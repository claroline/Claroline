<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class GroupRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Group');
        self::createWorkspace('ws_1');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createGroup('group_1', array(), array(self::get('ROLE_1')));
        self::createGroup('group_2', array(), array(self::get('ROLE_1')));
        self::createGroup('group_3');
        self::createGroup('group_4');
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

    public function testFindByWorkspaceAndName()
    {
        $groups = self::$repo->findByWorkspaceAndName(self::get('ws_1'), 'oup');
        $this->assertEquals(2, count($groups));
        $this->assertEquals('group_1', $groups[0]->getName());
        $this->assertEquals('group_2', $groups[1]->getName());
        $groups = self::$repo->findByWorkspaceAndName(self::get('ws_1'), 'foobar');
        $this->assertEquals(0, count($groups));
    }

    public function testFindAll()
    {
        $groups = self::$repo->findAll();
        $this->assertEquals(4, count($groups));
        $query = self::$repo->findAll(false);
        $this->assertInstanceof('Doctrine\ORM\Query', $query);
    }

    public function testFindByName()
    {
        $groups = self::$repo->findByName('RouP');
        $this->assertEquals(4, count($groups));
        $groups = self::$repo->findByName('_1');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('group_1', $groups[0]->getName());
    }
}
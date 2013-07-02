<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\AltRepositoryTestCase;

class AltAbstractResourceRepositoryTest extends AltRepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Resource\AbstractResource');

        self::createWorkspace('ws_1');
        self::createWorkspace('ws_2');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_2'));
        self::createUser('john', array(self::get('ROLE_1'), self::get('ROLE_2')));
        self::createUser('jane', array(self::get('ROLE_2')));
        self::createResourceType('t_dir');
        self::createResourceType('t_file');

        /*
         * Structure :
         *
         * ws_1
         *     dir_1
         *         dir_2
         *         dir_3
         *             dir_4
         *                 file_1
         *
         * ws_2
         *     dir_5
         */
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createDirectory('dir_2', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::createDirectory('dir_3', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::createDirectory('dir_4', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_3'));
        self::createDirectory('dir_5', self::get('t_dir'), self::get('jane'), self::get('ws_2'));
        self::createFile('file_1', self::get('t_file'), self::get('john'), self::get('dir_4'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_2'), array('open'));
    }

    public function testFindWorkspaceRoot()
    {
        $root = self::$repo->findWorkspaceRoot(self::get('ws_1'));
        $this->assertEquals(self::get('dir_1'), $root);
    }

    public function testFindDescendants()
    {
        $this->assertEquals(0, count(self::$repo->findDescendants(self::get('dir_2'))));
        $this->assertEquals(4, count(self::$repo->findDescendants(self::get('dir_1'))));
        $this->assertEquals(5, count(self::$repo->findDescendants(self::get('dir_1'), true)));
        $this->assertEquals(2, count(self::$repo->findDescendants(self::get('dir_3'), true, 't_dir')));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFindChildrenThrowsAnExceptionIfNoRolesAreGiven()
    {
        $children = self::$repo->findChildren(self::get('dir_1'), array());
    }

    public function testFindChildrenReturnsEverythingIfTheUserIsAdmin()
    {
        $children = self::$repo->findChildren(self::get('dir_1'), array('ROLE_ADMIN'));
        $this->assertEquals(2, count($children));
        $this->assertEquals(self::get('dir_2')->getId(), $children[0]['id']);
        $this->assertEquals(self::get('dir_3')->getId(), $children[1]['id']);
    }

    public function testFindChildrenReturnsOpenableResources()
    {
        $children = self::$repo->findChildren(self::get('dir_1'), array('ROLE_1'));
        $this->assertEquals(1, count($children));
        $this->assertEquals(self::get('dir_2')->getId(), $children[0]['id']);
    }

    public function testFindWorkspaceRootsByUser()
    {
        $johnRoots = self::$repo->findWorkspaceRootsByUser(self::get('john'));
        $this->assertEquals(2, count($johnRoots));
        $this->assertEquals(self::get('dir_1')->getId(), $johnRoots[0]['id']);
        $this->assertEquals(self::get('dir_5')->getId(), $johnRoots[1]['id']);
        $janeRoots = self::$repo->findWorkspaceRootsByUser(self::get('jane'));
        $this->assertEquals(1, count($janeRoots));
        $this->assertEquals(self::get('dir_5')->getId(), $janeRoots[0]['id']);
    }

//    public function testFindWorkspaceRootsByRoles()
//    {
//        $janeManager = 'ROLE_WS_MANAGER_'.self::getWorkspace('jane')->getId();
//        $johnManager = 'ROLE_WS_MANAGER_'.self::getWorkspace('john')->getId();
//        $roots = self::$repo->findWorkspaceRootsByRoles(array($janeManager));
//        $this->assertEquals(1, count($roots));
//        $roots = self::$repo->findWorkspaceRootsByRoles(array($janeManager, $johnManager));
//        $this->assertEquals(2, count($roots));
//    }
}
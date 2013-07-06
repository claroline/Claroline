<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class AltAbstractResourceRepositoryTest extends RepositoryTestCase
{
    private static $repo;
    private static $time;

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
        self::createResourceType('t_link', false);

        /*
         * Structure :
         *
         * ws_1
         *     dir_1
         *         dir_2
         *             l_dir_3
         *         dir_3
         *             dir_4
         *                 file_1
         *
         * ws_2
         *     dir_5
         *         l_dir_2
         */
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createDirectory('dir_2', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));

        sleep(1); // dates involved
        self::$time = new \DateTime();
        sleep(1);

        self::createDirectory('dir_3', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::createDirectory('dir_4', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_3'));
        self::createDirectory('dir_5', self::get('t_dir'), self::get('jane'), self::get('ws_2'));
        self::createFile('file_1', self::get('t_file'), self::get('john'), self::get('dir_4'));
        self::createShortcut('l_dir_2', self::get('t_link'), self::get('dir_2'), self::get('john'), self::get('dir_5'));
        self::createShortcut('l_dir_3', self::get('t_link'), self::get('dir_3'), self::get('john'), self::get('dir_2'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_1'), array('open'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_2'), array('open'));
        self::createResourceRights(self::get('ROLE_2'), self::get('dir_5'), array('open'));
    }

    public function testFindWorkspaceRoot()
    {
        $root = self::$repo->findWorkspaceRoot(self::get('ws_1'));
        $this->assertEquals(self::get('dir_1'), $root);
    }

    public function testFindDescendants()
    {
        $this->assertEquals(1, count(self::$repo->findDescendants(self::get('dir_2'))));
        $this->assertEquals(5, count(self::$repo->findDescendants(self::get('dir_1'))));
        $this->assertEquals(6, count(self::$repo->findDescendants(self::get('dir_1'), true)));
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
        $this->assertEquals('dir_2', $children[0]['name']);
        $this->assertEquals('dir_3', $children[1]['name']);
    }

    public function testFindChildrenReturnsOpenableResources()
    {
        $children = self::$repo->findChildren(self::get('dir_1'), array('ROLE_1'));
        $this->assertEquals(1, count($children));
        $this->assertEquals('dir_2', $children[0]['name']);
    }

    public function testFindWorkspaceRootsByUser()
    {
        $johnRoots = self::$repo->findWorkspaceRootsByUser(self::get('john'));
        $this->assertEquals(2, count($johnRoots));
        $this->assertEquals('dir_1', $johnRoots[0]['name']);
        $this->assertEquals('dir_5', $johnRoots[1]['name']);
        $janeRoots = self::$repo->findWorkspaceRootsByUser(self::get('jane'));
        $this->assertEquals(1, count($janeRoots));
        $this->assertEquals('dir_5', $janeRoots[0]['name']);
    }

    public function testFindWorkspaceRootsByRoles()
    {
        $roots = self::$repo->findWorkspaceRootsByRoles(array('ROLE_2'));
        $this->assertEquals(1, count($roots));
        $this->assertEquals('dir_5', $roots[0]['name']);

        $this->markTestIncomplete('Queries with more than one role make mysql randomly slow');

        $roots = self::$repo->findWorkspaceRootsByRoles(array('ROLE_1', 'ROLE_2'));
        $this->assertEquals(2, count($roots));
        $this->assertEquals('dir_1', $roots[0]['name']);
        $this->assertEquals('dir_5', $roots[1]['name']);
    }

    public function testFindAncestors()
    {
        $ancestors = self::$repo->findAncestors(self::get('dir_4'));
        $this->assertEquals(3, count($ancestors));
        $this->assertEquals('dir_1', $ancestors[0]['name']);
        $this->assertEquals('dir_3', $ancestors[1]['name']);
        $this->assertEquals('dir_4', $ancestors[2]['name']);
    }

    /**
     * @expectedException Claroline\CoreBundle\Repository\Exception\UnknownFilterException
     */
    public function testFindByCriteriaThrowsAnExceptionOnUnknownFilter()
    {
        self::$repo->findByCriteria(array('foo' => 'bar'));
    }

    public function testFindByCriteria()
    {
        $resources = self::$repo->findByCriteria(array());
        $this->assertEquals(8, count($resources));

        $resources = self::$repo->findByCriteria(array('types' => array('t_file')));
        $this->assertEquals(1, count($resources));

        $resources = self::$repo->findByCriteria(array('roots' => array(self::get('dir_1')->getPath())));
        $this->assertEquals(6, count($resources));

        $resources = self::$repo->findByCriteria(array('dateFrom' => self::$time->format('Y-m-d H:i:s')));
        $this->assertEquals(6, count($resources));

        $resources = self::$repo->findByCriteria(array('dateTo' => self::$time->format('Y-m-d H:i:s')));
        $this->assertEquals(2, count($resources));

        $resources = self::$repo->findByCriteria(array('name' => '_1'));
        $this->assertEquals(2, count($resources));

        $resources = self::$repo->findByCriteria(array('isExportable' => true));
        $this->assertEquals(6, count($resources));
    }

    public function testFindByCriteriaWithRoles()
    {
        $resources = self::$repo->findByCriteria(array(), array('ROLE_2'));
        $this->assertEquals(1, count($resources));

        $this->markTestIncomplete('Queries with more than one role make mysql randomly slow');

        $resources = self::$repo->findByCriteria(array(), self::get('john')->getRoles());
        $this->assertEquals(3, count($resources));
    }

    public function testFindDirectoryShortcutTargets()
    {
        $shortcuts = self::$repo->findRecursiveDirectoryShortcuts(
            array('roots' => array(self::get('dir_5')->getPath()))
        );
        $this->assertEquals(2, count($shortcuts));
        $this->assertEquals('l_dir_3', $shortcuts[0]['name']);
        $this->assertEquals('l_dir_2', $shortcuts[1]['name']);
        $this->assertEquals(self::get('dir_3')->getPath(), $shortcuts[0]['target_path']);
        $this->assertEquals(self::get('dir_2')->getPath(), $shortcuts[1]['target_path']);
    }

    public function testFindMimeTypesWithMostResources()
    {
        $mimeTypes = self::$repo->findMimeTypesWithMostResources(10);
        $this->assertEquals(3, count($mimeTypes));
        $this->assertEquals('directory/mime', $mimeTypes[0]['mimeType']);
        $this->assertEquals('file/mime', $mimeTypes[2]['mimeType']);
        $this->assertEquals(5, $mimeTypes[0]['total']);
        $this->assertEquals(1, $mimeTypes[2]['total']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindWorkspaceInfoByIdsThrowsAnExceptionIfIdArrayIsEmpty()
    {
        self::$repo->findWorkspaceInfoByIds(array());
    }

    public function testFindWorkspaceInfoByIds()
    {
        $infos = self::$repo->findWorkspaceInfoByIds(
            array(self::get('dir_4')->getId(), self::get('dir_5')->getId())
        );
        $this->assertEquals(2, count($infos));
        $this->assertEquals('ws_1', $infos[0]['name']);
        $this->assertEquals('ws_2', $infos[1]['name']);
        $this->assertEquals('ws_1Code', $infos[0]['code']);
        $this->assertEquals('ws_2Code', $infos[1]['code']);
    }
}
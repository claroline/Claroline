<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class AbstractResourceRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\AbstractResourceRepository */
    private static $repo;
    private static $timeOne;
    private static $timeTwo;

    /*
     * directory structure:
     *
     * john/dir1/dir2/linkToDir3
     * john/dir1/dir2/linkToDir2
     * john/dir1/file1.txt
     * john/dir3/dir4/linkToDir5
     * john/dir3/dir4/linkToDir1
     * john/dir5
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        self::loadPlatformRoleData();
        self::loadUserData(array('john' => 'user'));
        self::$timeOne = new \DateTime();
        self::loadDirectoryData('john', array('john/dir1/dir2'));
        sleep(1);
        self::loadDirectoryData('john', array('john/dir3/dir4'));
        self::$timeTwo = new \DateTime;
        self::loadDirectoryData('john', array('john/dir5'));
        self::loadFileData('john', 'dir1', array('file1.txt'));

        self::loadShortcutData(
            self::getDirectory('dir3'),
            'dir2',
            'john'
        );

        self::loadShortcutData(
            self::getDirectory('dir2'),
            'dir2',
            'john'
        );

        self::loadShortcutData(
            self::getDirectory('dir5'),
            'dir4',
            'john'
        );

        self::loadShortcutData(
            self::getDirectory('dir1'),
            'dir4',
            'john'
        );

        $rights = self::$em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->findOneBy(array('role' => self::getRole('anonymous'), 'resource' => self::getDirectory('dir1')));
        $rights->setCanOpen(true);
        //warning: flush !
        self::$em->persist($rights);

        self::loadUserData(array('jane' => 'user'));
        $john = self::getUser('john');
        $role = self::$em->getRepository('Claroline\Corebundle\Entity\Role')
            ->findCollaboratorRole(self::getWorkspace('jane'));
        $john->addRole($role);
        self::$em->persist($john);

        self::$em->flush();
    }

    public function testFindWorkspaceRoot()
    {
        $root = self::$repo->findWorkspaceRoot(self::getWorkspace('john'));
        $this->assertEquals(self::getDirectory('john'), $root);
    }

    public function testFindDescendants()
    {
        $this->assertEquals(
            0,
            count(self::$repo->findDescendants(self::getDirectory('dir5')))
        );
        $this->assertEquals(
            4,
            count(self::$repo->findDescendants(self::getDirectory('dir1')))
        );
        $this->assertEquals(
            5,
            count(self::$repo->findDescendants(self::getDirectory('dir1'), true))
        );
        $this->assertEquals(
            4,
            count(self::$repo->findDescendants(self::getDirectory('dir1'), true, 'directory'))
        );

        $entityDirs = self::$repo->findDescendants(self::getDirectory('dir1'), false);
        $this->assertInstanceOf(
            'Claroline\CoreBundle\Entity\Resource\AbstractResource',
            $entityDirs[0]
        );
    }

    public function testFindChildren()
    {
        $children = self::$repo->findChildren(self::getDirectory('john'), array('ROLE_ADMIN'));
        $this->assertEquals(3, count($children));
        $this->assertEquals(self::getDirectory('dir1')->getId(), $children[0]['id']);
        $this->assertEquals(self::getDirectory('dir3')->getId(), $children[1]['id']);
        $children = self::$repo->findChildren(self::getDirectory('john'), array('ROLE_ANONYMOUS'));
        $this->assertEquals(1, count($children));
    }

    public function testFindChildrenThrowsAnExceptionIfNoRolesAreGiven()
    {
        $this->setExpectedException('RuntimeException');
        $children = self::$repo->findChildren(self::getDirectory('john'), array());
        $this->assertEquals(0, count($children));
    }

    public function testFindWorkspaceRootsByUser()
    {
        $jane = self::getUser('jane');
        $roots = self::$repo->findWorkspaceRootsByUser($jane);
        $this->assertEquals(1, count($roots));
        $this->assertEquals(self::getDirectory('jane')->getId(), $roots[0]['id']);
        $john = self::getUser('john');
        $roots = self::$repo->findWorkspaceRootsByUser($john);
        $this->assertEquals(2, count($roots));
        $this->assertEquals(self::getDirectory('jane')->getId(), $roots[0]['id']);
        $this->assertEquals(self::getDirectory('john')->getId(), $roots[1]['id']);
    }

    public function testFindWorkspaceRootsByRoles()
    {
        $janeManager = 'ROLE_WS_MANAGER_'.self::getWorkspace('jane')->getId();
        $johnManager = 'ROLE_WS_MANAGER_'.self::getWorkspace('john')->getId();
        $roots = self::$repo->findWorkspaceRootsByRoles(array($janeManager));
        $this->assertEquals(1, count($roots));
        $roots = self::$repo->findWorkspaceRootsByRoles(array($janeManager, $johnManager));
        $this->assertEquals(2, count($roots));
    }

    public function testFindResourcesByIds()
    {
        $ids = array(self::getDirectory('dir1')->getId(), self::getDirectory('dir2')->getId());
        $resources = self::$repo->findResourcesByIds($ids);
        $this->assertEquals(2, count($resources));
    }

    public function testFindAncestors()
    {
        $ancestors = self::$repo->findAncestors(self::getDirectory('dir2'));
        $this->assertEquals(3, count($ancestors));
        $this->assertEquals(self::getDirectory('john')->getId(), $ancestors[0]['id']);
        $this->assertEquals(self::getDirectory('dir1')->getId(), $ancestors[1]['id']);
        $this->assertEquals(self::getDirectory('dir2')->getId(), $ancestors[2]['id']);
    }

    public function testFindByCriteria()
    {
        $resources = self::$repo->findByCriteria(array());
        $this->assertEquals(12, count($resources));

        $resources = self::$repo->findByCriteria(array('types' => array('directory')));
        $this->assertEquals(11, count($resources));

        $resources = self::$repo->findByCriteria(array('roots' => array(self::getDirectory('john')->getPath())));
        $this->assertEquals(11, count($resources));

        $resources = self::$repo->findByCriteria(array('dateFrom' => self::$timeTwo->format('Y-m-d H:i:s')));
        $this->assertEquals(9, count($resources));

        $resources = self::$repo->findByCriteria(array('dateTo' => self::$timeOne->format('Y-m-d H:i:s')));
        $this->assertEquals(3, count($resources));

        $resources = self::$repo->findByCriteria(array('name' => 'j'));
        $this->assertEquals(2, count($resources));

        $resources = self::$repo->findByCriteria(array('isExportable' => true));
        $this->assertEquals(12, count($resources));

        $resources = self::$repo->findByCriteria(array(), self::getUser('jane')->getRoles());
        $this->assertEquals(1, count($resources));

        $resources = self::$repo->findByCriteria(array(), array('ROLE_ANONYMOUS'));
        $this->assertEquals(1, count($resources));
        $this->assertEquals(self::getDirectory('dir1')->getId(), $resources[0]['id']);
    }

    public function testFindByCriteriaThrowsAnExceptionOnUnknownFilter()
    {
        $this->setExpectedException('Claroline\CoreBundle\Repository\Exception\UnknownFilterException');
        self::$repo->findByCriteria(array('foo' => 'bar'));
    }

    public function testFindDirectoryShortcutTargets()
    {
        $shortcuts = self::$repo->findRecursiveDirectoryShortcuts(array(), null, array());
        $this->assertEquals(4, count($shortcuts));
    }

    public function testCount()
    {
       $this->assertEquals(12, self::$repo->count());
    }
}
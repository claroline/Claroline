<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class WorkspaceTagHierarchyRepositoryTest extends RepositoryTestCase
{
    private static $tagHierarchyRepo;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$tagHierarchyRepo = self::$em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy');
        self::loadPlatformRoleData();
        self::loadUserData(array('user' => 'user', 'admin' => 'admin'));
        self::loadWorkspaceData(array('wsa' => 'user', 'wsb' => 'user', 'wsc' => 'user', 'wsd' => 'admin'));
        self::loadWorkspaceTagData(
            array(
                array(
                    'name' => 'tag_1'
                ),
                array(
                    'name' => 'tag_2'
                ),
                array(
                    'name' => 'tag_3'
                ),
                array(
                    'name' => 'tag_4'
                ),
                array(
                    'name' => 'tag_5'
                ),
                array(
                    'name' => 'user_tag_1',
                    'user' => 'user'
                ),
                array(
                    'name' => 'user_tag_2',
                    'user' => 'user'
                ),
                array(
                    'name' => 'user_tag_3',
                    'user' => 'user'
                ),
                array(
                    'name' => 'user_tag_4',
                    'user' => 'user'
                ),
                array(
                    'name' => 'admin_tag',
                    'user' => 'admin'
                )
            )
        );

        /**
         *  Creates tag hierarchy
         *
         *  admin :
         *   ________________________
         *  | parent | child | level |
         *  |------------------------|
         *  | tag_1  | tag_1 |   0   |
         *  | tag_1  | tag_2 |   1   |
         *  | tag_1  | tag_4 |   1   |
         *  | tag_1  | tag_2 |   2   |
         *  | tag_1  | tag_3 |   2   |
         *  | tag_1  | tag_3 |   3   |
         *  | tag_2  | tag_2 |   0   |
         *  | tag_2  | tag_3 |   1   |
         *  | tag_3  | tag_3 |   0   |
         *  | tag_4  | tag_4 |   0   |
         *  | tag_4  | tag_2 |   1   |
         *  | tag_4  | tag_3 |   2   |
         *  | tag_5  | tag_5 |   0   |
         *  |________|_______|_______|
         *
         *
         *  user :
         *   _________________________________
         *  |   parent   |   child    | level |
         *  |---------------------------------|
         *  | user_tag_1 | user_tag_1 |   0   |
         *  | user_tag_1 | user_tag_2 |   1   |
         *  | user_tag_1 | user_tag_4 |   1   |
         *  | user_tag_1 | user_tag_2 |   2   |
         *  | user_tag_1 | user_tag_3 |   2   |
         *  | user_tag_1 | user_tag_3 |   3   |
         *  | user_tag_2 | user_tag_2 |   0   |
         *  | user_tag_2 | user_tag_3 |   1   |
         *  | user_tag_3 | user_tag_3 |   0   |
         *  | user_tag_4 | user_tag_4 |   0   |
         *  | user_tag_4 | user_tag_2 |   1   |
         *  | user_tag_4 | user_tag_3 |   2   |
         *  |____________|____________|_______|
         *
         *
         *  admin :
         *   _______________________________
         *  |  parent   |   child   | level |
         *  |-------------------------------|
         *  | admin_tag | admin_tag |   0   |
         *  |___________|___________|_______|
         *
         */
        self::loadWorkspaceTagHierarchyData(
            array(
                array(
                    'parent' => 'tag_1',
                    'child' => 'tag_1',
                    'level' => 0
                ),
                array(
                    'parent' => 'tag_1',
                    'child' => 'tag_2',
                    'level' => 1
                ),
                array(
                    'parent' => 'tag_1',
                    'child' => 'tag_4',
                    'level' => 1
                ),
                array(
                    'parent' => 'tag_1',
                    'child' => 'tag_2',
                    'level' => 2
                ),
                array(
                    'parent' => 'tag_1',
                    'child' => 'tag_3',
                    'level' => 2
                ),
                array(
                    'parent' => 'tag_1',
                    'child' => 'tag_3',
                    'level' => 3
                ),
                array(
                    'parent' => 'tag_2',
                    'child' => 'tag_2',
                    'level' => 0
                ),
                array(
                    'parent' => 'tag_2',
                    'child' => 'tag_3',
                    'level' => 1
                ),
                array(
                    'parent' => 'tag_3',
                    'child' => 'tag_3',
                    'level' => 0
                ),
                array(
                    'parent' => 'tag_4',
                    'child' => 'tag_4',
                    'level' => 0
                ),
                array(
                    'parent' => 'tag_4',
                    'child' => 'tag_2',
                    'level' => 1
                ),
                array(
                    'parent' => 'tag_4',
                    'child' => 'tag_3',
                    'level' => 2
                ),
                array(
                    'parent' => 'tag_5',
                    'child' => 'tag_5',
                    'level' => 0
                ),
                array(
                    'parent' => 'user_tag_1',
                    'child' => 'user_tag_1',
                    'level' => 0
                ),
                array(
                    'parent' => 'user_tag_1',
                    'child' => 'user_tag_2',
                    'level' => 1
                ),
                array(
                    'parent' => 'user_tag_1',
                    'child' => 'user_tag_4',
                    'level' => 1
                ),
                array(
                    'parent' => 'user_tag_1',
                    'child' => 'user_tag_2',
                    'level' => 2
                ),
                array(
                    'parent' => 'user_tag_1',
                    'child' => 'user_tag_3',
                    'level' => 2
                ),
                array(
                    'parent' => 'user_tag_1',
                    'child' => 'user_tag_3',
                    'level' => 3
                ),
                array(
                    'parent' => 'user_tag_2',
                    'child' => 'user_tag_2',
                    'level' => 0
                ),
                array(
                    'parent' => 'user_tag_2',
                    'child' => 'user_tag_3',
                    'level' => 1
                ),
                array(
                    'parent' => 'user_tag_3',
                    'child' => 'user_tag_3',
                    'level' => 0
                ),
                array(
                    'parent' => 'user_tag_4',
                    'child' => 'user_tag_4',
                    'level' => 0
                ),
                array(
                    'parent' => 'user_tag_4',
                    'child' => 'user_tag_2',
                    'level' => 1
                ),
                array(
                    'parent' => 'user_tag_4',
                    'child' => 'user_tag_3',
                    'level' => 2
                ),
                array(
                    'parent' => 'admin_tag',
                    'child' => 'admin_tag',
                    'level' => 0
                )
            )
        );
    }

    public function testFindAdminHierarchiesByParents()
    {
        $parents = array(self::getTag('tag_4')->getId(), self::getTag('tag_5')->getId());
        $tagHierarchies = self::$tagHierarchyRepo->findAdminHierarchiesByParents($parents);
        $this->assertEquals(4, count($tagHierarchies));

        $parents1 = array(self::getTag('tag_1')->getId());
        $tagHierarchies1 = self::$tagHierarchyRepo->findAdminHierarchiesByParents($parents1);
        $this->assertEquals(6, count($tagHierarchies1));
        $this->assertEquals('tag_1', $tagHierarchies1[0]->getParent()->getName());
        $this->assertEquals('tag_1', $tagHierarchies1[1]->getParent()->getName());
        $this->assertEquals('tag_1', $tagHierarchies1[2]->getParent()->getName());
        $this->assertEquals('tag_1', $tagHierarchies1[3]->getParent()->getName());
        $this->assertEquals('tag_1', $tagHierarchies1[4]->getParent()->getName());
        $this->assertEquals('tag_1', $tagHierarchies1[5]->getParent()->getName());
    }

    public function testFindHierarchiesByParents()
    {
        $user = self::getUser('user');
        $parents = array(self::getTag('user_tag_1')->getId(), self::getTag('user_tag_4')->getId());
        $tagHierarchies = self::$tagHierarchyRepo->findHierarchiesByParents($user, $parents);
        $this->assertEquals(9, count($tagHierarchies));
    }

    public function testFindAdminHierarchiesByParentsAndChildren()
    {
        $parents = array(self::getTag('tag_1')->getId(), self::getTag('tag_4')->getId());
        $children = array(self::getTag('tag_2')->getId(), self::getTag('tag_3')->getId());
        $tagHierarchies = self::$tagHierarchyRepo->findAdminHierarchiesByParentsAndChildren(
            $parents,
            $children
        );
        $this->assertEquals(6, count($tagHierarchies));
    }

    public function testFindHierarchiesByParentsAndChildren()
    {
        $user = self::getUser('user');
        $parents = array(self::getTag('user_tag_1')->getId(), self::getTag('user_tag_4')->getId());
        $children = array(self::getTag('user_tag_2')->getId(), self::getTag('user_tag_3')->getId());
        $tagHierarchies = self::$tagHierarchyRepo->findHierarchiesByParentsAndChildren(
            $user,
            $parents,
            $children
        );
        $this->assertEquals(6, count($tagHierarchies));

        $admin = self::getUser('admin');
        $adminParents = array(self::getTag('admin_tag')->getId());
        $adminChildren = array(self::getTag('admin_tag')->getId());
        $adminTagHierarchies = self::$tagHierarchyRepo->findHierarchiesByParentsAndChildren(
            $admin,
            $adminParents,
            $adminChildren
        );
        $this->assertEquals(0, count($adminTagHierarchies));
    }

    public function testFindAllByUser()
    {
        $user = self::getUser('user');
        $tagHierarchies = self::$tagHierarchyRepo->findAllByUser($user);
        $this->assertEquals(12, count($tagHierarchies));

        $admin = self::getUser('admin');
        $adminTagHierarchies = self::$tagHierarchyRepo->findAllByUser($admin);
        $this->assertEquals(1, count($adminTagHierarchies));
        $this->assertEquals('admin_tag', $adminTagHierarchies[0]->getParent()->getName());
        $this->assertEquals('admin_tag', $adminTagHierarchies[0]->getTag()->getName());
        $this->assertEquals(0, $adminTagHierarchies[0]->getLevel());
    }

    public function testFindAllAdmin()
    {
        $tagHierarchies = self::$tagHierarchyRepo->findAllAdmin();
        $this->assertEquals(13, count($tagHierarchies));
    }
}
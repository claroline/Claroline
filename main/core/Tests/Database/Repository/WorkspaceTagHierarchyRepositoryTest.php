<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class WorkspaceTagHierarchyRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy');
        self::createWorkspace('wsa');
        self::createWorkspace('wsb');
        self::createWorkspace('wsc');
        self::createWorkspace('wsd');
        self::createRole('ROLE_wsa', self::get('wsa'));
        self::createRole('ROLE_wsb', self::get('wsb'));
        self::createRole('ROLE_wsc', self::get('wsc'));
        self::createRole('ROLE_wsd', self::get('wsd'));
        self::createUser('user', array(self::get('ROLE_wsa'), self::get('ROLE_wsb'), self::get('ROLE_wsc')));
        self::createUser('admin', array(self::get('ROLE_wsd')));
        self::createWorkspaceTag('tag_1');
        self::createWorkspaceTag('tag_2');
        self::createWorkspaceTag('tag_3');
        self::createWorkspaceTag('tag_4');
        self::createWorkspaceTag('tag_5');
        self::createWorkspaceTag('user_tag_1', self::get('user'));
        self::createWorkspaceTag('user_tag_2', self::get('user'));
        self::createWorkspaceTag('user_tag_3', self::get('user'));
        self::createWorkspaceTag('user_tag_4', self::get('user'));
        self::createWorkspaceTag('admin_tag', self::get('admin'));

        /*
         *  Creates admin tag hierarchy
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
         */

        self::createWorkspaceTagHierarchy(self::get('tag_1'), self::get('tag_1'), 0);
        self::createWorkspaceTagHierarchy(self::get('tag_1'), self::get('tag_2'), 1);
        self::createWorkspaceTagHierarchy(self::get('tag_1'), self::get('tag_4'), 1);
        self::createWorkspaceTagHierarchy(self::get('tag_1'), self::get('tag_2'), 2);
        self::createWorkspaceTagHierarchy(self::get('tag_1'), self::get('tag_3'), 2);
        self::createWorkspaceTagHierarchy(self::get('tag_1'), self::get('tag_3'), 3);
        self::createWorkspaceTagHierarchy(self::get('tag_2'), self::get('tag_2'), 0);
        self::createWorkspaceTagHierarchy(self::get('tag_2'), self::get('tag_3'), 1);
        self::createWorkspaceTagHierarchy(self::get('tag_3'), self::get('tag_3'), 0);
        self::createWorkspaceTagHierarchy(self::get('tag_4'), self::get('tag_4'), 0);
        self::createWorkspaceTagHierarchy(self::get('tag_4'), self::get('tag_2'), 1);
        self::createWorkspaceTagHierarchy(self::get('tag_4'), self::get('tag_3'), 2);
        self::createWorkspaceTagHierarchy(self::get('tag_5'), self::get('tag_5'), 0);

        /*
         *  Creates tag hierarchy for user 'user'
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
         */

        self::createWorkspaceTagHierarchy(self::get('user_tag_1'), self::get('user_tag_1'), 0, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_1'), self::get('user_tag_2'), 1, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_1'), self::get('user_tag_4'), 1, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_1'), self::get('user_tag_2'), 2, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_1'), self::get('user_tag_3'), 2, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_1'), self::get('user_tag_3'), 3, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_2'), self::get('user_tag_2'), 0, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_2'), self::get('user_tag_3'), 1, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_3'), self::get('user_tag_3'), 0, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_4'), self::get('user_tag_4'), 0, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_4'), self::get('user_tag_2'), 1, self::get('user'));
        self::createWorkspaceTagHierarchy(self::get('user_tag_4'), self::get('user_tag_3'), 2, self::get('user'));

        /*
         *  Creates tag hierarchy for user 'admin'
         *   _______________________________
         *  |  parent   |   child   | level |
         *  |-------------------------------|
         *  | admin_tag | admin_tag |   0   |
         *  |___________|___________|_______|
         */

        self::createWorkspaceTagHierarchy(self::get('admin_tag'), self::get('admin_tag'), 0, self::get('admin'));
    }

    public function testFindAdminHierarchiesByParents()
    {
        $parents = array(self::get('tag_4')->getId(), self::get('tag_5')->getId());
        $tagHierarchies = self::$repo->findAdminHierarchiesByParents($parents);
        $this->assertEquals(4, count($tagHierarchies));

        $tag = self::get('tag_1');
        $parentsA = array($tag->getId());
        $tagHierarchiesA = self::$repo->findAdminHierarchiesByParents($parentsA);
        $this->assertEquals(6, count($tagHierarchiesA));
        $this->assertEquals($tag, $tagHierarchiesA[0]->getParent());
        $this->assertEquals($tag, $tagHierarchiesA[1]->getParent());
        $this->assertEquals($tag, $tagHierarchiesA[2]->getParent());
        $this->assertEquals($tag, $tagHierarchiesA[3]->getParent());
        $this->assertEquals($tag, $tagHierarchiesA[4]->getParent());
        $this->assertEquals($tag, $tagHierarchiesA[5]->getParent());
    }

    public function testFindHierarchiesByParents()
    {
        $user = self::get('user');
        $parents = array(self::get('user_tag_1')->getId(), self::get('user_tag_4')->getId());
        $tagHierarchies = self::$repo->findHierarchiesByParents($user, $parents);
        $this->assertEquals(9, count($tagHierarchies));
    }

    public function testFindAdminHierarchiesByParentsAndChildren()
    {
        $parents = array(self::get('tag_1')->getId(), self::get('tag_4')->getId());
        $children = array(self::get('tag_2')->getId(), self::get('tag_3')->getId());
        $tagHierarchies = self::$repo->findAdminHierarchiesByParentsAndChildren(
            $parents,
            $children
        );
        $this->assertEquals(6, count($tagHierarchies));
    }

    public function testFindHierarchiesByParentsAndChildren()
    {
        $user = self::get('user');
        $parents = array(self::get('user_tag_1')->getId(), self::get('user_tag_4')->getId());
        $children = array(self::get('user_tag_2')->getId(), self::get('user_tag_3')->getId());
        $tagHierarchies = self::$repo->findHierarchiesByParentsAndChildren(
            $user,
            $parents,
            $children
        );
        $this->assertEquals(6, count($tagHierarchies));

        $admin = self::get('admin');
        $adminTag = self::get('admin_tag');
        $adminParents = array($adminTag->getId());
        $adminChildren = array($adminTag->getId());
        $adminTagHierarchies = self::$repo->findHierarchiesByParentsAndChildren(
            $admin,
            $adminParents,
            $adminChildren
        );
        $this->assertEquals(0, count($adminTagHierarchies));
    }

    public function testFindAllByUser()
    {
        $user = self::get('user');
        $tagHierarchies = self::$repo->findAllByUser($user);
        $this->assertEquals(12, count($tagHierarchies));

        $admin = self::get('admin');
        $adminTag = self::get('admin_tag');
        $adminTagHierarchies = self::$repo->findAllByUser($admin);
        $this->assertEquals(1, count($adminTagHierarchies));
        $this->assertEquals($adminTag, $adminTagHierarchies[0]->getParent());
        $this->assertEquals($adminTag, $adminTagHierarchies[0]->getTag());
        $this->assertEquals(0, $adminTagHierarchies[0]->getLevel());
    }

    public function testFindAllAdmin()
    {
        $tagHierarchies = self::$repo->findAllAdmin();
        $this->assertEquals(13, count($tagHierarchies));
    }
}

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

class WorkspaceTagRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag');
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
         *  Associates workspaces and tags :
         *   ___________________________________________________
         *  | Workspaces |                 Tags                 |
         *  |---------------------------------------------------|
         *  | wsa        | tag_1, tag_5, user_tag_1, user_tag_4 |
         *  | wsb        | tag_3, tag_5, user_tag_1, user_tag_2 |
         *  | wsc        | tag_4, tag_5                         |
         *  | wsd        | tag_1, tag_5, admin_tag              |
         *  |____________|______________________________________|
         */

        self::createWorkspaceTagRelation(self::get('tag_1'), self::get('wsa'));
        self::createWorkspaceTagRelation(self::get('tag_5'), self::get('wsa'));
        self::createWorkspaceTagRelation(self::get('user_tag_1'), self::get('wsa'));
        self::createWorkspaceTagRelation(self::get('user_tag_4'), self::get('wsa'));
        self::createWorkspaceTagRelation(self::get('tag_3'), self::get('wsb'));
        self::createWorkspaceTagRelation(self::get('tag_5'), self::get('wsb'));
        self::createWorkspaceTagRelation(self::get('user_tag_1'), self::get('wsb'));
        self::createWorkspaceTagRelation(self::get('user_tag_2'), self::get('wsb'));
        self::createWorkspaceTagRelation(self::get('tag_4'), self::get('wsc'));
        self::createWorkspaceTagRelation(self::get('tag_5'), self::get('wsc'));
        self::createWorkspaceTagRelation(self::get('tag_1'), self::get('wsd'));
        self::createWorkspaceTagRelation(self::get('tag_5'), self::get('wsd'));
        self::createWorkspaceTagRelation(self::get('admin_tag'), self::get('wsd'));

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

    public function testFindNonEmptyTagsByUser()
    {
        $tags = self::$repo->findNonEmptyTagsByUser(self::get('user'));
        $this->assertEquals(3, count($tags));
        $this->assertEquals(self::get('user_tag_1'), $tags[0]);
        $this->assertEquals(self::get('user_tag_2'), $tags[1]);
        $this->assertEquals(self::get('user_tag_4'), $tags[2]);
    }

    public function testFindNonEmptyAdminTags()
    {
        $tags = self::$repo->findNonEmptyAdminTags();
        $this->assertEquals(4, count($tags));
        $this->assertEquals(self::get('tag_1'), $tags[0]);
        $this->assertEquals(self::get('tag_3'), $tags[1]);
        $this->assertEquals(self::get('tag_4'), $tags[2]);
        $this->assertEquals(self::get('tag_5'), $tags[3]);
    }

    public function testFindNonEmptyAdminTagsByWorspaces()
    {
        $workspaces = array(self::get('wsa'), self::get('wsb'));
        $tags = self::$repo->findNonEmptyAdminTagsByWorspaces($workspaces);
        $this->assertEquals(3, count($tags));
        $this->assertEquals(self::get('tag_1'), $tags[0]);
        $this->assertEquals(self::get('tag_3'), $tags[1]);
        $this->assertEquals(self::get('tag_5'), $tags[2]);
    }

    public function testFindPossibleAdminChildren()
    {
        $tag = self::get('tag_4');
        $tags = self::$repo->findPossibleAdminChildren($tag);
        $this->assertEquals(2, count($tags));
        $this->assertEquals(self::get('tag_3'), $tags[0]);
        $this->assertEquals(self::get('tag_5'), $tags[1]);
    }

    public function testFindPossibleChildren()
    {
        $userTags = self::$repo->findPossibleChildren(self::get('user'), self::get('user_tag_4'));
        $adminTags = self::$repo->findPossibleChildren(self::get('admin'), self::get('admin_tag'));
        $this->assertEquals(1, count($userTags));
        $this->assertEquals(self::get('user_tag_3'), $userTags[0]);
        $this->assertEquals(0, count($adminTags));
    }

    public function testFindAdminChildren()
    {
        $tags = self::$repo->findAdminChildren(self::get('tag_1'));
        $this->assertEquals(2, count($tags));
        $this->assertEquals(self::get('tag_2'), $tags[0]);
        $this->assertEquals(self::get('tag_4'), $tags[1]);
    }

    public function testFindChildren()
    {
        $userTags = self::$repo->findChildren(self::get('user'), self::get('user_tag_4'));
        $adminTags = self::$repo->findChildren(self::get('admin'), self::get('admin_tag'));
        $this->assertEquals(1, count($userTags));
        $this->assertEquals(self::get('user_tag_2'), $userTags[0]);
        $this->assertEquals(0, count($adminTags));
    }

    public function testFindAdminRootTags()
    {
        $tags = self::$repo->findAdminRootTags();
        $this->assertEquals(2, count($tags));
        $this->assertEquals(self::get('tag_1'), $tags[0]);
        $this->assertEquals(self::get('tag_5'), $tags[1]);
    }

    public function testFindRootTags()
    {
        $userTags = self::$repo->findRootTags(self::get('user'));
        $adminTags = self::$repo->findRootTags(self::get('admin'));
        $this->assertEquals(1, count($userTags));
        $this->assertEquals(self::get('user_tag_1'), $userTags[0]);
        $this->assertEquals(1, count($adminTags));
        $this->assertEquals(self::get('admin_tag'), $adminTags[0]);
    }

    public function testFindAdminChildrenFromTags()
    {
        $tagsArray = array(self::get('tag_2')->getId(), self::get('tag_4')->getId());
        $tags = self::$repo->findAdminChildrenFromTags($tagsArray);
        $this->assertEquals(3, count($tags));
        $this->assertEquals(self::get('tag_2'), $tags[0]);
        $this->assertEquals(self::get('tag_3'), $tags[1]);
        $this->assertEquals(self::get('tag_4'), $tags[2]);
    }

    public function testFindChildrenFromTags()
    {
        $userTagsArray = array(self::get('user_tag_2')->getId(), self::get('user_tag_4')->getId());
        $adminTagsArray = array(self::get('admin_tag')->getId());
        $userTags = self::$repo->findChildrenFromTags(self::get('user'), $userTagsArray);
        $admninTags = self::$repo->findChildrenFromTags(self::get('admin'), $adminTagsArray);
        $this->assertEquals(3, count($userTags));
        $this->assertEquals(self::get('user_tag_2'), $userTags[0]);
        $this->assertEquals(self::get('user_tag_3'), $userTags[1]);
        $this->assertEquals(self::get('user_tag_4'), $userTags[2]);
        $this->assertEquals(1, count($admninTags));
        $this->assertEquals(self::get('admin_tag'), $admninTags[0]);
    }

    public function testFindAdminParentsFromTag()
    {
        $tags = self::$repo->findAdminParentsFromTag(self::get('tag_2'));
        $this->assertEquals(3, count($tags));
        $this->assertEquals(self::get('tag_1'), $tags[0]);
        $this->assertEquals(self::get('tag_2'), $tags[1]);
        $this->assertEquals(self::get('tag_4'), $tags[2]);
    }

    public function testFindParentsFromTag()
    {
        $userTags = self::$repo->findParentsFromTag(self::get('user'), self::get('user_tag_2'));
        $admninTags = self::$repo->findParentsFromTag(self::get('admin'), self::get('admin_tag'));
        $this->assertEquals(3, count($userTags));
        $this->assertEquals(self::get('user_tag_1'), $userTags[0]);
        $this->assertEquals(self::get('user_tag_2'), $userTags[1]);
        $this->assertEquals(self::get('user_tag_4'), $userTags[2]);
        $this->assertEquals(1, count($admninTags));
        $this->assertEquals(self::get('admin_tag'), $admninTags[0]);
    }

    public function testFindWorkspaceTagFromIds()
    {
        $tags = self::$repo->findWorkspaceTagFromIds(
            array(
                self::get('user_tag_1')->getId(),
                self::get('user_tag_2')->getId(),
            )
        );
        $this->assertEquals(2, count($tags));
        $this->assertEquals(self::get('user_tag_1'), $tags[0]);
        $this->assertEquals(self::get('user_tag_2'), $tags[1]);
    }
}

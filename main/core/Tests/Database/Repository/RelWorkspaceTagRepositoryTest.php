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

class RelWorkspaceTagRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag');
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

    public function testFindByWorkspaceAndUser()
    {
        $workspaceA = self::get('wsa');
        $workspaceD = self::get('wsd');
        $userTagRelations = self::$repo->findByWorkspaceAndUser($workspaceA, self::get('user'));
        $adminTagRelations = self::$repo->findByWorkspaceAndUser($workspaceD, self::get('admin'));
        $this->assertEquals(2, count($userTagRelations));
        $this->assertEquals(1, count($adminTagRelations));
        $this->assertEquals($workspaceA, $userTagRelations[0]->getWorkspace());
        $this->assertEquals($workspaceA, $userTagRelations[1]->getWorkspace());
        $this->assertEquals(self::get('admin_tag'), $adminTagRelations[0]->getTag());
        $this->assertEquals($workspaceD, $adminTagRelations[0]->getWorkspace());
    }

    public function testFindAdminByWorkspace()
    {
        $workspace = self::get('wsa');
        $tagRelations = self::$repo->findAdminByWorkspace($workspace);
        $this->assertEquals(2, count($tagRelations));
        $this->assertEquals($workspace, $tagRelations[0]->getWorkspace());
        $this->assertEquals($workspace, $tagRelations[1]->getWorkspace());
    }

    public function testFindOneByWorkspaceAndTagAndUser()
    {
        $user = self::get('user');
        $admin = self::get('admin');
        $userWorkspace = self::get('wsa');
        $adminWorkspace = self::get('wsd');
        $userTag = self::get('user_tag_2');
        $adminTag = self::get('admin_tag');
        $userTagRelation = self::$repo->findOneByWorkspaceAndTagAndUser($userWorkspace, $userTag, $user);
        $adminTagRelation = self::$repo->findOneByWorkspaceAndTagAndUser($adminWorkspace, $adminTag, $admin);
        $this->assertNull($userTagRelation);
        $this->assertEquals($adminTag, $adminTagRelation->getTag());
        $this->assertEquals($adminWorkspace, $adminTagRelation->getWorkspace());
    }

    public function testFindOneAdminByWorkspaceAndTag()
    {
        $workspace = self::get('wsa');
        $tag = self::get('tag_1');
        $tagRelation = self::$repo->findOneAdminByWorkspaceAndTag($workspace, $tag);
        $this->assertEquals($tag, $tagRelation->getTag());
        $this->assertEquals($workspace, $tagRelation->getWorkspace());
    }

    public function testFindAllByWorkspaceAndUser()
    {
        $user = self::get('user');
        $workspace = self::get('wsa');
        $tagRelations = self::$repo->findAllByWorkspaceAndUser($workspace, $user);
        $this->assertEquals(2, count($tagRelations));
        $this->assertEquals($workspace, $tagRelations[0]->getWorkspace());
        $this->assertEquals($workspace, $tagRelations[1]->getWorkspace());
        $this->assertEquals(self::get('user_tag_1'), $tagRelations[0]->getTag());
        $this->assertEquals(self::get('user_tag_4'), $tagRelations[1]->getTag());
    }

    public function testFindByUser()
    {
        $tagRelations = self::$repo->findByUser(self::get('user'));
        $this->assertEquals(4, count($tagRelations));
    }

    public function testFindByAdmin()
    {
        $tagRelations = self::$repo->findByAdmin();
        $this->assertEquals(8, count($tagRelations));
    }

    public function testFindAdminRelationsByTag()
    {
        $tagRelations = self::$repo->findAdminRelationsByTag(self::get('tag_1'));
        $this->assertEquals(2, count($tagRelations));
        $this->assertEquals(self::get('wsa'), $tagRelations[0]->getWorkspace());
        $this->assertEquals(self::get('wsd'), $tagRelations[1]->getWorkspace());
    }

    public function testFindByAdminAndWorkspaces()
    {
        $workspaces = array(self::get('wsa'), self::get('wsd'));
        $tagRelations = self::$repo->findByAdminAndWorkspaces($workspaces);
        $this->assertEquals(4, count($tagRelations));
    }
}

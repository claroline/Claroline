<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class RelWorkspaceTagRepositoryTest extends RepositoryTestCase
{
    private static $tagRelationRepo;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$tagRelationRepo = self::$em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag');
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
        self::loadRelWorkspaceTagData(
            array(
                array(
                    'workspace' => 'wsa',
                    'tags' => array('tag_1', 'tag_5', 'user_tag_1', 'user_tag_4')
                ),
                array(
                    'workspace' => 'wsb',
                    'tags' => array('tag_3', 'tag_5', 'user_tag_1', 'user_tag_2')
                ),
                array(
                    'workspace' => 'wsc',
                    'tags' => array('tag_4', 'tag_5')
                ),
                array(
                    'workspace' => 'wsd',
                    'tags' => array('tag_1', 'tag_5', 'admin_tag')
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

    public function testFindByWorkspaceAndUser()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userWorkspace = self::getWorkspace('wsa');
        $adminWorkspace = self::getWorkspace('wsd');
        $userTagRelations = self::$tagRelationRepo->findByWorkspaceAndUser($userWorkspace, $user);
        $adminTagRelations = self::$tagRelationRepo->findByWorkspaceAndUser($adminWorkspace, $admin);
        $this->assertEquals(2, count($userTagRelations));
        $this->assertEquals(1, count($adminTagRelations));
        $this->assertEquals('admin_tag', $adminTagRelations[0]->getTag()->getName());
        $this->assertEquals('wsd', $adminTagRelations[0]->getWorkspace()->getName());
    }

    public function testFindAdminByWorkspace()
    {
        $workspace = self::getWorkspace('wsa');
        $tagRelations = self::$tagRelationRepo->findAdminByWorkspace($workspace);
        $this->assertEquals(2, count($tagRelations));
        $this->assertEquals('wsa', $tagRelations[0]->getWorkspace()->getName());
        $this->assertEquals('wsa', $tagRelations[1]->getWorkspace()->getName());
    }

    public function testFindOneByWorkspaceAndTagAndUser()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userWorkspace = self::getWorkspace('wsa');
        $adminWorkspace = self::getWorkspace('wsd');
        $userTag = self::getTag('user_tag_2');
        $adminTag = self::getTag('admin_tag');
        $userTagRelation = self::$tagRelationRepo->findOneByWorkspaceAndTagAndUser($userWorkspace, $userTag, $user);
        $adminTagRelation = self::$tagRelationRepo->findOneByWorkspaceAndTagAndUser($adminWorkspace, $adminTag, $admin);
        $this->assertNull($userTagRelation);
        $this->assertEquals('admin_tag', $adminTagRelation->getTag()->getName());
        $this->assertEquals('wsd', $adminTagRelation->getWorkspace()->getName());
    }

    public function testFindOneAdminByWorkspaceAndTag()
    {
        $workspace = self::getWorkspace('wsa');
        $tag = self::getTag('tag_1');
        $tagRelation = self::$tagRelationRepo->findOneAdminByWorkspaceAndTag($workspace, $tag);
        $this->assertEquals('tag_1', $tagRelation->getTag()->getName());
        $this->assertEquals('wsa', $tagRelation->getWorkspace()->getName());
    }

    public function testFindAllByWorkspaceAndUser()
    {
        $user = self::getUser('user');
        $workspace = self::getWorkspace('wsa');
        $tagRelations = self::$tagRelationRepo->findAllByWorkspaceAndUser($workspace, $user);
        $this->assertEquals(4, count($tagRelations));
        $this->assertEquals('wsa', $tagRelations[0]->getWorkspace()->getName());
        $this->assertEquals('wsa', $tagRelations[1]->getWorkspace()->getName());
        $this->assertEquals('wsa', $tagRelations[2]->getWorkspace()->getName());
        $this->assertEquals('wsa', $tagRelations[3]->getWorkspace()->getName());
    }

    public function testFindByUser()
    {
        $user = self::getUser('user');
        $tagRelations = self::$tagRelationRepo->findByUser($user);
        $this->assertEquals(4, count($tagRelations));
    }

    public function testFindByAdmin()
    {
        $tagRelations = self::$tagRelationRepo->findByAdmin();
        $this->assertEquals(8, count($tagRelations));
    }

    public function testFindByAdminAndWorkspaces()
    {
        $workspaces = array(self::getWorkspace('wsa'), self::getWorkspace('wsd'));
        $tagRelations = self::$tagRelationRepo->findByAdminAndWorkspaces($workspaces);
        $this->assertEquals(4, count($tagRelations));
    }
}
<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class WorkspaceTagRepositoryTest extends RepositoryTestCase
{
    private static $tagRepo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$tagRepo = self::$em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag');
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

    public function testFindNonEmptyTagsByUser()
    {
        $tags = self::$tagRepo->findNonEmptyTagsByUser(self::getUser('user'));
        $this->assertEquals(3, count($tags));
        $this->assertEquals('user_tag_1' , $tags[0]->getName());
        $this->assertEquals('user_tag_2' , $tags[1]->getName());
        $this->assertEquals('user_tag_4' , $tags[2]->getName());
    }

    public function testFindNonEmptyAdminTags()
    {
        $tags = self::$tagRepo->findNonEmptyAdminTags();
        $this->assertEquals(4, count($tags));
        $this->assertEquals('tag_1' , $tags[0]->getName());
        $this->assertEquals('tag_3' , $tags[1]->getName());
        $this->assertEquals('tag_4' , $tags[2]->getName());
        $this->assertEquals('tag_5' , $tags[3]->getName());
    }

    public function testFindNonEmptyAdminTagsByWorspaces()
    {
        $workspaces = array(self::getWorkspace('wsa'), self::getWorkspace('wsb'));
        $tags = self::$tagRepo->findNonEmptyAdminTagsByWorspaces($workspaces);
        $this->assertEquals(3, count($tags));
        $this->assertEquals('tag_1' , $tags[0]->getName());
        $this->assertEquals('tag_3' , $tags[1]->getName());
        $this->assertEquals('tag_5' , $tags[2]->getName());
    }

    public function testFindPossibleAdminChildren()
    {
        $tag = self::getTag('tag_4');
        $tags = self::$tagRepo->findPossibleAdminChildren($tag);
        $this->assertEquals(2, count($tags));
        $this->assertEquals('tag_3' , $tags[0]->getName());
        $this->assertEquals('tag_5' , $tags[1]->getName());
    }

    public function testFindPossibleChildren()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userTag = self::getTag('user_tag_4');
        $adminTag = self::getTag('admin_tag');
        $userTags = self::$tagRepo->findPossibleChildren($user, $userTag);
        $adminTags = self::$tagRepo->findPossibleChildren($admin, $adminTag);
        $this->assertEquals(1, count($userTags));
        $this->assertEquals('user_tag_3' , $userTags[0]->getName());
        $this->assertEquals(0, count($adminTags));
    }

    public function testFindAdminChildren()
    {
        $tag = self::getTag('tag_1');
        $tags = self::$tagRepo->findAdminChildren($tag);
        $this->assertEquals(2, count($tags));
        $this->assertEquals('tag_2' , $tags[0]->getName());
        $this->assertEquals('tag_4' , $tags[1]->getName());
    }

    public function testFindChildren()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userTag = self::getTag('user_tag_4');
        $adminTag = self::getTag('admin_tag');
        $userTags = self::$tagRepo->findChildren($user, $userTag);
        $adminTags = self::$tagRepo->findChildren($admin, $adminTag);
        $this->assertEquals(1, count($userTags));
        $this->assertEquals('user_tag_2' , $userTags[0]->getName());
        $this->assertEquals(0, count($adminTags));
    }

    public function testFindAdminRootTags()
    {
        $tags = self::$tagRepo->findAdminRootTags();
        $this->assertEquals(2, count($tags));
        $this->assertEquals('tag_1' , $tags[0]->getName());
        $this->assertEquals('tag_5' , $tags[1]->getName());
    }

    public function testFindRootTags()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userTags = self::$tagRepo->findRootTags($user);
        $adminTags = self::$tagRepo->findRootTags($admin);
        $this->assertEquals(1, count($userTags));
        $this->assertEquals('user_tag_1' , $userTags[0]->getName());
        $this->assertEquals(1, count($adminTags));
        $this->assertEquals('admin_tag' , $adminTags[0]->getName());
    }

    public function testFindAdminChildrenFromTags()
    {
        $tagsArray = array(self::getTag('tag_2')->getId(), self::getTag('tag_4')->getId());
        $tags = self::$tagRepo->findAdminChildrenFromTags($tagsArray);
        $this->assertEquals(3, count($tags));
        $this->assertEquals('tag_2' , $tags[0]->getName());
        $this->assertEquals('tag_3' , $tags[1]->getName());
        $this->assertEquals('tag_4' , $tags[2]->getName());
    }

    public function testFindChildrenFromTags()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userTagsArray = array(self::getTag('user_tag_2')->getId(), self::getTag('user_tag_4')->getId());
        $adminTagsArray = array(self::getTag('admin_tag')->getId());
        $userTags = self::$tagRepo->findChildrenFromTags($user, $userTagsArray);
        $admninTags = self::$tagRepo->findChildrenFromTags($admin, $adminTagsArray);
        $this->assertEquals(3, count($userTags));
        $this->assertEquals('user_tag_2' , $userTags[0]->getName());
        $this->assertEquals('user_tag_3' , $userTags[1]->getName());
        $this->assertEquals('user_tag_4' , $userTags[2]->getName());
        $this->assertEquals(1, count($admninTags));
        $this->assertEquals('admin_tag' , $admninTags[0]->getName());
    }

    public function testFindAdminParentsFromTag()
    {
        $tag = self::getTag('tag_2');
        $tags = self::$tagRepo->findAdminParentsFromTag($tag);
        $this->assertEquals(3, count($tags));
        $this->assertEquals('tag_1' , $tags[0]->getName());
        $this->assertEquals('tag_2' , $tags[1]->getName());
        $this->assertEquals('tag_4' , $tags[2]->getName());
    }

    public function testFindParentsFromTag()
    {
        $user = self::getUser('user');
        $admin = self::getUser('admin');
        $userTag = self::getTag('user_tag_2');
        $adminTag = self::getTag('admin_tag');
        $userTags = self::$tagRepo->findParentsFromTag($user, $userTag);
        $admninTags = self::$tagRepo->findParentsFromTag($admin, $adminTag);
        $this->assertEquals(3, count($userTags));
        $this->assertEquals('user_tag_1' , $userTags[0]->getName());
        $this->assertEquals('user_tag_2' , $userTags[1]->getName());
        $this->assertEquals('user_tag_4' , $userTags[2]->getName());
        $this->assertEquals(1, count($admninTags));
        $this->assertEquals('admin_tag' , $admninTags[0]->getName());
    }

    public function testFindWorkspaceTagFromIds()
    {
        $tags = self::$tagRepo->findWorkspaceTagFromIds(
            array(
                self::getTag('user_tag_1')->getId(),
                self::getTag('user_tag_2')->getId()
            )
        );
        $this->assertEquals(2, count($tags));
        $this->assertEquals('user_tag_1', $tags[0]->getName());
        $this->assertEquals('user_tag_2', $tags[1]->getName());
    }
}
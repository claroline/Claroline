<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class UserRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\UserRepository */
    private static $repo;
    private static $collaboratorA;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::loadPlatformRoleData();
        self::loadUserData(array('user' => 'user', 'user_2' => 'user', 'user_3' => 'user', 'nogroup' => 'user', 'john' => 'ws_creator'));
        self::loadGroupData(array('group_a' => array('user', 'user_2')));
        self::loadGroupData(array('group_b' => array('user_2' => 'user_3')));
        self::loadWorkspaceData(array('ws_a' => 'john'));
        self::$collaboratorA = self::$em->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole(self::getWorkspace('ws_a'));
        self::getGroup('group_a')->addRole(self::$collaboratorA);
        self::$em->persist(self::getGroup('group_a'));
        self::getUser('nogroup')->addRole(self::$collaboratorA);
        self::$em->flush();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:User');
    }

    public function testLoadUserByUsername()
    {
        $user = self::$repo->loadUserByUsername('john');
        $this->assertEquals('john', $user->getUsername());
    }

    public function testLoadUserByUsernameThrowsExceptionOnUnknownUsername()
    {
        $this->setExpectedException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException');
        self::$repo->loadUserByUsername('mr42');
    }

    public function testRefreshUser()
    {
        $user = self::getUser('john');
        self::$repo->refreshUser($user);
        $this->assertEquals('john', $user->getUsername());
    }

    public function testFindByWorkspaceAndRole()
    {
        $users = self::$repo->findByWorkspaceAndRole(self::getWorkspace('ws_a'), self::$collaboratorA);
        //also include groups
        $this->assertEquals(3, count($users));
    }

    public function testFindWorkspaceOutsidersByName()
    {
        $users = self::$repo->findWorkspaceOutsidersByName(self::getWorkspace('ws_a'), 'user');
        $this->assertEquals(3, count($users));
        $users = self::$repo->findWorkspaceOutsidersByName(self::getWorkspace('ws_a'), 'doe');
        $this->assertEquals(3, count($users));
    }

    public function testFindWorkspaceOutsiders()
    {
        $users = self::$repo->findWorkspaceOutsiders(self::getWorkspace('ws_a'));
        $this->assertEquals(3, count($users));
    }

    public function testFindAll()
    {
        $users = self::$repo->findAll();
        $this->assertEquals(5, count($users));
        $query = self::$repo->findAll(true);
        $this->assertEquals('Doctrine\ORM\Query', get_class($query));
    }

    public function testFindByName()
    {
        $users = self::$repo->findByName('UsEr');
        $this->assertEquals(3, count($users));
    }

    public function testFindByGroup()
    {
        $users = self::$repo->findByGroup(self::getGroup('group_a'));
        $this->assertEquals(2, count($users));
    }

    public function testFindByNameAndGroup()
    {
        $users = self::$repo->findByNameAndGroup('user', self::getGroup('group_a'));
        $this->assertEquals(2, count($users));
        $users = self::$repo->findByNameAndGroup('2', self::getGroup('group_a'));
        $this->assertEquals(1, count($users));
        $users = self::$repo->findByNameAndGroup('doe', self::getGroup('group_a'));
        $this->assertEquals(2, count($users));
    }

    public function testFindByWorkspace()
    {
        $users = self::$repo->findByWorkspace(self::getWorkspace('ws_a'));
        $this->assertEquals(2, count($users));
        $this->assertEquals('nogroup', $users[0]->getUsername());
    }

    public function testFindByWorkspaceAndName()
    {
        $users = self::$repo->findByWorkspaceAndName(self::getWorkspace('ws_a'), 'nogroup');
        $this->assertEquals(1, count($users));
        $users = self::$repo->findByWorkspaceAndName(self::getWorkspace('ws_a'), 'doe');
        $this->assertEquals(2, count($users));
    }

    public function testFindGroupOutsiders()
    {
        $users = self::$repo->findGroupOutsiders(self::getGroup('group_a'));
        $this->assertEquals(3, count($users));
    }

    public function testFindGroupOutsidersByName()
    {
        $users = self::$repo->findGroupOutsidersByName(self::getGroup('group_a'), 'uSeR');
        $this->assertEquals(1, count($users));
        $users = self::$repo->findGroupOutsidersByName(self::getGroup('group_a'), 'doe');
        $this->assertEquals(3, count($users));
    }

    public function testFindAllExcept()
    {
        $users = self::$repo->findAllExcept(self::getUser('john'));
        $this->assertEquals(4, count($users));
    }

    public function testCount()
    {
        $this->assertEquals(5, self::$repo->count());
    }

    public function testFindByUsernames()
    {
        $users = self::$repo->findByUsernames(array('nogroup', 'john'));
        $this->assertEquals(2, count($users));
    }
}
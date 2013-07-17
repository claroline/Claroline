<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class UserRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:User');
        self::createWorkspace('ws_1');
        self::createWorkspace('ws_2');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_2'));
        self::createUser('john', array(self::get('ROLE_1')));
        self::createUser('jane');
        self::createUser('bill');
        self::createUser('bob', array(self::get('ROLE_1'), self::get('ROLE_2')));
        self::createGroup('group_1', array(self::get('jane')), array(self::get('ROLE_1')));
        self::createGroup('group_2', array(self::get('jane'), self::get('bill'), self::get('bob')));
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsernameOnUnknownUsername()
    {
        $user = self::$repo->loadUserByUsername('unknown_user');
    }

    public function testLoadUserByUsername()
    {
        $user = self::$repo->loadUserByUsername('johnUsername');
        $this->assertEquals(self::get('john'), $user);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserThrowsAnExceptionOnUnsupportedUserClass()
    {
        $user = \Mockery::mock('Symfony\Component\Security\Core\User\UserInterface');
        self::$repo->refreshUser($user);
    }

    public function testRefreshUser()
    {
        $user = self::$repo->refreshUser(self::get('john'));
        $this->assertEquals(self::get('john'), $user);
    }

    public function testSupportsClass()
    {
        $coreUserClass = 'Claroline\CoreBundle\Entity\User';
        $extendedUserClass = get_class(\Mockery::mock($coreUserClass));
        $this->assertTrue(self::$repo->supportsClass($coreUserClass));
        $this->assertTrue(self::$repo->supportsClass($extendedUserClass));
        $this->assertFalse(self::$repo->supportsClass('Foo\User'));
    }

    public function testFindByWorkspaceAndRole()
    {
        $users = self::$repo->findByWorkspaceAndRole(self::get('ws_1'), self::get('ROLE_1'));
        $this->assertEquals(3, count($users));
    }

    public function testFindWorkspaceOutsiders()
    {
        $users = self::$repo->findWorkspaceOutsiders(self::get('ws_1'));
        $this->assertEquals(2, count($users));
    }

    public function testFindWorkspaceOutsidersByName()
    {
        $users = self::$repo->findWorkspaceOutsidersByName(self::get('ws_1'), 'B');
        $this->assertEquals(1, count($users));
    }

    public function testFindAll()
    {
        $this->assertEquals(4, count(self::$repo->findAll()));
        $this->assertInstanceOf('Doctrine\ORM\Query', self::$repo->findAll(false));
    }

    public function testFindByName()
    {
        $users = self::$repo->findByName('J');
        $this->assertEquals(2, count($users));
    }

    public function testFindByGroup()
    {
        $users = self::$repo->findByGroup(self::get('group_2'));
        $this->assertEquals(3, count($users));
    }

    public function testFindByNameAndGroup()
    {
        $users = self::$repo->findByNameAndGroup('JANEFIRSTNAME', self::get('group_1'));
        $this->assertEquals(1, count($users));
        $users = self::$repo->findByNameAndGroup('b', self::get('group_2'));
        $this->assertEquals(2, count($users));
    }

    public function testFindByWorkspace()
    {
        $users = self::$repo->findByWorkspace(self::get('ws_1'));
        $this->assertEquals(2, count($users));
        $this->assertEquals(self::get('john'), $users[0]);
        $this->assertEquals(self::get('bob'), $users[1]);
    }

    public function testFindByWorkspaceAndName()
    {
        $users = self::$repo->findByWorkspaceAndName(self::get('ws_1'), 'O');
        $this->assertEquals(2, count($users));
        $users = self::$repo->findByWorkspaceAndName(self::get('ws_1'), 'ohn');
        $this->assertEquals(1, count($users));
    }

    public function testFindGroupOutsiders()
    {
        $users = self::$repo->findGroupOutsiders(self::get('group_1'));
        $this->assertEquals(3, count($users));
    }

    public function testFindGroupOutsidersByName()
    {
        $users = self::$repo->findGroupOutsidersByName(self::get('group_1'), 'o');
        $this->assertEquals(2, count($users));
        $users = self::$repo->findGroupOutsidersByName(self::get('group_1'), 'iLL');
        $this->assertEquals(1, count($users));
    }

    public function testFindAllExcept()
    {
        $users = self::$repo->findAllExcept(self::get('john'));
        $this->assertEquals(3, count($users));
    }

    /**
     * @expectedException Claroline\CoreBundle\Persistence\MissingObjectException
     */
    public function testFindByUsernamesThrowsAnExceptionIfAUserIsMissing()
    {
        $users = self::$repo->findByUsernames(array('johnUsername', 'unknown'));
    }

    public function testFindByUsernames()
    {
        $users = self::$repo->findByUsernames(array('johnUsername', 'janeUsername'));
        $this->assertEquals(2, count($users));
    }

    public function testCount()
    {
        $this->assertEquals(4, self::$repo->count());
    }

    public function testFindUsersEnrolledInMostWorkspaces()
    {
        $users = self::$repo->findUsersEnrolledInMostWorkspaces(10);
        $this->assertEquals(3, count($users));
        $this->assertEquals('bobUsername', $users[0]['username']);
        $lastUsers = array($users[1]['username'], $users[2]['username']);
        $this->assertContains('janeUsername', $lastUsers);
        $this->assertContains('johnUsername', $lastUsers);
        $this->assertEquals(1, $users[1]['total']);
        $this->assertEquals(1, $users[2]['total']);
    }

    public function testFindUsersOwnersOfMostWorkspaces()
    {
        $this->markTestSkipped('A slight modification of workspace fixture is needed to test this method');
    }
}

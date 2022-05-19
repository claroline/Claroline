<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository(User::class);
        self::createWorkspace('ws_1');
        self::createWorkspace('ws_2');
        self::createWorkspace('ws_3');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_2'));
        self::createRole('ROLE_3', self::get('ws_3'));
        self::createUser('john', [self::get('ROLE_1')]);
        self::createUser('jane');
        self::createUser('bill');
        self::createUser('bob', [self::get('ROLE_1'), self::get('ROLE_2'), self::get('ROLE_3')]);
        self::createGroup('group_1', [self::get('jane')], [self::get('ROLE_1')]);
        self::createGroup('group_2', [self::get('jane'), self::get('bill'), self::get('bob')]);
    }

    public function testLoadUserByUsernameOnUnknownUsername()
    {
        $this->expectException(UsernameNotFoundException::class);

        self::$repo->loadUserByUsername('unknown_user');
    }

    public function testLoadUserByUsername()
    {
        $user = self::$repo->loadUserByUsername('john');
        $this->assertEquals(self::get('john'), $user);
    }

    public function testRefreshUserThrowsAnExceptionOnUnsupportedUserClass()
    {
        $this->expectException(UnsupportedUserException::class);

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

    public function testFindAll()
    {
        $this->assertEquals(4, count(self::$repo->findAll()));
    }

    public function testFindByName()
    {
        $users = self::$repo->findByName('j');
        $this->assertEquals(2, count($users));
    }

    public function testFindByGroup()
    {
        $users = self::$repo->findByGroup(self::get('group_2'));
        $this->assertEquals(3, count($users));
    }

    public function testFindByUsernamesThrowsAnExceptionIfAUserIsMissing()
    {
        $users = self::$repo->findByUsernames(['john', 'unknown']);
        $this->assertEquals(1, count($users));
    }

    public function testFindByUsernames()
    {
        $users = self::$repo->findByUsernames(['john', 'jane']);
        $this->assertEquals(2, count($users));
    }

    public function testFindUsersEnrolledInMostWorkspaces()
    {
        $users = self::$repo->findUsersEnrolledInMostWorkspaces(10);
        $this->assertEquals(3, count($users));
        $this->assertEquals('bob', $users[0]['username']);
        $lastUsers = [$users[1]['username'], $users[2]['username']];
        $this->assertContains('john', $lastUsers);
        $this->assertContains('jane', $lastUsers);
        $this->assertEquals(1, $users[1]['total']);
        $this->assertEquals(1, $users[2]['total']);
    }

    public function testFindUsersOwnersOfMostWorkspaces()
    {
        $this->markTestSkipped('A slight modification of workspace fixture is needed to test this method');
    }

    public function testFindByRoles()
    {
        $users = self::$repo->findByRoles([self::get('ROLE_1')]);
        $this->assertEquals(3, count($users));
        $users = self::$repo->findByRoles([self::get('ROLE_2')]);
        $this->assertEquals(1, count($users));
    }
}

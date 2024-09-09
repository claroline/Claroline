<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Tests\Repository;

use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepositoryTest extends RepositoryTestCase
{
    private static ?UserRepository $repo = null;

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

    public function testLoadUserByUsernameOnUnknownUsername(): void
    {
        $this->expectException(UserNotFoundException::class);

        self::$repo->loadUserByUsername('unknown_user');
    }

    public function testLoadUserByUsername(): void
    {
        $user = self::$repo->loadUserByUsername('john');
        $this->assertEquals(self::get('john'), $user);
    }

    public function testRefreshUserThrowsAnExceptionOnUnsupportedUserClass(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $user = \Mockery::mock(UserInterface::class);
        self::$repo->refreshUser($user);
    }

    public function testRefreshUser(): void
    {
        $user = self::$repo->refreshUser(self::get('john'));
        $this->assertEquals(self::get('john'), $user);
    }

    public function testSupportsClass(): void
    {
        $extendedUserClass = get_class(\Mockery::mock(User::class));

        $this->assertTrue(self::$repo->supportsClass(User::class));
        $this->assertTrue(self::$repo->supportsClass($extendedUserClass));
        $this->assertFalse(self::$repo->supportsClass('Foo\User'));
    }

    public function testFindByGroup(): void
    {
        $users = self::$repo->findByGroup(self::get('group_2'));
        $this->assertEquals(3, count($users));
    }

    public function testFindByUsernamesThrowsAnExceptionIfAUserIsMissing(): void
    {
        $users = self::$repo->findByUsernames(['john', 'unknown']);
        $this->assertEquals(1, count($users));
    }

    public function testFindByUsernames(): void
    {
        $users = self::$repo->findByUsernames(['john', 'jane']);
        $this->assertEquals(2, count($users));
    }

    public function testFindUsersOwnersOfMostWorkspaces(): void
    {
        $this->markTestSkipped('A slight modification of workspace fixture is needed to test this method');
    }

    public function testFindByRoles(): void
    {
        $users = self::$repo->findByRoles([self::get('ROLE_1')]);
        $this->assertEquals(3, count($users));
        $users = self::$repo->findByRoles([self::get('ROLE_2')]);
        $this->assertEquals(1, count($users));
    }
}

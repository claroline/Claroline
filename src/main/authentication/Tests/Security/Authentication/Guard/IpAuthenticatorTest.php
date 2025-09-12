<?php

namespace Claroline\AuthenticationBundle\Tests\Security\Authentication\Guard;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Claroline\AuthenticationBundle\Security\Authentication\IpAuthenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class IpAuthenticatorTest extends MockeryTestCase
{
    public function testSupports(): void
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertTrue($authenticator->supports(new Request()));
    }

    public function testAuthenticate(): void
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(true);

        $user = new User();
        $user->setUsername('john.doe');

        $ipUser = new IpUser();
        $ipUser->setUser($user);

        $ipUserRepository = $this->mock(EntityRepository::class);
        $ipUserRepository->shouldReceive('findOneBy')->with(['ip' => '127.0.0.1'])->once()->andReturn($ipUser);

        $userRepository = $this->mock(EntityRepository::class);
        $userRepository->shouldReceive('loadUserByIdentifier')->with('john.doe')->once()->andReturn($user);

        $om = $this->mock(ObjectManager::class);
        $om->shouldReceive('getRepository')->with(IpUser::class)->andReturn($ipUserRepository);
        $om->shouldReceive('getRepository')->with(User::class)->andReturn($userRepository);

        $authenticator = new IpAuthenticator($om);
        $passport = $authenticator->authenticate(new Request([], [], [], [], [], ['REMOTE_ADDR' => '127.0.0.1']));

        $this->assertSame($user, $passport->getUser());
    }

    public function testOnAuthenticationSuccess(): void
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(new Request(), new NullToken(), 'test')
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException())
        );
    }
}

<?php

namespace Claroline\AuthenticationBundle\Tests\Security\Authentication\Guard;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\AuthenticationBundle\Security\Authentication\ApiTokenAuthenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiTokenAuthenticatorTest extends MockeryTestCase
{
    public function testSupports(): void
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertFalse($authenticator->supports(new Request()));
        $this->assertTrue($authenticator->supports(new Request(['apitoken' => 'foo'])));

        $request = new Request();
        $request->headers->set(ApiTokenAuthenticator::HEADER_NAME, 'foo');
        $this->assertTrue($authenticator->supports($request));
    }

    public function testAuthenticate(): void
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(true);

        $user = new User();
        $user->setUsername('john.doe');

        $apiToken = new ApiToken();
        $apiToken->setUser($user);

        $apiTokenRepository = $this->mock(EntityRepository::class);
        $apiTokenRepository->shouldReceive('findOneBy')->with(['token' => 'foo'])->once()->andReturn($apiToken);

        $userRepository = $this->mock(EntityRepository::class);
        $userRepository->shouldReceive('loadUserByIdentifier')->with('john.doe')->once()->andReturn($user);

        $om = $this->mock(ObjectManager::class);
        $om->shouldReceive('getRepository')->with(ApiToken::class)->andReturn($apiTokenRepository);
        $om->shouldReceive('getRepository')->with(User::class)->andReturn($userRepository);

        $authenticator = new ApiTokenAuthenticator($om);
        $passport = $authenticator->authenticate(new Request(['apitoken' => 'foo']));

        $this->assertSame($user, $passport->getUser());
    }

    public function testOnAuthenticationSuccess(): void
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(new Request(), new NullToken(), 'test')
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException())
        );
    }
}

<?php

namespace Claroline\AuthenticationBundle\Tests\Security\Authentication\Guard;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\AuthenticationBundle\Security\Authentication\Guard\ApiTokenAuthenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ApiTokenAuthenticatorTest extends MockeryTestCase
{
    public function testSupports()
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertFalse($authenticator->supports(new Request()));
        $this->assertTrue($authenticator->supports(new Request(['apitoken' => 'foo'])));

        $request = new Request();
        $request->headers->set(ApiTokenAuthenticator::HEADER_NAME, 'foo');
        $this->assertTrue($authenticator->supports($request));
    }

    public function testGetCredentials()
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertSame('foo', $authenticator->getCredentials(new Request(['apitoken' => 'foo'])));

        $request = new Request();
        $request->headers->set(ApiTokenAuthenticator::HEADER_NAME, 'foo');
        $this->assertSame('foo', $authenticator->getCredentials($request));
    }

    public function testGetUser()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);

        $user = new User();

        $apiToken = new ApiToken();
        $apiToken->setUser($user);

        $apiTokenRepository = $this->mock(EntityRepository::class);
        $apiTokenRepository->shouldReceive('findOneBy')->with(['token' => 'foo'])->once()->andReturn($apiToken);

        $om = $this->mock(ObjectManager::class);
        $om->shouldReceive('getRepository')->with(ApiToken::class)->andReturn($apiTokenRepository);

        $authenticator = new ApiTokenAuthenticator($om);
        $this->assertSame($user, $authenticator->getUser('foo', $this->mock(UserProviderInterface::class)));
    }

    public function testOnAuthenticationSuccess()
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(new Request(), new PostAuthenticationGuardToken(new User(), 'test', []), 'test')
        );
    }

    public function testOnAuthenticationFailure()
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException())
        );
    }

    public function testStart()
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));

        $this->assertEquals(
            new RedirectResponse('/'),
            $authenticator->start(new Request())
        );
    }

    public function testSupportsRememberMe()
    {
        $authenticator = new ApiTokenAuthenticator($this->mock(ObjectManager::class));
        $this->assertFalse($authenticator->supportsRememberMe());
    }
}

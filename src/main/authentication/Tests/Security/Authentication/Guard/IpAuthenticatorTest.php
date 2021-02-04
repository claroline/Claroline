<?php

namespace Claroline\AuthenticationBundle\Tests\Security\Authentication\Guard;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Claroline\AuthenticationBundle\Security\Authentication\Guard\IpAuthenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class IpAuthenticatorTest extends MockeryTestCase
{
    public function testSupports()
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertTrue($authenticator->supports(new Request()));
    }

    public function testGetCredentials()
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $request = $this->mock(Request::class);
        $request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $this->assertSame('127.0.0.1', $authenticator->getCredentials($request));
    }

    public function testGetUser()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);

        $user = new User();

        $ipUser = new IpUser();
        $ipUser->setUser($user);

        $ipUserRepository = $this->mock(EntityRepository::class);
        $ipUserRepository->shouldReceive('findOneBy')->with(['ip' => '127.0.0.1'])->once()->andReturn($ipUser);

        $om = $this->mock(ObjectManager::class);
        $om->shouldReceive('getRepository')->with(IpUser::class)->andReturn($ipUserRepository);

        $authenticator = new IpAuthenticator($om);

        $this->assertSame($user, $authenticator->getUser('127.0.0.1', $this->mock(UserProviderInterface::class)));
    }

    public function testOnAuthenticationSuccess()
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(new Request(), new PostAuthenticationGuardToken(new User(), 'test', []), 'test')
        );
    }

    public function testOnAuthenticationFailure()
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException())
        );
    }

    public function testStart()
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));

        $this->assertEquals(new RedirectResponse('/'), $authenticator->start(new Request()));
    }

    public function testSupportsRememberMe()
    {
        $authenticator = new IpAuthenticator($this->mock(ObjectManager::class));
        $this->assertFalse($authenticator->supportsRememberMe());
    }
}

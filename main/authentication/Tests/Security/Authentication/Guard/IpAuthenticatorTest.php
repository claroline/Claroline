<?php

namespace Claroline\AuthenticationBundle\Tests\Security\Authentication\Guard;

use Claroline\AuthenticationBundle\Manager\IPWhiteListManager;
use Claroline\AuthenticationBundle\Security\Authentication\Guard\IpAuthenticator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class IpAuthenticatorTest extends MockeryTestCase
{
    public function testSupports()
    {
        $whitelistManager = $this->mock(IPWhiteListManager::class);
        $whitelistManager->shouldReceive('isWhiteListed')->once()->andReturn(true);

        $config = $this->mock(PlatformConfigurationHandler::class);
        $config->shouldReceive('getParameter')->with('security.default_root_anon_id')->andReturn('foo');

        $authenticator = new IpAuthenticator($config, $whitelistManager);

        $this->assertTrue($authenticator->supports(new Request()));

        $whitelistManager = $this->mock(IPWhiteListManager::class);
        $whitelistManager->shouldReceive('isWhiteListed')->once()->andReturn(false);

        $authenticator = new IpAuthenticator($config, $whitelistManager);

        $this->assertFalse($authenticator->supports(new Request()));
    }

    public function testGetCredentials()
    {
        $config = $this->mock(PlatformConfigurationHandler::class);
        $config->shouldReceive('getParameter')->with('security.default_root_anon_id')->andReturn('foo');

        $authenticator = new IpAuthenticator($config, $this->mock(IPWhiteListManager::class));

        $this->assertSame('foo', $authenticator->getCredentials(new Request()));
    }

    public function testGetUser()
    {
        $user = new User();

        $config = $this->mock(PlatformConfigurationHandler::class);
        $config->shouldReceive('getParameter')->with('security.default_root_anon_id')->andReturn('foo');

        $userProvider = \Mockery::mock(UserProviderInterface::class);
        $userProvider->shouldReceive('loadUserByUsername')->with('foo')->andReturn($user);

        $authenticator = new IpAuthenticator($config, $this->mock(IPWhiteListManager::class));

        $this->assertSame($user, $authenticator->getUser('foo', $userProvider));
    }

    public function testOnAuthenticationSuccess()
    {
        $authenticator = new IpAuthenticator($this->mock(PlatformConfigurationHandler::class), $this->mock(IPWhiteListManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(new Request(), new PostAuthenticationGuardToken(new User(), 'test', []), 'test')
        );
    }

    public function testOnAuthenticationFailure()
    {
        $authenticator = new IpAuthenticator($this->mock(PlatformConfigurationHandler::class), $this->mock(IPWhiteListManager::class));

        $this->assertNull(
            $authenticator->onAuthenticationFailure(new Request(), new AuthenticationException())
        );
    }

    public function testStart()
    {
        $authenticator = new IpAuthenticator($this->mock(PlatformConfigurationHandler::class), $this->mock(IPWhiteListManager::class));

        $this->assertEquals(new RedirectResponse('/'), $authenticator->start(new Request()));
    }

    public function testSupportsRememberMe()
    {
        $authenticator = new IpAuthenticator($this->mock(PlatformConfigurationHandler::class), $this->mock(IPWhiteListManager::class));
        $this->assertFalse($authenticator->supportsRememberMe());
    }
}

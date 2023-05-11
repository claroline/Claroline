<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\Unit\Converter;

use Claroline\CoreBundle\Converter\CurrentUserConverter;
use Claroline\CoreBundle\Converter\InvalidConfigurationException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CurrentUserConverterTest extends MockeryTestCase
{
    private $request;
    private $configuration;
    private $securityContext;
    private $token;
    private $converter;

    protected function setUp(): void
    {
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $this->securityContext = $this->mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->securityContext->shouldReceive('getToken')->andReturn($this->token);
        $this->converter = new CurrentUserConverter($this->securityContext);
    }

    public function testApplyThrowsAnExceptionIfTheNameParameterIsMissing()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionCode(1);

        $this->configuration->shouldReceive('getName')->once()->andReturn(null);
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testSupportsAcceptsOnlyAnonymousAllowedParameter()
    {
        $confTrue = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $confTrue->shouldReceive('getName')->once()->andReturn('user');
        $confTrue->shouldReceive('getOptions')->times(1)->andReturn(
            ['allowAnonymous' => true]
        );

        $this->assertTrue($this->converter->supports($confTrue));

        $confFalse = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $confFalse->shouldReceive('getName')->once()->andReturn('user');
        $confFalse->shouldReceive('getOptions')->times(1)->andReturn(
            ['allowAnonymous' => 'notboolean']
        );

        $this->assertFalse($this->converter->supports($confFalse));
    }

    public function testSupportsAcceptsOnlyBooleanForAnonymousAllowedParameter()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $configuration->shouldReceive('getName')->once()->andReturn('user');
        $configuration->shouldReceive('getOptions')->once()->andReturn(
            ['allowAnonymous' => 'not_a_boolean']
        );

        $this->assertFalse($this->converter->supports($configuration));
    }

    public function testApplyThrowsAnExceptionIfThereIsNoAuthenticatedUserAndAnonymousDisallowed()
    {
        $this->expectException(AccessDeniedException::class);

        $this->configuration->shouldReceive('getName')->once()->andReturn('user');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn([]);

        $this->token->shouldReceive('getUser')->andReturn('anon.');
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheAuthenticatedUserAsARequestAttribute()
    {
        $user = new User();
        $this->request->attributes = new ParameterBag();
        $this->configuration->shouldReceive('getName')->once()->andReturn('user');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn([]);

        $this->token->shouldReceive('getUser')->andReturn($user);
        $this->assertTrue($this->converter->apply($this->request, $this->configuration));
        $this->assertEquals($user, $this->request->attributes->get('user'));
    }

    public function testApplySetsNullIfNoAuthenticatedUserAsARequestAttribute()
    {
        $this->request->attributes = new ParameterBag();
        $this->configuration->shouldReceive('getName')->once()->andReturn('current_user');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['allowAnonymous' => true]);

        $this->token->shouldReceive('getUser')->andReturn('anon.');
        $this->assertTrue($this->converter->apply($this->request, $this->configuration));
        $this->assertEquals(null, $this->request->attributes->get('user'));
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Converter;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class AuthenticatedUserConverterTest extends MockeryTestCase
{
    private $request;
    private $configuration;
    private $securityContext;
    private $token;
    private $translator;
    private $converter;

    protected function setUp()
    {
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $this->securityContext = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->securityContext->shouldReceive('getToken')->andReturn($this->token);
        $this->translator = $this->mock('Symfony\Component\Translation\TranslatorInterface');
        $this->converter = new AuthenticatedUserConverter($this->securityContext, $this->translator);
    }

    public function testSupportsAcceptsOnlyAnAuthenticatedUserParameter()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->times(3)->andReturn(
            ['some_other_option'],
            ['authenticatedUser' => true]
        );
        $this->assertFalse($this->converter->supports($configuration));
        $this->assertTrue($this->converter->supports($configuration));
    }

    /**
     * @expectedException       Claroline\CoreBundle\Converter\InvalidConfigurationException
     * @expectedExceptionCode   1
     */
    public function testApplyThrowsAnExceptionIfTheNameParameterIsMissing()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn(null);
        $this->converter->apply($this->request, $this->configuration);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testApplyThrowsAnExceptionIfThereIsNoAuthenticatedUser()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('user');
        $this->configuration->shouldReceive('getOptions')
            ->once()
            ->andReturn(['authenticatedUser' => true]);
        $this->token->shouldReceive('getUser')->andReturn('anon.');
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheAuthenticatedUserAsARequestAttribute()
    {
        $user = new User();
        $this->request->attributes = new ParameterBag();
        $this->configuration->shouldReceive('getName')->once()->andReturn('user');
        $this->configuration->shouldReceive('getOptions')
            ->once()
            ->andReturn(['authenticatedUser' => true]);
        $this->token->shouldReceive('getUser')->andReturn($user);
        $this->assertTrue($this->converter->apply($this->request, $this->configuration));
        $this->assertEquals($user, $this->request->attributes->get('user'));
    }
}

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

use Symfony\Component\HttpFoundation\ParameterBag;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\User;

class CurrentUserConverterTest extends MockeryTestCase
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

    public function testSupportsAcceptsOnlyParamConverterConfiguration()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface');
        $this->assertFalse($this->converter->supports($configuration));
    }

    /**
     * @expectedException       \Claroline\CoreBundle\Converter\InvalidConfigurationException
     * @expectedExceptionCode   1
     */
    public function testApplyThrowsAnExceptionIfTheNameParameterIsMissing()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn(null);
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testSupportsAcceptsOnlyAnonymousAllowedParameter()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->times(2)->andReturn(
            array('some_other_option'),
            array('anonymousAllowed' => true)
        );

        $this->assertFalse($this->converter->supports($configuration));
        $this->assertTrue($this->converter->supports($configuration));
    }

    public function testSupportsAcceptsOnlyBooleanForAnonymousAllowedParameter()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->once()->andReturn(
            array('anonymousAllowed' => 'not_a_boolean')
        );

        $this->assertFalse($this->converter->supports($configuration));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testApplyThrowsAnExceptionIfThereIsNoAuthenticatedUserAndAnonymousDisallowed()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('current_user');
        $this->configuration->shouldReceive('getOptions')
            ->once()
            ->andReturn(array('authenticatedUser' => true));
        $this->token->shouldReceive('getUser')->andReturn('anon.');
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheAuthenticatedUserAsARequestAttribute()
    {
        $user = new User();
        $this->request->attributes = new ParameterBag();
        $this->configuration->shouldReceive('getName')->once()->andReturn('current_user');

        $this->token->shouldReceive('getUser')->andReturn($user);
        $this->assertTrue($this->converter->apply($this->request, $this->configuration));
        $this->assertEquals($user, $this->request->attributes->get('user'));
    }

    public function testApplySetsNullIfNoAuthenticatedUserAsARequestAttribute()
    {
        $this->request->attributes = new ParameterBag();
        $this->configuration->shouldReceive('getName')->once()->andReturn('current_user');

        $this->token->shouldReceive('getUser')->andReturn('anon.');
        $this->assertTrue($this->converter->apply($this->request, $this->configuration));
        $this->assertEquals(null, $this->request->attributes->get('user'));
    }
}

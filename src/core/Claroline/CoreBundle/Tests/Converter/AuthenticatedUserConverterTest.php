<?php

namespace Claroline\CoreBundle\Converter;

use \Mockery as m;
use Symfony\Component\HttpFoundation\ParameterBag;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\User;

class AuthenticatedUserConverterTest extends MockeryTestCase
{
    private $request;
    private $configuration;
    private $securityContext;
    private $converter;

    protected function setUp()
    {
        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');
        $this->configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $this->securityContext = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->converter = new AuthenticatedUserConverter($this->securityContext);
    }

    public function testSupportsAcceptsOnlyParamConverterConfiguration()
    {
        $configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface');
        $this->assertFalse($this->converter->supports($configuration));
    }

    public function testSupportsAcceptsOnlyAnAuthenticatedUserParameterSetToTrue()
    {
        $configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->times(3)->andReturn(
            array('some_other_option'),
            array('authenticatedUser' => false),
            array('authenticatedUser' => true)
        );
        $this->assertFalse($this->converter->supports($configuration));
        $this->assertFalse($this->converter->supports($configuration));
        $this->assertTrue($this->converter->supports($configuration));
    }

    public function testApplyThrowsAnExceptionIfTheNameParameterIsMissing()
    {
        $this->setExpectedException('Claroline\CoreBundle\Converter\InvalidConfigurationException');
        $this->configuration->shouldReceive('getName')->once()->andReturn(null);
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplyThrowsAnExceptionIfThereIsNoAuthenticatedUser()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        $this->configuration->shouldReceive('getName')->once()->andReturn('user');
        $this->securityContext->shouldReceive('getToken->getUser')->andReturn('anon.');
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheAuthenticatedUserAsARequestAttribute()
    {
        $user = new User();
        $this->request->attributes = new ParameterBag();
        $this->configuration->shouldReceive('getName')->once()->andReturn('user');
        $this->securityContext->shouldReceive('getToken->getUser')->andReturn($user);
        $this->converter->apply($this->request, $this->configuration);
        $this->assertEquals($user, $this->request->attributes->get('user'));
    }
}
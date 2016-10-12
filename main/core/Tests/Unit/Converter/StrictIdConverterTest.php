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

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class StrictIdConverterTest extends MockeryTestCase
{
    private $request;
    private $configuration;
    private $em;
    private $converter;

    protected function setUp()
    {
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $this->em = $this->mock('Doctrine\ORM\EntityManager');
        $this->converter = new StrictIdConverter($this->em);
    }

    public function testSupportsAcceptsOnlyAStrictIdParameterSetToTrue()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->times(3)->andReturn(
            ['some_other_option'],
            ['strictId' => false],
            ['strictId' => true]
        );
        $this->assertFalse($this->converter->supports($configuration));
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
     * @expectedException       Claroline\CoreBundle\Converter\InvalidConfigurationException
     * @expectedExceptionCode   2
     */
    public function testApplyThrowsAnExceptionIfTheClassParameterIsMissing()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn(null);
        $this->converter->apply($this->request, $this->configuration);
    }

    /**
     * @expectedException       Claroline\CoreBundle\Converter\InvalidConfigurationException
     * @expectedExceptionCode   3
     */
    public function testApplyThrowsAnExceptionIfTheIdOptionIsMissing()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn([]);
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplyReturnsFalseIfTheRequestDoesntHaveTheIdAttribute()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['id' => 'someId']);
        $this->request->attributes = new ParameterBag();
        $this->assertFalse($this->converter->apply($this->request, $this->configuration));
    }

    public function testApplyReturnsFalseIfTheIdAttributeIsNullAndTheParameterIsOptional()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['id' => 'someId']);
        $this->configuration->shouldReceive('isOptional')->once()->andReturn(true);
        $this->request->attributes = new ParameterBag();
        $this->request->attributes->set('someId', null);
        $this->assertFalse($this->converter->apply($this->request, $this->configuration));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testApplyThrowsAnExceptionIfTheIdAttributeIsNullAndTheParameterIsNotOptional()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['id' => 'someId']);
        $this->configuration->shouldReceive('isOptional')->once()->andReturn(false);
        $this->request->attributes = new ParameterBag();
        $this->request->attributes->set('someId', null);
        $this->converter->apply($this->request, $this->configuration);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testApplyThrowsAnExceptionIfTheEntityCannotBeFound()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['id' => 'someId']);
        $this->configuration->shouldReceive('isOptional')->once()->andReturn(false);
        $this->request->attributes = new ParameterBag();
        $this->request->attributes->set('someId', 1);
        $repo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->em->shouldReceive('getRepository')->with('Foo\Entity')->once()->andReturn($repo);
        $repo->shouldReceive('find')->with(1)->andReturn(null);
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheRetreivedEntityAsARequestAttribute()
    {
        $entity = 'entity_1';
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['id' => 'someId']);
        $this->request->attributes = new ParameterBag();
        $this->request->attributes->set('someId', 1);
        $repo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->em->shouldReceive('getRepository')->with('Foo\Entity')->once()->andReturn($repo);
        $repo->shouldReceive('find')->with(1)->andReturn($entity);
        $this->assertTrue($this->converter->apply($this->request, $this->configuration));
        $this->assertEquals($entity, $this->request->attributes->get('parameter'));
    }
}

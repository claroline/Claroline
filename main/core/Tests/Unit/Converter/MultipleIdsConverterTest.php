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
use Mockery as m;
use Symfony\Component\HttpFoundation\ParameterBag;

class MultipleIdsConverterTest extends MockeryTestCase
{
    private $request;
    private $configuration;
    private $om;
    private $converter;

    protected function setUp()
    {
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->converter = new MultipleIdsConverter($this->om);
    }

    public function testSupportsAcceptsOnlyAnMultipleIdsParameterSetToTrue()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->times(3)->andReturn(
            ['some_other_option'],
            ['multipleIds' => false],
            ['multipleIds' => true]
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

    public function testApplyThrowsAnExceptionIfNoIdsParameterWerePassed()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['multipleIds' => true]);
        $this->request->query = new ParameterBag();
        $this->request->attributes = m::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->attributes->shouldReceive('set')->once()->with('parameter', []);
        $this->converter->apply($this->request, $this->configuration);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testApplyThrowsAnExceptionIfTheIdsParameterIsNotAnArray()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['multipleIds' => true]);
        $this->request->query = new ParameterBag();
        $this->request->query->set('ids', 'not_an_array');
        $this->converter->apply($this->request, $this->configuration);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testApplyThrowsAnExceptionIfSomeEntitiesCannotBeRetreived()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['multipleIds' => true]);
        $this->request->query = new ParameterBag();
        $this->request->query->set('ids', [1, 2]);
        $this->om->shouldReceive('findByIds')
            ->once()
            ->andThrow('Claroline\CoreBundle\Persistence\MissingObjectException');
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheRetreivedEntitiesAsARequestAttribute()
    {
        $entities = ['entity_1', 'entity_2'];
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->configuration->shouldReceive('getOptions')->once()->andReturn(['multipleIds' => true]);
        $this->request->query = new ParameterBag();
        $this->request->attributes = new ParameterBag();
        $this->request->query->set('ids', [1, 2]);
        $this->om->shouldReceive('findByIds')
            ->once()
            ->with('Foo\Entity', [1, 2])
            ->andReturn($entities);
        $this->assertEquals(true, $this->converter->apply($this->request, $this->configuration));
        $this->assertEquals($entities, $this->request->attributes->get('parameter'));
    }
}

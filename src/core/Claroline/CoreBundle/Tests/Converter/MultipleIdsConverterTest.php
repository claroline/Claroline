<?php

namespace Claroline\CoreBundle\Converter;

use \Mockery as m;
use Symfony\Component\HttpFoundation\ParameterBag;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

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

    public function testSupportsAcceptsOnlyParamConverterConfiguration()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface');
        $this->assertFalse($this->converter->supports($configuration));
    }

    public function testSupportsAcceptsOnlyAnMultipleIdsParameterSetToTrue()
    {
        $configuration = $this->mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');
        $configuration->shouldReceive('getOptions')->times(3)->andReturn(
            array('some_other_option'),
            array('multipleIds' => false),
            array('multipleIds' => true)
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
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testApplyThrowsAnExceptionIfNoIdsParameterWerePassed()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->request->query = new ParameterBag();
        $this->converter->apply($this->request, $this->configuration);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testApplyThrowsAnExceptionIfTheIdsParameterIsNotAnArray()
    {
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
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
        $this->request->query = new ParameterBag();
        $this->request->query->set('ids', array(1, 2));
        $this->om->shouldReceive('findByIds')
            ->once()
            ->andThrow('Claroline\CoreBundle\Persistence\MissingObjectException');
        $this->converter->apply($this->request, $this->configuration);
    }

    public function testApplySetsTheRetreivedEntitiesAsARequestAttribute()
    {
        $entities = array('entity_1', 'entity_2');
        $this->configuration->shouldReceive('getName')->once()->andReturn('parameter');
        $this->configuration->shouldReceive('getClass')->once()->andReturn('Foo\Entity');
        $this->request->query = new ParameterBag();
        $this->request->attributes = new ParameterBag();
        $this->request->query->set('ids', array(1, 2));
        $this->om->shouldReceive('findByIds')
            ->once()
            ->with('Foo\Entity', array(1, 2))
            ->andReturn($entities);
        $this->assertEquals(true, $this->converter->apply($this->request, $this->configuration));
        $this->assertEquals($entities, $this->request->attributes->get('parameter'));
    }
}

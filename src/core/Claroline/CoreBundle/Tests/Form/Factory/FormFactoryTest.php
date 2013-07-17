<?php

namespace Claroline\CoreBundle\Form\Factory;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class FormFactoryTest extends MockeryTestCase
{
    private $formFactory;
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->formFactory = m::mock('Symfony\Component\Form\FormFactoryInterface');
        $this->factory = new FormFactory($this->formFactory);
        $rFactory = new \ReflectionClass($this->factory);
        $rTypes = $rFactory->getProperty('types');
        $rTypes->setAccessible(true);
        $rTypes->setValue(
            null,
            array(
                'fooType' => array(
                    'formType' => 'DOMDocument',
                    'entity' => 'stdClass'
                )
            )
        );
    }

    public function testCreateThrowsAnExceptionOnUnknownFormType()
    {
        $this->setExpectedException('Claroline\CoreBundle\Form\Factory\UnknownTypeException');
        $this->factory->create('unknown_type');
    }

    public function testCreateWithDefaults()
    {
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(anInstanceOf('DOMDocument'), anInstanceOf('stdClass'))
            ->andReturn('someFormInstance');
        $this->assertEquals('someFormInstance', $this->factory->create('fooType'));
    }

    public function testCreateUsesTheEntityPassedInIfAny()
    {
        $entity = new \stdClass();
        $this->formFactory->shouldReceive('create')
            ->once()
            ->with(anInstanceOf('DOMDocument'), $entity)
            ->andReturn('someFormInstance');
        $this->assertEquals(
            'someFormInstance',
            $this->factory->create('fooType', array(), $entity)
        );
    }
}

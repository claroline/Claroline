<?php

namespace Claroline\CoreBundle\Form\Handler;

use HeVinci\CompetencyBundle\Util\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class FormHandlerTest extends UnitTestCase
{
    private $factory;
    private $handler;

    protected function setUp()
    {
        $this->factory = $this->mock('Symfony\Component\Form\FormFactoryInterface');
        $this->handler = new FormHandler($this->factory);
    }

    /**
     * @expectedException \LogicException
     * @dataProvider currentFormMethodProvider
     *
     * @param string $method
     */
    public function testAccessToCurrentFormRequiresPreviousFetch($method)
    {
        $this->handler->{$method}();
    }

    public function testValidateAndRetrieveDataAndView()
    {
        $form = $this->mock('Symfony\Component\Form\Form');
        $request = new Request();

        // validation
        $this->factory->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                ['form.ref'],
                ['form.ref', 'DATA', ['options']]
            )
            ->willReturn($form);
        $form->expects($this->exactly(2))
            ->method('isValid')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->assertTrue($this->handler->isValid('form.ref', $request));
        $this->assertFalse($this->handler->isValid('form.ref', $request, 'DATA', ['options']));

        // retrieval
        $form->expects($this->once())
            ->method('getData')
            ->willReturn('DATA');
        $form->expects($this->once())
            ->method('createView')
            ->willReturn('VIEW');

        $this->assertEquals('DATA', $this->handler->getData());
        $this->assertEquals('VIEW', $this->handler->getView());
    }

    public function testGetViewAndRetrieveData()
    {
        $form = $this->mock('Symfony\Component\Form\Form');
        $this->factory->expects($this->once())
            ->method('create')
            ->with('form.ref')
            ->willReturn($form);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn('VIEW');
        $form->expects($this->once())
            ->method('getData')
            ->willReturn('DATA');

        $this->assertEquals('VIEW', $this->handler->getView('form.ref'));
        $this->assertEquals('DATA', $this->handler->getData());
    }

    public function currentFormMethodProvider()
    {
        return [
            ['getData'],
            ['getView'],
        ];
    }

    protected function mock($class)
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

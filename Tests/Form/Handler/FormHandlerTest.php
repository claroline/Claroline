<?php

namespace HeVinci\CompetencyBundle\Form\Handler;

use HeVinci\CompetencyBundle\Util\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class FormHandlerTest extends UnitTestCase
{
    private $container;
    private $handler;

    protected function setUp()
    {
        $this->container = $this->mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->handler = new FormHandler($this->container);
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider formMethodProvider
     * @param string $method
     */
    public function testHandlerRequiresValidFormReference($method)
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('form.ref')
            ->willReturn('NOT A FORM');
        $this->handler->{$method}('form.ref', new Request());
    }

    /**
     * @expectedException LogicException
     * @dataProvider currentFormMethodProvider
     * @param string $method
     */
    public function testAccessToCurrentFormRequiresPreviousFetch($method)
    {
        $this->handler->{$method}();
    }

    public function testValidateAndRetrieveDataAndView()
    {
        $form = $this->mock('Symfony\Component\Form\Form');

        // validation
        $this->container->expects($this->exactly(2))
            ->method('get')
            ->with('form.ref')
            ->willReturn($form);
        $form->expects($this->exactly(2))
            ->method('isValid')
            ->willReturnOnConsecutiveCalls(true, false);
        $form->expects($this->once())
            ->method('setData')
            ->with('DATA');
        $this->assertTrue($this->handler->isValid('form.ref', new Request()));
        $this->assertFalse($this->handler->isValid('form.ref', new Request(), 'DATA'));

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
        $this->container->expects($this->once())
            ->method('get')
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

    public function formMethodProvider()
    {
        return [
            ['isValid'],
            ['getView']
        ];
    }

    public function currentFormMethodProvider()
    {
        return [
            ['getData'],
            ['getView']
        ];
    }
}

<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class ScaleControllerTest extends UnitTestCase
{
    private $manager;
    private $formHandler;
    private $controller;

    protected function setUp()
    {
        $this->manager = $this->mock('HeVinci\CompetencyBundle\Manager\CompetencyManager');
        $this->formHandler = $this->mock('HeVinci\CompetencyBundle\Form\Handler\FormHandler');
        $this->controller = new ScaleController($this->manager, $this->formHandler);
    }

    public function testNewScaleAction()
    {
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_scale')
            ->willReturn('VIEW');
        $this->assertEquals(['form' => 'VIEW'], $this->controller->newScaleAction());
    }

    public function testCreateValidScaleAction()
    {
        $request = new Request();
        $scale = new Scale();

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_scale', $request)
            ->willReturn(true);
        $this->formHandler->expects($this->once())
            ->method('getData')
            ->willReturn($scale);
        $this->manager->expects($this->once())
            ->method('createScale')
            ->with($scale)
            ->willReturn($scale);

        $this->assertEquals(
            json_encode($scale),
            $this->controller->createScaleAction($request)->getContent()
        );
    }

    public function testCreateInvalidScaleAction()
    {
        $request = new Request();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_scale', $request)
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('VIEW');
        $this->assertEquals(['form' => 'VIEW'], $this->controller->createScaleAction($request));
    }

    public function testScalesAction()
    {
        $this->manager->expects($this->once())
            ->method('listScales')
            ->willReturn('SCALES');
        $this->assertEquals(['scales' => 'SCALES'], $this->controller->scalesAction());
    }

    public function testScaleAction()
    {
        $scale = new Scale();
        $this->formHandler->expects($this->exactly(2))
            ->method('getView')
            ->withConsecutive(
                ['hevinci_form_scale', $scale, ['read_only' => true]],
                ['hevinci_form_scale', $scale, ['read_only' => false]]
            )
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'scale' => null],
            $this->controller->scaleAction($scale, 0)
        );
        $this->assertEquals(
            ['form' => 'FORM', 'scale' => $scale],
            $this->controller->scaleAction($scale, 1)
        );
    }

    public function testEditValidScaleAction()
    {
        $request = new Request();
        $scale = new Scale();

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_scale', $request)
            ->willReturn(true);
        $this->formHandler->expects($this->once())
            ->method('getData')
            ->willReturn($scale);
        $this->manager->expects($this->once())
            ->method('updateScale')
            ->with($scale)
            ->willReturn($scale);

        $this->assertEquals(
            json_encode($scale),
            $this->controller->editScaleAction($request, $scale)->getContent()
        );
    }

    public function testEditInvalidScaleAction()
    {
        $scale = new Scale();
        $request = new Request();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_scale', $request)
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('VIEW');
        $this->assertEquals(
            ['form' => 'VIEW', 'scale' => $scale],
            $this->controller->editScaleAction($request, $scale)
        );
    }

    public function testDeleteScaleAction()
    {
        $scale = new Scale();
        $this->manager->expects($this->once())
            ->method('deleteScale')
            ->with($scale);
        $this->controller->deleteScaleAction($scale);
    }
}

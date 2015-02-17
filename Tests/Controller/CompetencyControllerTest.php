<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class CompetencyControllerTest extends UnitTestCase
{
    private $manager;
    private $formHandler;
    private $controller;

    protected function setUp()
    {
        $this->manager = $this->mock('HeVinci\CompetencyBundle\Manager\CompetencyManager');
        $this->formHandler = $this->mock('HeVinci\CompetencyBundle\Form\Handler\FormHandler');
        $this->controller = new CompetencyController($this->manager, $this->formHandler);
    }

    public function testFrameworksAction()
    {
        $this->manager->expects($this->once())
            ->method('listFrameworks')
            ->willReturn('FRAMEWORKS');
        $this->manager->expects($this->once())
            ->method('hasScales')
            ->willReturn(true);
        $this->assertEquals(
            ['frameworks' => 'FRAMEWORKS', 'hasScales' => true],
            $this->controller->frameworksAction()
        );
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
            ->method('persistScale')
            ->with($scale);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $this->controller->createScaleAction($request)
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
            ->method('persistScale')
            ->with($scale);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $this->controller->editScaleAction($request, $scale)
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

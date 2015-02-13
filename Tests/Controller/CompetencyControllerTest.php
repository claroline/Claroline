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
            ->with('hevinci.form.scale')
            ->willReturn('VIEW');
        $this->assertEquals(['form' => 'VIEW'], $this->controller->newScaleAction());
    }

    public function testCreateValidScaleAction()
    {
        $request = new Request();
        $scale = new Scale();

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci.form.scale', $request)
            ->willReturn(true);
        $this->formHandler->expects($this->once())
            ->method('getData')
            ->willReturn($scale);
        $this->manager->expects($this->once())
            ->method('createScale')
            ->with($scale);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $this->controller->createScaleAction($request)
        );
    }

    public function  testCreateInvalidScaleAction()
    {
        $request = new Request();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci.form.scale', $request)
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('VIEW');
        $this->assertEquals(['form' => 'VIEW'], $this->controller->createScaleAction($request));
    }
}

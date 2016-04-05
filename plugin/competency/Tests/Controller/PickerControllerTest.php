<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class PickerControllerTest extends UnitTestCase
{
    private $competencyManager;
    private $controller;

    protected function setUp()
    {
        $this->competencyManager = $this->mock('HeVinci\CompetencyBundle\Manager\CompetencyManager');
        $this->controller = new PickerController($this->competencyManager);
    }

    public function testFrameworksAction()
    {
        $this->competencyManager->expects($this->once())
            ->method('listFrameworks')
            ->willReturn('FRAMEWORKS');
        $this->assertEquals(['frameworks' => 'FRAMEWORKS'], $this->controller->frameworksAction());
    }

    public function testFrameworkCompetenciesAction()
    {
        $framework = new Competency();
        $this->competencyManager->expects($this->once())
            ->method('loadCompetency')
            ->with($framework, true)
            ->willReturn('FRAMEWORK');
        $this->assertEquals(
            ['framework' => 'FRAMEWORK'],
            $this->controller->competenciesAction($framework, 1)
        );
    }
}

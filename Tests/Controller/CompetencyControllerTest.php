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
}

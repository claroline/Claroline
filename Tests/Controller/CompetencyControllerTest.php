<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
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

    public function testNewFrameworkAction()
    {
        $this->manager->expects($this->once())->method('ensureHasScale');
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_framework')
            ->willReturn('FORM');
        $this->assertEquals(['form' => 'FORM'], $this->controller->newFrameworkAction());
    }

    public function testValidCreateFrameworkAction()
    {
        $request = new Request();
        $framework = new Competency();

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_framework', $request)
            ->willReturn(true);
        $this->formHandler->expects($this->once())
            ->method('getData')
            ->willReturn($framework);
        $this->manager->expects($this->once())
            ->method('persistFramework')
            ->with($framework)
            ->willReturn($framework);

        $this->assertEquals(
            json_encode($framework),
            $this->controller->createFrameworkAction($request)->getContent()
        );
    }

    public function testInvalidCreateFrameworkAction()
    {
        $request = new Request();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_framework', $request)
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');
        $this->assertEquals(['form' => 'FORM'], $this->controller->createFrameworkAction($request));
    }

    public function testFrameworkAction()
    {
        $framework = new Competency();
        $this->manager->expects($this->once())
            ->method('loadFramework')
            ->with($framework)
            ->willReturn('FRAMEWORK');
        $this->assertEquals(
            ['framework' => 'FRAMEWORK'],
            $this->controller->frameworkAction($framework)
        );
    }

    public function testFrameworkEditionFormAction()
    {
        $framework = new Competency();
        $this->manager->expects($this->once())
            ->method('ensureIsRoot')
            ->with($framework);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_framework', $framework)
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'framework' => $framework],
            $this->controller->frameworkEditionFormAction($framework)
        );
    }

    public function testValidEditFrameworkAction()
    {
        $request = new Request();
        $framework = new Competency();

        $this->manager->expects($this->once())
            ->method('ensureIsRoot')
            ->with($framework);
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_framework', $request, $framework)
            ->willReturn(true);
        $this->manager->expects($this->once())
            ->method('updateCompetency')
            ->with($framework)
            ->willReturn($framework);

        $this->assertEquals(
            json_encode($framework),
            $this->controller->editFrameworkAction($request, $framework)->getContent()
        );
    }

    public function testInvalidEditFrameworkAction()
    {
        $request = new Request();
        $framework = new Competency();

        $this->manager->expects($this->once())
            ->method('ensureIsRoot')
            ->with($framework);
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_framework', $request, $framework)
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'framework' => $framework],
            $this->controller->editFrameworkAction($request, $framework)
        );
    }

    public function testDeleteCompetencyAction()
    {
        $competency = new Competency();
        $this->manager->expects($this->once())
            ->method('deleteCompetency')
            ->with($competency);
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\JsonResponse',
            $this->controller->deleteCompetencyAction($competency)
        );
    }

    public function testNewSubCompetencyAction()
    {
        $parent = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $parent->expects($this->once())->method('getId')->willReturn(1);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_competency', null, ['parent_competency' => $parent])
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'parentId' => 1],
            $this->controller->newSubCompetencyAction($parent)
        );
    }

    public function testValidCreateSubCompetencyAction()
    {
        $request = new Request();
        $parent = new Competency();
        $competency = new Competency();

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_competency', $request, null, ['parent_competency' => $parent])
            ->willReturn(true);
        $this->formHandler->expects($this->once())
            ->method('getData')
            ->willReturn($competency);
        $this->manager->expects($this->once())
            ->method('createSubCompetency')
            ->with($parent, $competency)
            ->willReturn($competency);

        $this->assertEquals(
            json_encode($competency),
            $this->controller->createSubCompetencyAction($request, $parent)->getContent()
        );
    }

    public function testInvalidCreateSubCompetencyAction()
    {
        $request = new Request();
        $parent = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $parent->expects($this->once())->method('getId')->willReturn(1);

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_competency', $request, null, ['parent_competency' => $parent])
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');

        $this->assertEquals(
            ['form' => 'FORM', 'parentId' => 1],
            $this->controller->createSubCompetencyAction($request, $parent)
        );
    }

    public function testCompetencyAction()
    {
        $competency = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $competency->expects($this->once())->method('getId')->willReturn(1);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_competency', $competency)
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'id' => 1],
            $this->controller->competencyAction($competency)
        );
    }

    public function testValidEditCompetencyAction()
    {
        $request = new Request();
        $competency = new Competency();

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_competency', $request, $competency)
            ->willReturn(true);
        $this->manager->expects($this->once())
            ->method('updateCompetency')
            ->with($competency)
            ->willReturn($competency);

        $this->assertEquals(
            json_encode($competency),
            $this->controller->editCompetencyAction($request, $competency)->getContent()
        );
    }

    public function testInvalidEditCompetencyAction()
    {
        $request = new Request();
        $competency = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $competency->expects($this->once())->method('getId')->willReturn(1);

        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_competency', $request, $competency)
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');

        $this->assertEquals(
            ['form' => 'FORM', 'id' => 1],
            $this->controller->editCompetencyAction($request, $competency)
        );
    }

    public function testNewAbilityAction()
    {
        $parent = new Competency();
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_ability', null, ['competency' => $parent])
            ->willReturn('FORM');
        $this->assertEquals(['form' => 'FORM', 'competency' => $parent], $this->controller->newAbilityAction($parent));
    }

    public function testInvalidCreateAbilityAction()
    {
        $parent = new Competency();
        $request = new Request();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_ability', $request, null, ['competency' => $parent])
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'competency' => $parent],
            $this->controller->createAbilityAction($request, $parent)
        );
    }

    public function testValidCreateAbilityAction()
    {
        $parent = new Competency();
        $request = new Request();
        $ability = new Ability();
        $level = new Level();
        $ability->setLevel($level);
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_ability', $request, null, ['competency' => $parent])
            ->willReturn(true);
        $this->formHandler->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($ability);
        $this->manager->expects($this->once())
            ->method('createAbility')
            ->with($parent, $ability, $level)
            ->willReturn($ability);
        $this->assertEquals(
            json_encode($ability),
            $this->controller->createAbilityAction($request, $parent)->getContent()
        );
    }

    public function testDeleteAbilityAction()
    {
        $parent = new Competency();
        $ability = new Ability();
        $this->manager->expects($this->once())
            ->method('removeAbility')
            ->with($parent, $ability)
            ->willReturn(true);
        $this->assertEquals(
            json_encode(true),
            $this->controller->deleteAbilityAction($parent, $ability)->getContent()
        );
    }

    public function testAbilityAction()
    {
        $parent = new Competency();
        $ability = new Ability();
        $this->manager->expects($this->once())
            ->method('loadAbility')
            ->with($parent, $ability);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_ability', $ability, ['competency' => $parent])
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'competency' => $parent, 'ability' => $ability],
            $this->controller->abilityAction($parent, $ability)
        );
    }

    public function testInvalidEditAbilityAction()
    {
        $request = new Request();
        $parent = new Competency();
        $ability = new Ability();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_ability', $request, $ability, ['competency' => $parent])
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'competency' => $parent, 'ability' => $ability],
            $this->controller->editAbilityAction($request, $parent, $ability)
        );
    }

    public function testValidEditAbilityAction()
    {
        $request = new Request();
        $parent = new Competency();
        $ability = new Ability();
        $level = new Level();
        $ability->setLevel($level);
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_ability', $request, $ability, ['competency' => $parent])
            ->willReturn(true);
        $this->formHandler->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($ability);
        $this->manager->expects($this->once())
            ->method('updateAbility')
            ->with($parent, $ability, $level)
            ->willReturn($ability);
        $this->assertEquals(
            json_encode($ability),
            $this->controller->editAbilityAction($request, $parent, $ability)->getContent()
        );
    }

    public function testAddAbilityFormAction()
    {
        $parent = new Competency();
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->with('hevinci_form_ability_import', null, ['competency' => $parent])
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'competency' => $parent],
            $this->controller->addAbilityFormAction($parent)
        );
    }

    public function testInvalidAddAbilityAction()
    {
        $request = new Request();
        $parent = new Competency();
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_ability_import', $request, null, ['competency' => $parent])
            ->willReturn(false);
        $this->formHandler->expects($this->once())
            ->method('getView')
            ->willReturn('FORM');
        $this->assertEquals(
            ['form' => 'FORM', 'competency' => $parent],
            $this->controller->addAbilityAction($request, $parent)
        );
    }

    public function testValidAddAbilityAction()
    {
        $request = new Request();
        $parent = new Competency();
        $ability = new Ability();
        $level = new Level();
        $ability->setLevel($level);
        $this->formHandler->expects($this->once())
            ->method('isValid')
            ->with('hevinci_form_ability_import', $request, null, ['competency' => $parent])
            ->willReturn(true);
        $this->formHandler->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($ability);
        $this->manager->expects($this->once())
            ->method('linkAbilityToCompetency')
            ->with($parent, $ability, $level)
            ->willReturn($ability);
        $this->assertEquals(
            json_encode($ability),
            $this->controller->addAbilityAction($request, $parent)->getContent()
        );
    }

    public function testSuggestAbilityAction()
    {
        $parent = new Competency();
        $this->manager->expects($this->once())
            ->method('suggestAbilities')
            ->with($parent, 'SEARCH')
            ->willReturn('RESULT');
        $this->assertEquals(
            json_encode('RESULT'),
            $this->controller->suggestAbilityAction($parent, 'SEARCH')->getContent()
        );
    }

    public function testActivitiesAction()
    {
        $framework = new Competency();
        $this->manager->expects($this->once())
            ->method('loadFramework')
            ->with($framework)
            ->willReturn('FRAMEWORK');
        $this->assertEquals(
            ['framework' => 'FRAMEWORK'],
            $this->controller->activitiesAction($framework)
        );
    }
}

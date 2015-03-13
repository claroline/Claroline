<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class ActivityControllerTest extends UnitTestCase
{
    private $competencyManager;
    private $activityManager;
    private $controller;

    protected function setUp()
    {
        $this->competencyManager = $this->mock('HeVinci\CompetencyBundle\Manager\CompetencyManager');
        $this->activityManager = $this->mock('HeVinci\CompetencyBundle\Manager\ActivityManager');
        $this->controller = new ActivityController($this->competencyManager, $this->activityManager);
    }

    public function testCompetenciesAction()
    {
        $activity = new Activity();
        $this->activityManager->expects($this->once())
            ->method('loadLinkedCompetencies')
            ->with($activity)
            ->willReturn('COMPETENCIES');
        $this->assertEquals(
            ['_resource' => $activity, 'competencies' => 'COMPETENCIES'],
            $this->controller->competenciesAction($activity)
        );
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
            ->method('loadFramework')
            ->with($framework)
            ->willReturn('FRAMEWORK');
        $this->assertEquals(
            ['framework' => 'FRAMEWORK'],
            $this->controller->frameworkCompetenciesAction($framework)
        );
    }

    public function testLinkAbilityActionForAlreadyLinkedAbilities()
    {
        $activity = new Activity();
        $ability = new Ability();
        $this->activityManager->expects($this->once())
            ->method('linkActivityToAbility')
            ->with($activity, $ability)
            ->willReturn(false);
        $response = $this->controller->linkAbilityAction($activity, $ability);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testLinkAbilityAction()
    {
        $activity = new Activity();
        $ability = new Ability();
        $this->activityManager->expects($this->once())
            ->method('linkActivityToAbility')
            ->with($activity, $ability)
            ->willReturn('ABILITY');
        $response = $this->controller->linkAbilityAction($activity, $ability);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(json_encode('ABILITY'), $response->getContent());
    }

    public function testRemoveAbilityLinkAction()
    {
        $activity = new Activity();
        $ability = new Ability();
        $this->activityManager->expects($this->once())
            ->method('removeAbilityLink')
            ->with($activity, $ability)
            ->willReturn('ABILITY');
        $this->assertEquals(
            json_encode('ABILITY'),
            $this->controller->removeAbilityLinkAction($activity, $ability)->getContent()
        );
    }
}

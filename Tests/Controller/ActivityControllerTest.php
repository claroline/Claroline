<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class ActivityControllerTest extends UnitTestCase
{
    private $activityManager;
    private $controller;

    protected function setUp()
    {
        $this->activityManager = $this->mock('HeVinci\CompetencyBundle\Manager\ActivityManager');
        $this->controller = new ActivityController($this->activityManager);
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

    public function testLinkAbilityActionForAlreadyLinkedAbilities()
    {
        $activity = new Activity();
        $ability = new Ability();
        $this->activityManager->expects($this->once())
            ->method('createLink')
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
            ->method('createLink')
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
            ->method('removeLink')
            ->with($activity, $ability)
            ->willReturn('ABILITY');
        $this->assertEquals(
            json_encode('ABILITY'),
            $this->controller->removeAbilityLinkAction($activity, $ability)->getContent()
        );
    }
}

<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
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

    /**
     * @dataProvider linkActionProvider
     * @param string $method
     * @param string $target
     */
    public function testLinkActionsForAlreadyLinkedTargets($method, $target)
    {
        $activity = new Activity();
        $this->activityManager->expects($this->once())
            ->method('createLink')
            ->with($activity, $target)
            ->willReturn(false);
        $response = $this->controller->{$method}($activity, $target);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @dataProvider linkActionProvider
     * @param string $method
     * @param string $target
     */
    public function testLinkActions($method, $target)
    {
        $activity = new Activity();
        $this->activityManager->expects($this->once())
            ->method('createLink')
            ->with($activity, $target)
            ->willReturn('TARGET');
        $response = $this->controller->{$method}($activity, $target);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(json_encode('TARGET'), $response->getContent());
    }

    /**
     * @dataProvider removeLinkActionProvider
     * @param string $method
     * @param string $target
     */
    public function testRemoveLinkActions($method, $target)
    {
        $activity = new Activity();
        $this->activityManager->expects($this->once())
            ->method('removeLink')
            ->with($activity, $target)
            ->willReturn('TARGET');
        $this->assertEquals(
            json_encode('TARGET'),
            $this->controller->{$method}($activity, $target)->getContent()
        );
    }

    public function testCompetencyActivitiesAction()
    {
        $competency = new Competency();
        $this->assertEquals(
            ['competency' => $competency],
            $this->controller->competencyActivitiesAction($competency)
        );
    }

    public function testAbilityActivitiesAction()
    {
        $ability = new Ability();
        $this->assertEquals(
            ['ability' => $ability],
            $this->controller->abilityActivitiesAction($ability)
        );
    }

    public function linkActionProvider()
    {
        return [
            ['linkAbilityAction', new Ability()],
            ['linkCompetencyAction', new Competency()]
        ];
    }

    public function removeLinkActionProvider()
    {
        return [
            ['removeAbilityLinkAction', new Ability()],
            ['removeCompetencyLinkAction', new Competency()]
        ];
    }
}

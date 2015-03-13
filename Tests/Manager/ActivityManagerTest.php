<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\Activity;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class ActivityManagerTest extends UnitTestCase
{
    private $om;
    private $competencyRepo;
    private $abilityRepo;
    private $manager;

    protected function setUp()
    {
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->competencyRepo = $this->mock('HeVinci\CompetencyBundle\Repository\CompetencyRepository');
        $this->abilityRepo = $this->mock('HeVinci\CompetencyBundle\Repository\AbilityRepository');
        $this->om->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['HeVinciCompetencyBundle:Ability'],
                ['HeVinciCompetencyBundle:Competency']
            )
            ->willReturnOnConsecutiveCalls(
                $this->abilityRepo,
                $this->competencyRepo
            );
        $this->manager = new ActivityManager($this->om);
    }

    /**
     * @expectedException \LogicException
     */
    public function testRemoveAbilityLinkThrowsIfNoLink()
    {
        $this->manager->removeAbilityLink(new Activity(), new Ability());
    }

    public function testRemoveAbilityLink()
    {
        $activity = new Activity();
        $ability = $this->mock('HeVinci\CompetencyBundle\Entity\Ability');
        $ability->expects($this->once())->method('isLinkedToActivity')->with($activity)->willReturn(true);
        $ability->expects($this->once())->method('removeActivity')->with($activity);
        $this->om->expects($this->once())->method('flush');
        $this->manager->removeAbilityLink($activity, $ability);
    }
}

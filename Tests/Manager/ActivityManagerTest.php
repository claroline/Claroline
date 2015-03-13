<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\Activity;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
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
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveLinkThrowsOnWrongTargetType()
    {
        $this->manager->removeLink(new Activity(), 'Bad type');
    }

    /**
     * @expectedException \LogicException
     */
    public function testRemoveLinkThrowsIfNoLink()
    {
        $this->manager->removeLink(new Activity(), new Ability());
    }

    /**
     * @dataProvider targetClassProvider
     * @param string $targetClass
     */
    public function testRemoveLink($targetClass)
    {
        $activity = new Activity();
        $ability = $this->mock($targetClass);
        $ability->expects($this->once())->method('isLinkedToActivity')->with($activity)->willReturn(true);
        $ability->expects($this->once())->method('removeActivity')->with($activity);
        $this->om->expects($this->once())->method('flush');
        $this->manager->removeLink($activity, $ability);
    }

    public function targetClassProvider()
    {
        return [
            ['HeVinci\CompetencyBundle\Entity\Ability'],
            ['HeVinci\CompetencyBundle\Entity\Competency']
        ];
    }
}

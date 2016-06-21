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

    public function testLoadLinkedCompetencies()
    {
        $activity = new Activity();
        $ability = new Ability();
        $competency = new Competency();
        $this->abilityRepo->expects($this->once())
            ->method('findByActivity')
            ->with($activity)
            ->willReturn([$ability]);
        $this->competencyRepo->expects($this->once())
            ->method('findByActivity')
            ->with($activity)
            ->willReturn([$competency]);
        $this->competencyRepo->expects($this->any())
            ->method('getPath')
            ->willReturn([]);
        $this->manager->loadLinkedCompetencies($activity);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateLinkThrowsOnWrongTargetType()
    {
        $this->manager->createLink(new Activity(), 'Bad type');
    }

    public function testCreateLinkReturnsFalseIfLinkExists()
    {
        $activity = new Activity();
        $ability = new Ability();
        $ability->linkActivity($activity);
        $this->assertFalse($this->manager->createLink($activity, $ability));
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
     * @dataProvider targetProvider
     *
     * @param mixed $target
     */
    public function testRemoveLink($target)
    {
        $activity = new Activity();
        $target->linkActivity($activity);
        $this->om->expects($this->once())->method('flush');
        $this->manager->removeLink($activity, $target);
        $this->assertFalse($target->isLinkedToActivity($activity));
    }

    public function targetProvider()
    {
        return [
            [new Ability()],
            [new Competency()],
        ];
    }
}

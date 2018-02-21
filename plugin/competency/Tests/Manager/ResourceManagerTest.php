<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class ResourceManagerTest extends UnitTestCase
{
    private $om;
    private $competencyRepo;
    private $abilityRepo;
    private $manager;

    protected function setUp()
    {
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
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
        $this->manager = new ResourceManager($this->om);
    }

    public function testLoadLinkedCompetencies()
    {
        $resource = new ResourceNode();
        $ability = new Ability();
        $competency = new Competency();
        $this->abilityRepo->expects($this->once())
            ->method('findByResource')
            ->with($resource)
            ->willReturn([$ability]);
        $this->competencyRepo->expects($this->once())
            ->method('findByResource')
            ->with($resource)
            ->willReturn([$competency]);
        $this->competencyRepo->expects($this->any())
            ->method('getPath')
            ->willReturn([]);
        $this->manager->loadLinkedCompetencies($resource);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateLinkThrowsOnWrongTargetType()
    {
        $this->manager->createLink(new ResourceNode(), 'Bad type');
    }

    public function testCreateLinkReturnsFalseIfLinkExists()
    {
        $resource = new ResourceNode();
        $ability = new Ability();
        $ability->linkResource($resource);
        $this->assertFalse($this->manager->createLink($resource, $ability));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveLinkThrowsOnWrongTargetType()
    {
        $this->manager->removeLink(new ResourceNode(), 'Bad type');
    }

    /**
     * @expectedException \LogicException
     */
    public function testRemoveLinkThrowsIfNoLink()
    {
        $this->manager->removeLink(new ResourceNode(), new Ability());
    }

    /**
     * @dataProvider targetProvider
     *
     * @param mixed $target
     */
    public function testRemoveLink($target)
    {
        $resource = new ResourceNode();
        $target->linkResource($resource);
        $this->om->expects($this->once())->method('flush');
        $this->manager->removeLink($resource, $target);
        $this->assertFalse($target->isLinkedToResource($resource));
    }

    public function targetProvider()
    {
        return [
            [new Ability()],
            [new Competency()],
        ];
    }
}

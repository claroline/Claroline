<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class CompetencyManagerTest extends UnitTestCase
{
    private $om;
    private $competencyRepo;
    private $scaleRepo;
    private $manager;

    protected function setUp()
    {
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->competencyRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->scaleRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->om->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['HeVinciCompetencyBundle:Competency'],
                ['HeVinciCompetencyBundle:Scale']
            )
            ->willReturnOnConsecutiveCalls(
                $this->competencyRepo,
                $this->scaleRepo
            );
        $this->manager = new CompetencyManager($this->om);
    }

    public function testListFrameworks()
    {
        $this->competencyRepo->expects($this->once())
            ->method('findAll')
            ->willReturn(['foo']);
        $this->assertEquals(['foo'], $this->manager->listFrameworks());
    }

    public function testHasScales()
    {
        $this->om->expects($this->exactly(2))
            ->method('count')
            ->with('HeVinciCompetencyBundle:Scale')
            ->willReturnOnConsecutiveCalls(3, 0);
        $this->assertTrue($this->manager->hasScales());
        $this->assertFalse($this->manager->hasScales());
    }

    public function testPersistScale()
    {
        $scale = new Scale();
        $this->om->expects($this->once())->method('persist')->with($scale);
        $this->om->expects($this->once())->method('flush');
        $this->manager->persistScale($scale);
    }

    public function testListScales()
    {
        $this->scaleRepo->expects($this->once())
            ->method('findAll')
            ->willReturn(['foo']);
        $this->assertEquals(['foo'], $this->manager->listScales());
    }

    /**
     * @expectedException LogicException
     */
    public function testDeleteScaleExpectsNonLockedScale()
    {
        $scale = new Scale();
        $scale->setIsLocked(true);
        $this->manager->deleteScale($scale);
    }

    public function testDeleteScale()
    {
        $scale = new Scale();
        $this->om->expects($this->once())
            ->method('remove')
            ->with($scale);
        $this->om->expects($this->once())->method('flush');
        $this->manager->deleteScale($scale);
    }
}

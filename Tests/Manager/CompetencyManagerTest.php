<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class CompetencyManagerTest extends UnitTestCase
{
    private $om;
    private $competencyRepo;
    private $manager;

    protected function setUp()
    {
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->competencyRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->om->expects()
            ->method('getRepository')
            ->with('HeVinciCompetencyBundle:Competency')
            ->willReturn($this->competencyRepo);
        $this->manager = new CompetencyManager($this->om);
    }

    public function testListFrameworks()
    {
        $this->competencyRepo->expects()->method('findAll')->willReturn(['foo']);
        $this->assertEquals(['foo'], $this->manager->listFrameworks());
    }

    public function testHasScales()
    {
        $this->om->expects()
            ->method('count')
            ->with('HeVinciCompetencyBundle:Scale')
            ->willReturn(3, 0);
        $this->assertTrue($this->manager->hasScales());
        $this->assertFalse($this->manager->hasScales());
    }

    public function testCreateScale()
    {
        $scale = new Scale();
        $this->om->expects()->method('persist')->with($scale);
        $this->om->expects()->method('flush');
        $this->manager->createScale($scale);
    }
}

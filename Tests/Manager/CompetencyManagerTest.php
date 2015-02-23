<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class CompetencyManagerTest extends UnitTestCase
{
    private $om;
    private $translator;
    private $competencyRepo;
    private $scaleRepo;
    private $manager;

    protected function setUp()
    {
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->translator = $this->mock('Symfony\Component\Translation\TranslatorInterface');
        $this->competencyRepo = $this->mock('Gedmo\Tree\Entity\Repository\NestedTreeRepository');
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
        $this->manager = new CompetencyManager($this->om, $this->translator);
    }

    public function testListFrameworks()
    {
        $this->competencyRepo->expects($this->once())
            ->method('findBy')
            ->with(['parent' => null])
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
        $this->assertEquals($scale, $this->manager->persistScale($scale));
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

    public function testEnsureHasScale()
    {
        $this->om->expects($this->once())
            ->method('count')
            ->with('HeVinciCompetencyBundle:Scale')
            ->willReturn(0);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturn('TRANSLATED');
        $this->om->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($scale) {
                $this->assertEquals('TRANSLATED', $scale->getName());
                $this->assertEquals(1, count($scale->getLevels()));
                $this->assertEquals('TRANSLATED', $scale->getLevels()[0]->getName());
                $this->assertEquals(0, $scale->getLevels()[0]->getValue());

                return true;
            }));
        $this->om->expects($this->once())
            ->method('flush');

        $this->manager->ensureHasScale();
    }

    public function testPersistFramework()
    {
        $competency = new Competency();
        $this->om->expects($this->once())->method('persist')->with($competency);
        $this->om->expects($this->once())->method('flush');
        $this->assertEquals($competency, $this->manager->persistFramework($competency));
    }

    public function testLoadFramework()
    {
        $framework = new Competency();
        $this->competencyRepo->expects($this->once())
            ->method('childrenHierarchy')
            ->with($framework, false, [], true)
            ->willReturn(['FRAMEWORK']);
        $this->assertEquals('FRAMEWORK', $this->manager->loadFramework($framework));
    }

    /**
     * @expectedException LogicException
     */
    public function testEnsureIsRootExpectsARootCompetency()
    {
        $competency = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $competency->expects($this->once())->method('getId')->willReturn(1);
        $competency->expects($this->once())->method('getRoot')->willReturn(2);
        $this->manager->ensureIsRoot($competency);
    }

    public function testEnsureIsRoot()
    {
        $competency = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $competency->expects($this->once())->method('getId')->willReturn(1);
        $competency->expects($this->once())->method('getRoot')->willReturn(1);
        $this->manager->ensureIsRoot($competency);
    }

    public function testDeleteCompetency()
    {
        $competency = new Competency();
        $this->om->expects($this->once())->method('remove')->with($competency);
        $this->om->expects($this->once())->method('flush');
        $this->manager->deleteCompetency($competency);
    }

    public function testCreateSubCompetency()
    {
        $parent = new Competency();
        $child = $this->mock('HeVinci\CompetencyBundle\Entity\Competency');
        $child->expects($this->once())->method('setParent')->with($parent);
        $this->om->expects($this->once())->method('persist')->with($child);
        $this->om->expects($this->once())->method('flush');
        $this->manager->createSubCompetency($parent, $child);
    }

    public function testUpdateCompetency()
    {
        $competency = new Competency();
        $this->om->expects($this->once())->method('flush');
        $this->assertEquals($competency, $this->manager->updateCompetency($competency));
    }
}

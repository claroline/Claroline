<?php

namespace HeVinci\CompetencyBundle\Manager;

use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Util\UnitTestCase;

class CompetencyManagerTest extends UnitTestCase
{
    private $om;
    private $translator;
    private $converter;
    private $competencyRepo;
    private $scaleRepo;
    private $abilityRepo;
    private $competencyAbilityRepo;
    private $manager;

    protected function setUp(): void
    {
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->translator = $this->mock('Symfony\Component\Translation\TranslatorInterface');
        $this->converter = $this->mock('HeVinci\CompetencyBundle\Transfer\Converter');
        $this->competencyRepo = $this->mock('HeVinci\CompetencyBundle\Repository\CompetencyRepository');
        $this->scaleRepo = $this->mock('HeVinci\CompetencyBundle\Repository\ScaleRepository');
        $this->abilityRepo = $this->mock('HeVinci\CompetencyBundle\Repository\AbilityRepository');
        $this->competencyAbilityRepo = $this->mock('HeVinci\CompetencyBundle\Repository\CompetencyAbilityRepository');
        $this->om->expects($this->exactly(5))
            ->method('getRepository')
            ->withConsecutive(
                ['HeVinciCompetencyBundle:Competency'],
                ['HeVinciCompetencyBundle:Scale'],
                ['HeVinciCompetencyBundle:Ability'],
                ['HeVinciCompetencyBundle:CompetencyAbility']
            )
            ->willReturnOnConsecutiveCalls(
                $this->competencyRepo,
                $this->scaleRepo,
                $this->abilityRepo,
                $this->competencyAbilityRepo
            );
        $this->manager = new CompetencyManager($this->om, $this->translator, $this->converter);
    }

    public function testListFrameworks()
    {
        $this->competencyRepo->expects($this->once())
            ->method('findBy')
            ->with(['parent' => null])
            ->willReturn(['foo']);
        $this->assertEquals(['foo'], $this->manager->listFrameworks());
    }

    public function testListFrameworksAsShortArrays()
    {
        $c1 = new Competency();
        $c1->setName('Foo');
        $c1->setDescription('Foo desc');
        $c2 = new Competency();
        $c2->setName('Bar');
        $c2->setDescription('Bar desc');

        $this->competencyRepo->expects($this->once())
            ->method('findBy')
            ->with(['parent' => null])
            ->willReturn([$c1, $c2]);
        $expected = [
            ['id' => null, 'name' => 'Foo', 'description' => 'Foo desc'],
            ['id' => null, 'name' => 'Bar', 'description' => 'Bar desc'],
        ];
        $this->assertEquals($expected, $this->manager->listFrameworks(true));
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

    /**
     * @dataProvider loadCompetencyProvider
     *
     * @param array $competencies
     * @param array $abilities
     * @param array $expectedResult
     */
    public function testLoadCompetency(array $competencies, array $abilities, array $expectedResult)
    {
        $framework = new Competency();
        $this->competencyRepo->expects($this->once())
            ->method('childrenHierarchy')
            ->with($framework, false, [], true)
            ->willReturn($competencies);
        $this->abilityRepo->expects($this->once())
            ->method('findByCompetency')
            ->with($framework)
            ->willReturn($abilities);
        $this->assertEquals($expectedResult, $this->manager->loadCompetency($framework));
    }

    /**
     * @expectedException \LogicException
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

    public function testLoadAbility()
    {
        $ability = new Ability();
        $level = new Level();
        $parent = new Competency();
        $link = new CompetencyAbility();
        $link->setLevel($level);
        $this->competencyAbilityRepo->expects($this->once())
            ->method('findOneByTerms')
            ->with($parent, $ability)
            ->willReturn($link);
        $this->manager->loadAbility($parent, $ability);
        $this->assertEquals($level, $ability->getLevel());
    }

    public function loadCompetencyProvider()
    {
        return [
            [[[]], [], []],
            [
                [[
                    'id' => 1,
                    'name' => 'C1',
                ]],
                [],
                [
                    'id' => 1,
                    'name' => 'C1',
                ],
            ],
            [
                [[
                    'id' => 1,
                    'name' => 'C1',
                    '__children' => [
                        [
                            'id' => 2,
                            'name' => 'C2',
                        ],
                    ],
                ]],
                [],
                [
                    'id' => 1,
                    'name' => 'C1',
                    '__children' => [
                        [
                            'id' => 2,
                            'name' => 'C2',
                        ],
                    ],
                ],
            ],
            [
                [[
                    'id' => 1,
                    'name' => 'C1',
                    '__children' => [
                        [
                            'id' => 2,
                            'name' => 'C2',
                        ],
                    ],
                ]],
                [
                    [
                        'id' => 3,
                        'name' => 'A1',
                        'competencyId' => 2,
                    ],
                ],
                [
                    'id' => 1,
                    'name' => 'C1',
                    '__children' => [
                        [
                            'id' => 2,
                            'name' => 'C2',
                            '__abilities' => [
                                [
                                    'id' => 3,
                                    'name' => 'A1',
                                    'competencyId' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                [[
                    'id' => 1,
                    'name' => 'C1',
                    '__children' => [
                        [
                            'id' => 2,
                            'name' => 'C2',
                        ],
                    ],
                ]],
                [
                    [
                        'id' => 3,
                        'name' => 'A1',
                        'competencyId' => 2,
                    ],
                    [
                        'id' => 4,
                        'name' => 'A2',
                        'competencyId' => 2,
                    ],
                ],
                [
                    'id' => 1,
                    'name' => 'C1',
                    '__children' => [
                        [
                            'id' => 2,
                            'name' => 'C2',
                            '__abilities' => [
                                [
                                    'id' => 3,
                                    'name' => 'A1',
                                    'competencyId' => 2,
                                ],
                                [
                                    'id' => 4,
                                    'name' => 'A2',
                                    'competencyId' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

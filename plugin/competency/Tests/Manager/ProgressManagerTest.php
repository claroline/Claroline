<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ProgressManagerTest extends RepositoryTestCase
{
    private $manager;
    private $abilityProgressRepo;
    private $competencyProgressRepo;
    private $competencyProgressLogRepo;
    private $objectiveProgressRepo;
    private $objectiveProgressLogRepo;
    private $userProgressRepo;
    private $userProgressLogRepo;
    private $testUser;
    private $testData;

    protected function setUp()
    {
        parent::setUp();
        $this->manager = $this->client->getContainer()->get('hevinci.competency.progress_manager');
        $this->abilityProgressRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\AbilityProgress');
        $this->competencyProgressRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgress');
        $this->competencyProgressLogRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgressLog');
        $this->objectiveProgressRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\ObjectiveProgress');
        $this->objectiveProgressLogRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\ObjectiveProgressLog');
        $this->userProgressRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\UserProgress');
        $this->userProgressLogRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\UserProgressLog');
        $this->testUser = $this->persistUser('jdoe');
        $this->testData = [];
    }

    public function testHandleEvaluationTracksAbilityProgress()
    {
        $this->createFramework(['c1' => [
            'a1' => [
                'level' => 'l1',
                'resources' => ['ac1'],
            ],
        ]]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $summaries = $this->abilityProgressRepo->findBy(['user' => $this->testUser]);
        $this->assertEquals(1, count($summaries));
        $this->assertEquals('a1', $summaries[0]->getAbility()->getName());
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $summaries[0]->getStatus());
    }

    public function testHandleEvaluationSetsAbilityProgressToPendingIfUnderResourceCount()
    {
        $this->createFramework(['c1' => [
            'a1' => [
                'level' => 'l1',
                'resources' => ['ac1', 'ac2'],
                'required' => 2,
            ],
        ]]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $summaries = $this->abilityProgressRepo->findBy(['user' => $this->testUser]);
        $this->assertEquals(1, count($summaries));
        $this->assertEquals('a1', $summaries[0]->getAbility()->getName());
        $this->assertEquals(AbilityProgress::STATUS_PENDING, $summaries[0]->getStatus());
    }

    public function testHandleEvaluationUpdatesPendingAbilityRecordIfAny()
    {
        $this->createFramework(['c1' => [
            'a1' => [
                'level' => 'l1',
                'resources' => ['ac1', 'ac2'],
                'required' => 2,
            ],
        ]]);

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED, null, false);
        $eval2 = $this->createEvaluation('ac2', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());

        $summaries = $this->abilityProgressRepo->findBy(['user' => $this->testUser]);
        $this->assertEquals(1, count($summaries));
        $this->assertEquals('a1', $summaries[0]->getAbility()->getName());
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $summaries[0]->getStatus());
    }

    public function testHandleEvaluationCreatesAnAbilityRecordForEachAbility()
    {
        $this->createFramework(['c1' => [
            'a1' => [
                'level' => 'l1',
                'resources' => ['ac1'],
            ],
            'a2' => [
                'level' => 'l2',
                'resources' => ['ac1'],
            ],
        ]]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $summaries = $this->abilityProgressRepo->findBy(['user' => $this->testUser]);
        $this->assertEquals(2, count($summaries));
        $this->assertEquals('a1', $summaries[0]->getAbility()->getName());
        $this->assertEquals('a2', $summaries[1]->getAbility()->getName());
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $summaries[0]->getStatus());
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $summaries[1]->getStatus());
    }

    public function testHandleEvaluationTracksDirectCompetencyProgress()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
        ]]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $summaries = $this->competencyProgressRepo->findBy([
            'user' => $this->testUser,
            'competency' => $this->testData['competencies']['c2'],
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals('c2', $summaries[0]->getCompetency()->getName());
        $this->assertEquals('l1', $summaries[0]->getLevel()->getName());
        $this->assertEquals(100, $summaries[0]->getPercentage());
    }

    public function testHandleEvaluationDoesNotDecreaseCompetencyProgress()
    {
        $this->createFramework(['c1' => [
            'a1' => [
                'level' => 'l1',
                'resources' => ['ac1'],
            ],
            'a2' => [
                'level' => 'l2',
                'resources' => ['ac2'],
            ],
        ]]);

        $getSummary = function () {
            return $this->competencyProgressRepo->findOneBy([
                'user' => $this->testUser,
                'competency' => $this->testData['competencies']['c1'],
            ]);
        };

        $eval1 = $this->createEvaluation('ac2', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->assertEquals('l2', $getSummary()->getLevel()->getName());

        $eval2 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());
        $this->assertEquals('l2', $getSummary()->getLevel()->getName());
    }

    public function testHandleEvaluationRequiresAllSameLevelAbilitiesToBeCompleted()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
                'a2' => [
                    'level' => 'l2',
                    'resources' => ['ac2'],
                ],
                'a3' => [
                    'level' => 'l2',
                    'resources' => ['ac3'],
                ],
            ],
        ]]);

        $getSummary = function () {
            return $this->competencyProgressRepo->findOneBy([
                'user' => $this->testUser,
                'competency' => $this->testData['competencies']['c2'],
            ]);
        };

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->assertEquals('l1', $getSummary()->getLevel()->getName());

        $eval2 = $this->createEvaluation('ac2', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());
        $this->assertEquals('l1', $getSummary()->getLevel()->getName());

        $eval3 = $this->createEvaluation('ac3', AbstractResourceEvaluation::STATUS_COMPLETED);
        $this->manager->handleEvaluation($eval3->getResourceUserEvaluation());
        $this->assertEquals('l2', $getSummary()->getLevel()->getName());
    }

    public function testHandleEvaluationKeepsCompetencyProgressHistoryLogs()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
                'a2' => [
                    'level' => 'l2',
                    'resources' => ['ac2'],
                ],
            ],
        ]]);

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED, null, false);
        $eval2 = $this->createEvaluation('ac2', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());

        $competency = $this->testData['competencies']['c2'];
        $summaries = $this->competencyProgressRepo->findBy([
            'user' => $this->testUser,
            'competency' => $competency,
        ]);
        $logs = $this->competencyProgressLogRepo->findBy([
            'user' => $this->testUser,
            'competency' => $competency,
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals('c2', $summaries[0]->getCompetency()->getName());
        $this->assertEquals('l2', $summaries[0]->getLevel()->getName());
        $this->assertEquals(1, count($logs));
        $this->assertEquals('c2', $logs[0]->getCompetency()->getName());
        $this->assertEquals('l1', $logs[0]->getLevel()->getName());
    }

    public function testHandleEvaluationComputesParentCompetenciesProgress()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
            'c3' => [
                'c4' => [
                    'a2' => [
                        'level' => 'l1',
                        'resources' => ['ac2'],
                    ],
                ],
                'c5' => [
                    'a3' => [
                        'level' => 'l1',
                        'resources' => ['ac3'],
                    ],
                    'a4' => [
                        'level' => 'l2',
                        'resources' => ['ac4'],
                    ],
                ],
            ],
        ]]);

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED, null, false);
        $eval2 = $this->createEvaluation('ac4', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());

        $summaries = $this->competencyProgressRepo->findBy(['user' => $this->testUser]);
        $this->assertEquals(5, count($summaries));
        $this->assertHasProgressLog($summaries, 'c2', 100, 'l1');
        $this->assertHasProgressLog($summaries, 'c5', 100, 'l2');
        $this->assertHasProgressLog($summaries, 'c4', 0, null);
        $this->assertHasProgressLog($summaries, 'c3', 50, 'l2');
        $this->assertHasProgressLog($summaries, 'c1', 75, 'l1');
    }

    public function testHandleEvaluationKeepsParentCompetenciesHistory()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
            'c3' => [
                'a2' => [
                    'level' => 'l1',
                    'resources' => ['ac2'],
                ],
                'a3' => [
                    'level' => 'l2',
                    'resources' => ['ac3'],
                ],
                'a4' => [
                    'level' => 'l3',
                    'resources' => ['ac4'],
                ],
                'a5' => [
                    'level' => 'l4',
                    'resources' => ['ac5'],
                ],
                'a6' => [
                    'level' => 'l5',
                    'resources' => ['ac6'],
                ],
            ],
        ]]);

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED, null, false);
        $eval2 = $this->createEvaluation('ac6', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());

        $competency = $this->testData['competencies']['c1'];
        $summaries = $this->competencyProgressRepo->findBy([
            'user' => $this->testUser,
            'competency' => $competency,
        ]);
        $logs = $this->competencyProgressLogRepo->findBy([
            'user' => $this->testUser,
            'competency' => $competency,
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals(100, $summaries[0]->getPercentage());
        $this->assertEquals('l3', $summaries[0]->getLevel()->getName());
        $this->assertEquals(1, count($logs));
        $this->assertEquals(50, $logs[0]->getPercentage());
        $this->assertEquals('l1', $logs[0]->getLevel()->getName());
    }

    public function testHandleEvaluationTracksObjectivesProgress()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
            'c3' => [
                'c4' => [
                    'a2' => [
                        'level' => 'l1',
                        'resources' => ['ac2'],
                    ],
                ],
                'c5' => [
                    'a2' => [
                        'level' => 'l2',
                        'resources' => ['ac1'],
                    ],
                ],
            ],
        ]]);

        $this->createObjectives('c1', [
           'o1' => [
               ['c2', 'l1'],
           ],
           'o2' => [
               ['c2', 'l1'],
               ['c3', 'l2'],
           ],
        ]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $o1Summaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->testUser,
            'objective' => $this->testData['objectives']['o1'],
        ]);
        $o2Summaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->testUser,
            'objective' => $this->testData['objectives']['o2'],
        ]);

        $this->assertEquals(1, count($o1Summaries));
        $this->assertEquals(100, $o1Summaries[0]->getPercentage());
        $this->assertEquals(1, count($o2Summaries));
        $this->assertEquals(50, $o2Summaries[0]->getPercentage());
    }

    public function testHandleEvaluationDoesNotComputeObjectivesForIncompleteCompetencies()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
            'c3' => [
                'a2' => [
                    'level' => 'l1',
                    'resources' => ['ac2'],
                ],
            ],
        ]]);

        $this->createObjectives('c1', [
           'o1' => [
               ['c1', 'l1'],
           ],
        ]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $competencySummaries = $this->competencyProgressRepo->findBy([
            'user' => $this->testUser,
            'competency' => $this->testData['competencies']['c1'],
        ]);
        $objectiveSummaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->testUser,
            'objective' => $this->testData['objectives']['o1'],
        ]);

        $this->assertEquals(1, count($competencySummaries));
        $this->assertEquals(50, $competencySummaries[0]->getPercentage());
        $this->assertEquals(0, count($objectiveSummaries));
    }

    public function testHandleEvaluationDoesNotComputeObjectivesForInsufficientCompetencies()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
                'a2' => [
                    'level' => 'l2',
                    'resources' => ['ac2'],
                ],
            ],
        ]]);

        $this->createObjectives('c1', [
           'o1' => [
               ['c1', 'l2'],
           ],
        ]);

        $eval = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval->getResourceUserEvaluation());

        $competencySummaries = $this->competencyProgressRepo->findBy([
            'user' => $this->testUser,
            'competency' => $this->testData['competencies']['c2'],
        ]);
        $objectiveSummaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->testUser,
            'objective' => $this->testData['objectives']['o1'],
        ]);

        $this->assertEquals(1, count($competencySummaries));
        $this->assertEquals('l1', $competencySummaries[0]->getLevel()->getName());
        $this->assertEquals(0, count($objectiveSummaries));
    }

    public function testHandleEvaluationKeepsObjectivesProgressHistory()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
            'c3' => [
                'a2' => [
                    'level' => 'l1',
                    'resources' => ['ac2'],
                ],
            ],
        ]]);

        $this->createObjectives('c1', [
           'o1' => [
               ['c2', 'l1'],
               ['c3', 'l1'],
           ],
        ]);

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED, null, false);
        $eval2 = $this->createEvaluation('ac2', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());

        $summaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->testUser,
            'objective' => $this->testData['objectives']['o1'],
        ]);
        $logs = $this->objectiveProgressLogRepo->findBy([
            'user' => $this->testUser,
            'objective' => $this->testData['objectives']['o1'],
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals(100, $summaries[0]->getPercentage());
        $this->assertEquals(1, count($logs));
        $this->assertEquals(50, $logs[0]->getPercentage());
    }

    public function testHandleEvaluationTracksUserProgress()
    {
        $this->createFramework(['c1' => [
            'c2' => [
                'a1' => [
                    'level' => 'l1',
                    'resources' => ['ac1'],
                ],
            ],
            'c3' => [
                'c4' => [
                    'a2' => [
                        'level' => 'l1',
                        'resources' => ['ac2'],
                    ],
                ],
                'c5' => [
                    'a3' => [
                        'level' => 'l1',
                        'resources' => ['ac3'],
                    ],
                ],
                'c6' => [
                    'a4' => [
                        'level' => 'l1',
                        'resources' => ['ac4'],
                    ],
                ],
            ],
        ]]);

        $this->createObjectives('c1', [
            'o1' => [
                ['c2', 'l1'],
            ],
            'o2' => [
                ['c4', 'l1'],
                ['c5', 'l1'],
                ['c6', 'l1'],
            ],
        ]);

        $eval1 = $this->createEvaluation('ac1', AbstractResourceEvaluation::STATUS_PASSED, null, false);
        $eval2 = $this->createEvaluation('ac2', AbstractResourceEvaluation::STATUS_PASSED);
        $this->manager->handleEvaluation($eval1->getResourceUserEvaluation());
        $this->manager->handleEvaluation($eval2->getResourceUserEvaluation());

        $summaries = $this->userProgressRepo->findBy(['user' => $this->testUser]);
        $logs = $this->userProgressLogRepo->findBy(['user' => $this->testUser]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals(66, $summaries[0]->getPercentage());
        $this->assertEquals(1, count($logs));
        $this->assertEquals(50, $logs[0]->getPercentage());
    }

    /**
     * Creates a competency framework from an array for testing purposes.
     *
     * For example, the following array:
     *
     * [
     *   'c1' => [
     *     'c2' => [
     *       'a1' => [
     *         'level' => 'l1'
     *         'resources' => ['ac1']
     *       ]
     *     ],
     *     'c3' => [
     *       'a2' => [
     *         'level' => 'l1'
     *         'resources' => ['ac2']
     *       ],
     *       'a3' => [
     *         'level' => 'l2'
     *         'resources' => ['ac3', 'ac4'],
     *         'required' => 2
     *       ]
     *     ],
     *   ]
     * ]
     *
     * will produce a framework called "c1", containing two competencies,
     * "c2" and "c3". The first competency will contain one ability, "a1",
     * of level "l1", linked with one resource "ac1". The second competency
     * will contain two abilities "a2" and "a3", and so on.
     *
     * Note that:
     *
     * 1) All these objects are created from scratch.
     * 2) A default scale is created and associated with the framework.
     * 3) Scale levels are persisted as encountered in the array structure
     *    (beware of the order).
     * 4) Competency keys MUST match the "/^c\d+$/" pattern.
     * 5) The required number of resources for an ability defaults to 1
     *    but can be changed using the "required" key.
     *
     * @param array $framework
     */
    private function createFramework(array $framework)
    {
        $rootName = array_keys($framework)[0];
        $scale = $this->persistScale('s1');
        $root = $this->persistCompetency($rootName);
        $root->setScale($scale);
        $levelIndex = 0;

        $walkNodes = function ($parent, $nodes) use (&$walkNodes, $scale, &$levelIndex) {
            $subNodes = array_keys($nodes);

            foreach ($subNodes as $nodeName) {
                if (preg_match('#^(c\d+)$#', $nodeName)) {
                    $this->testData['competencies'][$nodeName] = $this->persistCompetency($nodeName, $parent);
                    $walkNodes($this->testData['competencies'][$nodeName], $nodes[$nodeName]);
                } else {
                    $required = isset($nodes[$nodeName]['required']) ?
                        $nodes[$nodeName]['required'] :
                        1;

                    if (!isset($this->testData['abilities'][$nodeName])) {
                        $this->testData['abilities'][$nodeName] = $this->persistAbility($nodeName, $required);
                    }

                    if (!isset($this->testData['levels'][$nodes[$nodeName]['level']])) {
                        $levelName = $nodes[$nodeName]['level'];
                        $this->testData['levels'][$levelName] = $this->persistLevel($levelName, $scale, $levelIndex);
                        ++$levelIndex;
                    }

                    $this->persistLink(
                        $parent,
                        $this->testData['abilities'][$nodeName],
                        $this->testData['levels'][$nodes[$nodeName]['level']]
                    );

                    foreach ($nodes[$nodeName]['resources'] as $resourceName) {
                        if (!isset($this->testData['resources'][$resourceName])) {
                            $this->testData['resources'][$resourceName] = $this->persistResource($resourceName);
                        }

                        $this->testData['abilities'][$nodeName]->linkResource($this->testData['resources'][$resourceName]);
                    }
                }
            }
        };

        $this->testData['competencies'][$rootName] = $root;
        $walkNodes($root, $framework[$rootName]);

        $this->om->flush();
    }

    /**
     * Creates learning objectives for testing purposes.
     *
     * For example, the following array of objectives:
     *
     * [
     *   'o1' => [
     *     ['c1', 'l1']
     *   ],
     *   'o2' => [
     *     ['c2', 'l1'],
     *     ['c3', 'l2']
     *   ]
     * ]
     *
     * will create two objectives "o1" and "o2". The first objective
     * is to attain level "l1" of competency "c1". The second one,
     * level "l1" of competency "c2" and level "l2" of competency "c3".
     *
     * Note that:
     *
     * 1) the competency and level objects MUST have been created with
     *    the "createFramework" method.
     * 2) objectives are automatically assigned to the test user created
     *    in the "setUp" method.
     *
     * @param string $frameworkName
     * @param array  $objectives
     */
    private function createObjectives($frameworkName, array $objectives)
    {
        foreach ($objectives as $name => $competencies) {
            $competencyData = array_map(function ($competency) use ($frameworkName) {
                return [
                    $this->testData['competencies'][$competency[0]],
                    $this->testData['competencies'][$frameworkName],
                    $this->testData['levels'][$competency[1]],
                ];
            }, $competencies);

            $this->testData['objectives'][$name] = $this->persistObjective($name, $competencyData);
            $this->testData['objectives'][$name]->addUser($this->testUser);
        }

        $this->om->flush();
    }

    /**
     * Creates an evaluation for a resource. The resource MUST have been
     * created using the "createFramework" method.
     *
     * @param string     $resourceName
     * @param string     $status
     * @param Evaluation $previous
     * @param bool       $flush
     *
     * @return Evaluation
     */
    private function createEvaluation($resourceName, $status, Evaluation $previous = null, $flush = true)
    {
        $evaluation = $this->persistEvaluation(
            $this->testData['resources'][$resourceName],
            $this->testUser,
            $status,
            $previous
        );

        if ($flush) {
            $this->om->flush();
        }

        return $evaluation;
    }

    private function assertHasProgressLog(array $logs, $competencyName, $percentage, $level = null)
    {
        $targetLog = null;

        foreach ($logs as $log) {
            if ($log->getCompetency()->getName() === $competencyName && $log->getUser() === $this->testUser) {
                $targetLog = $log;
                break;
            }
        }

        if (!$targetLog) {
            // make the assertion fail (hacky...)
            $this->assertTrue(false, 'No progress log matches the given criteria.');
        }

        $this->assertEquals($percentage, $targetLog->getPercentage());

        if ($level === null) {
            // "assertNull" dumps the whole entity if the assertion fails...
            $this->assertEquals(
                'NULL',
                gettype($targetLog->getLevel()),
                sprintf(
                    'Level was supposed to be null, "%s" received',
                    $targetLog->getLevel() ? $targetLog->getLevel()->getName() : 'null'
                )
            );
        } else {
            $this->assertEquals($level, $targetLog->getLevel()->getName());
        }
    }
}

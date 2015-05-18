<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Activity\Evaluation;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
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
    private $user;
    private $framework;

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
        $this->user = $this->persistUser('jdoe');
        $this->framework = $this->persistFramework();
    }

    public function testHandleEvaluationTracksAbilityProgress()
    {
        $eval = $this->makeEvaluation('ac3', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $summaries = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($summaries));
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $summaries[0]->getStatus());
    }

    public function testHandleEvaluationSetsAbilityProgressToPendingIfUnderActivityCount()
    {
        $eval = $this->makeEvaluation('ac5', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $summaries = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($summaries));
        $this->assertEquals(AbilityProgress::STATUS_PENDING, $summaries[0]->getStatus());
    }

    public function testHandleEvaluationUpdatesPendingAbilityRecordIfAny()
    {
        $eval1 = $this->makeEvaluation('ac5', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac6', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $records = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($records));
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $records[0]->getStatus());
    }

    public function testHandleEvaluationCreatesAnAbilityRecordForEachAbility()
    {
        $eval = $this->makeEvaluation('ac1', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $records = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(2, count($records));
        $this->assertEquals($this->framework['abilities']['a1'], $records[0]->getAbility());
        $this->assertEquals($this->framework['abilities']['a5'], $records[1]->getAbility());
    }

    public function testHandleEvaluationTracksDirectCompetencyProgress()
    {
        $eval = $this->makeEvaluation('ac8', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $competency = $this->framework['competencies']['c6'];
        $summaries = $this->competencyProgressRepo->findBy([
            'user' => $this->user,
            'competency' => $competency
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals($competency, $summaries[0]->getCompetency());
        $this->assertEquals($this->framework['levels']['l1'], $summaries[0]->getLevel());
        $this->assertEquals(100, $summaries[0]->getPercentage());
    }

    public function testHandleEvaluationKeepsCompetencyProgressHistoryLogs()
    {
        $eval1 = $this->makeEvaluation('ac15', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac16', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $competency = $this->framework['competencies']['c9'];
        $summaries = $this->competencyProgressRepo->findBy([
            'user' => $this->user,
            'competency' => $competency]
        );
        $logs = $this->competencyProgressLogRepo->findBy([
            'user' => $this->user,
            'competency' => $competency
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals($this->framework['competencies']['c9'], $summaries[0]->getCompetency());
        $this->assertEquals($this->framework['levels']['l2'], $summaries[0]->getLevel());
        $this->assertEquals(1, count($logs));
        $this->assertEquals($this->framework['competencies']['c9'], $logs[0]->getCompetency());
        $this->assertEquals($this->framework['levels']['l1'], $logs[0]->getLevel());
    }

    public function testHandleEvaluationComputesParentCompetenciesProgress()
    {
        $eval1 = $this->makeEvaluation('ac15', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac17', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $summaries = $this->competencyProgressRepo->findBy(['user' => $this->user]);
        $comps = $this->framework['competencies'];
        $levels = $this->framework['levels'];

        $this->assertEquals(8, count($summaries));
        $this->assertHasProgressLog($summaries, $comps['c10'], 100, $levels['l3']);
        $this->assertHasProgressLog($summaries, $comps['c9'], 100, $levels['l1']);
        $this->assertHasProgressLog($summaries, $comps['c8'], 0, null);
        $this->assertHasProgressLog($summaries, $comps['c7'], 0, null);
        $this->assertHasProgressLog($summaries, $comps['c6'], 0, null);
        $this->assertHasProgressLog($summaries, $comps['c3'], 40, $levels['l2']);
        $this->assertHasProgressLog($summaries, $comps['c2'], 0, null);
        $this->assertHasProgressLog($summaries, $comps['c1'], 20, $levels['l2']);
    }

    public function testHandleEvaluationKeepsParentCompetenciesHistory()
    {
        $eval1 = $this->makeEvaluation('ac15', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac16', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $summaries = $this->competencyProgressRepo->findBy([
            'user' => $this->user,
            'competency' => $this->framework['competencies']['c1']
        ]);
        $logs = $this->competencyProgressLogRepo->findBy([
            'user' => $this->user,
            'competency' => $this->framework['competencies']['c1']
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals($this->framework['levels']['l2'], $summaries[0]->getLevel());
        $this->assertEquals(1, count($logs));
        $this->assertEquals($this->framework['levels']['l1'], $logs[0]->getLevel());
    }

    public function testHandleEvaluationTracksObjectivesProgress()
    {
        $eval = $this->makeEvaluation('ac17', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $o1Summaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->user,
            'objective' => $this->framework['objectives']['o1']
        ]);
        $o2Summaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->user,
            'objective' => $this->framework['objectives']['o2']
        ]);

        $this->assertEquals(1, count($o1Summaries));
        $this->assertEquals(100, $o1Summaries[0]->getPercentage());
        $this->assertEquals(1, count($o2Summaries));
        $this->assertEquals(50, $o2Summaries[0]->getPercentage());
    }

    public function testHandleEvaluationDoesNotComputeObjectivesForIncompleteCompetencies()
    {
        $eval = $this->makeEvaluation('ac4', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $competencySummaries = $this->competencyProgressRepo->findBy([
            'user' => $this->user,
            'competency' => $this->framework['competencies']['c2']
        ]);
        $objectiveSummaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->user,
            'objective' => $this->framework['objectives']['o3']
        ]);

        $this->assertEquals(1, count($competencySummaries));
        $this->assertEquals(50, $competencySummaries[0]->getPercentage());
        $this->assertEquals(0, count($objectiveSummaries));
    }

    public function testHandleEvaluationDoesNotComputeObjectivesForInsufficientCompetencies()
    {
        $eval = $this->makeEvaluation('ac15', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $competencySummaries = $this->competencyProgressRepo->findBy([
            'user' => $this->user,
            'competency' => $this->framework['competencies']['c9']
        ]);
        $objectiveSummaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->user,
            'objective' => $this->framework['objectives']['o2']
        ]);

        $this->assertEquals(1, count($competencySummaries));
        $this->assertEquals($this->framework['levels']['l1'], $competencySummaries[0]->getLevel());
        $this->assertEquals(0, count($objectiveSummaries));
    }

    public function testHandleEvaluationKeepsObjectivesProgressHistory()
    {
        $eval1 = $this->makeEvaluation('ac16', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac17', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $summaries = $this->objectiveProgressRepo->findBy([
            'user' => $this->user,
            'objective' => $this->framework['objectives']['o2']
        ]);
        $logs = $this->objectiveProgressLogRepo->findBy([
            'user' => $this->user,
            'objective' => $this->framework['objectives']['o2']
        ]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals(100, $summaries[0]->getPercentage());
        $this->assertEquals(1, count($logs));
        $this->assertEquals(50, $logs[0]->getPercentage());
    }

    public function testHandleEvaluationTracksUserProgress()
    {
        $eval1 = $this->makeEvaluation('ac17', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac16', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $summaries = $this->userProgressRepo->findBy(['user' => $this->user]);
        $logs = $this->userProgressLogRepo->findBy(['user' => $this->user]);

        $this->assertEquals(1, count($summaries));
        $this->assertEquals(66, $summaries[0]->getPercentage());
        $this->assertEquals(1, count($logs));
        $this->assertEquals(50, $logs[0]->getPercentage());
    }

    private function persistFramework()
    {
        // Framework:
        //
        // c1
        //  - c2
        //    - c4
        //      - a1 (l1)
        //        - ac1
        //        - ac2
        //      - a2 (l2)
        //        - ac3
        //      - a3 (l3)
        //        - ac4
        //    - c5
        //      - a4 (l1) * req 2
        //        - ac5
        //        - ac6
        //      - a5 (l2)
        //        - ac1
        //      - a6 (l3)
        //        - ac7
        //  - c3
        //    - c6
        //      - a7 (l1))
        //        - ac8
        //      - a8 (l2)
        //        - ac9
        //      - a9 (l3)
        //        - ac10
        //        - ac11
        //    - c7
        //      - a3 (l1)
        //        - ac12
        //      - a2 (l2)
        //        - ac13
        //      - a1 (l3)
        //        - ac14
        //    - c8
        //      - a10 (l1)
        //        - ac7
        //        - ac9
        //      - a11 (l2)
        //        - ac10
        //      - a12 (l3)
        //        - ac11
        //    - c9
        //      - a13
        //        - ac15 (l1)
        //      - a14
        //        - ac16 (l2)
        //    - c10
        //      - a15
        //        - ac17 (l3)
        //
        // Objectives:
        //
        // - o1
        //   - c10 (l3)
        // - o2
        //   - c9 (l2)
        //   - c10 (l3)
        // - o3
        //   - c2 (l2)

        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2', $c1);
        $c3 = $this->persistCompetency('c3', $c1);
        $c4 = $this->persistCompetency('c4', $c2);
        $c5 = $this->persistCompetency('c5', $c2);
        $c6 = $this->persistCompetency('c6', $c3);
        $c7 = $this->persistCompetency('c7', $c3);
        $c8 = $this->persistCompetency('c8', $c3);
        $c9 = $this->persistCompetency('c9', $c3);
        $c10 = $this->persistCompetency('c10', $c3);

        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $a4 = $this->persistAbility('a4', 2);
        $a5 = $this->persistAbility('a5');
        $a6 = $this->persistAbility('a6');
        $a7 = $this->persistAbility('a7');
        $a8 = $this->persistAbility('a8');
        $a9 = $this->persistAbility('a9');
        $a10 = $this->persistAbility('a10');
        $a11 = $this->persistAbility('a11');
        $a12 = $this->persistAbility('a12');
        $a13 = $this->persistAbility('a13');
        $a14 = $this->persistAbility('a14');
        $a15 = $this->persistAbility('a15');

        $ac1 = $this->persistActivity('ac1');
        $ac2 = $this->persistActivity('ac2');
        $ac3 = $this->persistActivity('ac3');
        $ac4 = $this->persistActivity('ac4');
        $ac5 = $this->persistActivity('ac5');
        $ac6 = $this->persistActivity('ac6');
        $ac7 = $this->persistActivity('ac7');
        $ac8 = $this->persistActivity('ac8');
        $ac9 = $this->persistActivity('ac9');
        $ac10 = $this->persistActivity('ac10');
        $ac11 = $this->persistActivity('ac11');
        $ac12 = $this->persistActivity('ac12');
        $ac13 = $this->persistActivity('ac13');
        $ac14 = $this->persistActivity('ac14');
        $ac15 = $this->persistActivity('ac15');
        $ac16 = $this->persistActivity('ac16');
        $ac17 = $this->persistActivity('ac17');

        $s = $this->persistScale('s');
        $l1 = $this->persistLevel('l1', $s, 0);
        $l2 = $this->persistLevel('l2', $s, 1);
        $l3 = $this->persistLevel('l3', $s, 2);

        $c1->setScale($s);

        $this->persistLink($c4, $a1, $l1);
        $this->persistLink($c4, $a2, $l2);
        $this->persistLink($c4, $a3, $l3);
        $this->persistLink($c5, $a4, $l1);
        $this->persistLink($c5, $a5, $l2);
        $this->persistLink($c5, $a6, $l3);
        $this->persistLink($c6, $a7, $l1);
        $this->persistLink($c6, $a8, $l2);
        $this->persistLink($c6, $a9, $l3);
        $this->persistLink($c7, $a3, $l1);
        $this->persistLink($c7, $a2, $l2);
        $this->persistLink($c7, $a1, $l3);
        $this->persistLink($c8, $a10, $l1);
        $this->persistLink($c8, $a11, $l2);
        $this->persistLink($c8, $a12, $l3);
        $this->persistLink($c9, $a13, $l1);
        $this->persistLink($c9, $a14, $l2);
        $this->persistLink($c10, $a15, $l3);

        $a1->linkActivity($ac1);
        $a1->linkActivity($ac2);
        $a2->linkActivity($ac3);
        $a3->linkActivity($ac4);
        $a4->linkActivity($ac5);
        $a4->linkActivity($ac6);
        $a5->linkActivity($ac1);
        $a6->linkActivity($ac7);
        $a7->linkActivity($ac8);
        $a8->linkActivity($ac9);
        $a9->linkActivity($ac10);
        $a9->linkActivity($ac11);
        $a3->linkActivity($ac12);
        $a2->linkActivity($ac13);
        $a1->linkActivity($ac14);
        $a10->linkActivity($ac7);
        $a10->linkActivity($ac9);
        $a11->linkActivity($ac10);
        $a12->linkActivity($ac11);
        $a13->linkActivity($ac15);
        $a14->linkActivity($ac16);
        $a15->linkActivity($ac17);

        $o1 = $this->persistObjective('o1', [
            [$c10, $c1, $l3]
        ]);
        $o2 = $this->persistObjective('o2', [
            [$c9, $c1, $l2],
            [$c10, $c1, $l3]
        ]);
        $o3 = $this->persistObjective('o3', [
            [$c2, $c1, $l2]
        ]);

        $o1->addUser($this->user);
        $o2->addUser($this->user);
        $o3->addUser($this->user);

        return [
            'competencies' => [
                'c1' => $c1,
                'c2' => $c2,
                'c3' => $c3,
                'c4' => $c4,
                'c5' => $c5,
                'c6' => $c6,
                'c7' => $c7,
                'c8' => $c8,
                'c9' => $c9,
                'c10' => $c10
            ],
            'abilities' => [
                'a1' => $a1,
                'a2' => $a2,
                'a3' => $a3,
                'a4' => $a4,
                'a5' => $a5,
                'a6' => $a6,
                'a7' => $a7,
                'a8' => $a8,
                'a9' => $a9,
                'a10' => $a10,
                'a11' => $a11,
                'a12' => $a12,
                'a13' => $a13,
                'a14' => $a14,
                'a15' => $a15
            ],
            'activities' => [
                'ac1' => $ac1,
                'ac2' => $ac2,
                'ac3' => $ac3,
                'ac4' => $ac4,
                'ac5' => $ac5,
                'ac6' => $ac6,
                'ac7' => $ac7,
                'ac8' => $ac8,
                'ac9' => $ac9,
                'ac10' => $ac10,
                'ac11' => $ac11,
                'ac12' => $ac12,
                'ac13' => $ac13,
                'ac14' => $ac14,
                'ac15' => $ac15,
                'ac16' => $ac16,
                'ac17' => $ac17
            ],
            'levels' => [
                'l1' => $l1,
                'l2' => $l2,
                'l3' => $l3
            ],
            'objectives' => [
                'o1' => $o1,
                'o2' => $o2,
                'o3' => $o3
            ]
        ];
    }

    private function makeEvaluation($activityName, $status, Evaluation $previous = null)
    {
        return $this->persistEvaluation(
            $this->framework['activities'][$activityName],
            $this->user,
            $status,
            $previous
        );
    }

    private function assertHasProgressLog(array $logs, Competency $competency, $percentage, Level $level = null)
    {
        $targetLog = null;

        foreach ($logs as $log) {
            if ($log->getCompetency() === $competency && $log->getUser() === $this->user) {
                $targetLog = $log;
                break;
            }
        }

        if (!$targetLog) {
            $this->assertTrue(false); // make the assertion fail (hacky...)
        }

        $this->assertEquals($percentage, $targetLog->getPercentage());
        $this->assertEquals($level, $targetLog->getLevel());
    }
}

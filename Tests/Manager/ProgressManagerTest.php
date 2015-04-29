<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;
use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ProgressManagerTest extends RepositoryTestCase
{
    private $manager;
    private $abilityProgressRepo;
    private $competencyProgressRepo;
    private $user;
    private $framework;

    protected function setUp()
    {
        parent::setUp();
        $this->manager = $this->client->getContainer()->get('hevinci.competency.progress_manager');
        $this->abilityProgressRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\AbilityProgress');
        $this->competencyProgressRepo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgress');
        $this->user = $this->persistUser('jdoe');
        $this->framework = $this->persistFramework();
    }

    public function testHandleEvaluationLogsAbilityProgress()
    {
        $eval = $this->makeEvaluation('ac3', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $logs = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($logs));
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $logs[0]->getStatus());
    }

    public function testHandleEvaluationSetsAbilityProgressToPendingIfUnderActivityCount()
    {
        $eval = $this->makeEvaluation('ac5', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $logs = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($logs));
        $this->assertEquals(AbilityProgress::STATUS_PENDING, $logs[0]->getStatus());
    }

    public function testHandleEvaluationUpdatePendingLogIfAny()
    {
        $eval1 = $this->makeEvaluation('ac5', AbstractEvaluation::STATUS_PASSED);
        $eval2 = $this->makeEvaluation('ac6', AbstractEvaluation::STATUS_PASSED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval1);
        $this->manager->handleEvaluation($eval2);

        $logs = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($logs));
        $this->assertEquals(AbilityProgress::STATUS_ACQUIRED, $logs[0]->getStatus());
    }

    public function testHandleEvaluationCreatesAnAbilityLogForEachAbility()
    {
        $eval = $this->makeEvaluation('ac1', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $logs = $this->abilityProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(2, count($logs));
        $this->assertEquals($this->framework['abilities']['a1'], $logs[0]->getAbility());
        $this->assertEquals($this->framework['abilities']['a5'], $logs[1]->getAbility());
    }

    public function testHandleEvaluationLogsDirectCompetencyProgress()
    {
        $eval = $this->makeEvaluation('ac8', AbstractEvaluation::STATUS_COMPLETED);
        $this->om->flush();
        $this->manager->handleEvaluation($eval);

        $logs = $this->competencyProgressRepo->findBy(['user' => $this->user]);
        $this->assertEquals(1, count($logs));
        $this->assertEquals($this->framework['competencies']['c6'], $logs[0]->getCompetency());
        $this->assertEquals($this->framework['levels']['l1'], $logs[0]->getLevel());
    }

    private function persistFramework()
    {
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

        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2', $c1);
        $c3 = $this->persistCompetency('c3', $c1);
        $c4 = $this->persistCompetency('c4', $c2);
        $c5 = $this->persistCompetency('c5', $c2);
        $c6 = $this->persistCompetency('c6', $c3);
        $c7 = $this->persistCompetency('c7', $c3);
        $c8 = $this->persistCompetency('c8', $c3);

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

        $s = $this->persistScale('s');
        $l1 = $this->persistLevel('l1', $s);
        $l2 = $this->persistLevel('l2', $s);
        $l3 = $this->persistLevel('l3', $s);

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

        return [
            'competencies' => [
                'c1' => $c1,
                'c2' => $c2,
                'c3' => $c3,
                'c4' => $c4,
                'c5' => $c5,
                'c6' => $c6,
                'c7' => $c7,
                'c8' => $c8
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
                'a12' => $a12
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
                'ac14' => $ac14
            ],
            'levels' => [
                'l1' => $l1,
                'l2' => $l2,
                'l3' => $l3
            ]
        ];
    }

    protected function makeEvaluation($activityName, $status)
    {
        return $this->persistEvaluation(
            $this->framework['activities'][$activityName],
            $this->user,
            $status
        );
    }
}

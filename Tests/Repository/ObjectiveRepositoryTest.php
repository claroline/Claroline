<?php

namespace HeVinci\CompetencyBundle\Repository;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ObjectiveRepositoryTest extends RepositoryTestCase
{
    private $repo;
    private $context;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->om->getRepository('HeVinciCompetencyBundle:Objective');
        $this->context = $this->persistContext();
        $this->om->flush();
    }

    public function testFindByUser()
    {
        $objectives = $this->context['objectives'];
        $users = $this->context['users'];

        $u1Result = $this->repo->findByUser($users['u1']);
        $u2Result = $this->repo->findByUser($users['u2']);
        $u3Result = $this->repo->findByUser($users['u3']);

        $this->assertEquals(1, count($u1Result));
        $this->assertEquals($objectives['o1']->getId(), $u1Result[0]['id']);

        $this->assertEquals(2, count($u2Result));
        $this->assertEquals($objectives['o1']->getId(), $u1Result[0]['id']);
        $this->assertEquals($objectives['o2']->getId(), $u2Result[1]['id']);

        $this->assertEquals(1, count($u3Result));
        $this->assertEquals($objectives['o2']->getId(), $u3Result[0]['id']);
    }

    public function testFindByCompetencyAndUser()
    {
        $comps = $this->context['competencies'];
        $objectives = $this->context['objectives'];
        $users = $this->context['users'];

        $u1Result = $this->repo->findByCompetencyAndUser($comps['c2'], $users['u1']);
        $u2Result = $this->repo->findByCompetencyAndUser($comps['c2'], $users['u2']);
        $u3Result = $this->repo->findByCompetencyAndUser($comps['c2'], $users['u3']);

        $this->assertEquals(1, count($u1Result));
        $this->assertContains($objectives['o1'], $u1Result);

        $this->assertEquals(2, count($u2Result));
        $this->assertContains($objectives['o1'], $u2Result);
        $this->assertContains($objectives['o2'], $u2Result);

        $this->assertEquals(1, count($u3Result));
        $this->assertContains($objectives['o2'], $u3Result);
    }

    private function persistContext()
    {
        // Competencies:
        //   c1
        //     - c2
        //     - c3
        //
        // Objectives:
        //   - o1
        //     - c2
        //   - o2
        //     - c2
        //     - c3
        //
        // Users:
        //   - u1 (o1)
        //   - u2 (o1, o2)
        //   - u3
        //
        // Groups:
        //   - g1 (o2)
        //     - u2
        //     - u3

        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $u3 = $this->persistUser('u3');

        $g1 = $this->persistGroup('g1');
        $g1->addUser($u2);
        $g1->addUser($u3);

        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2', $c1);
        $c3 = $this->persistCompetency('c3', $c1);

        $s1 = $this->persistScale('s1');
        $l1 = $this->persistLevel('l1', $s1);

        $o1 = $this->persistObjective('o1', [
            [$c2, $c1, $l1]
        ]);
        $o2 = $this->persistObjective('o2', [
            [$c2, $c1, $l1],
            [$c3, $c1, $l1],
        ]);

        $o1->addUser($u1);
        $o1->addUser($u2);
        $o2->addUser($u2);
        $o2->addGroup($g1);

        return [
            'competencies' => [
                'c1' => $c1,
                'c2' => $c2,
                'c3' => $c3
            ],
            'objectives' => [
                'o1' => $o1,
                'o2' => $o2
            ],
            'users' => [
                'u1' => $u1,
                'u2' => $u2,
                'u3' => $u3
            ]
        ];
    }
}

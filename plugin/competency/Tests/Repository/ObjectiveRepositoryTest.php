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

    public function testGetUsersWithObjectiveQuery()
    {
        $objectives = $this->context['objectives'];
        $users = $this->context['users'];

        $result = $this->repo->getUsersWithObjectiveQuery()->getResult();
        $this->assertEquals(3, count($result));

        $result = $this->repo->getUsersWithObjectiveQuery($objectives['o1'])->getResult();
        $this->assertEquals(2, count($result));
        $this->assertEquals($users['u1']->getId(), $result[0]['id']);
        $this->assertEquals($users['u2']->getId(), $result[1]['id']);

        $result = $this->repo->getUsersWithObjectiveQuery($objectives['o2'])->getResult();
        $this->assertEquals(2, count($result));
        $this->assertEquals($users['u2']->getId(), $result[0]['id']);
        $this->assertEquals($users['u3']->getId(), $result[1]['id']);
    }

    public function testGetUsersWithObjectiveCountQuery()
    {
        $objectives = $this->context['objectives'];

        $result = $this->repo->getUsersWithObjectiveCountQuery()->getSingleScalarResult();
        $this->assertEquals(3, $result);

        $result = $this->repo->getUsersWithObjectiveCountQuery($objectives['o1'])->getSingleScalarResult();
        $this->assertEquals(2, $result);

        $result = $this->repo->getUsersWithObjectiveCountQuery($objectives['o2'])->getSingleScalarResult();
        $this->assertEquals(2, $result);
    }

    public function testGetGroupsWithObjectiveQuery()
    {
        $objectives = $this->context['objectives'];
        $groups = $this->context['groups'];

        $result = $this->repo->getGroupsWithObjectiveQuery()->getResult();
        $this->assertEquals(3, count($result));

        $result = $this->repo->getGroupsWithObjectiveQuery($objectives['o2'])->getResult();
        $this->assertEquals(2, count($result));
        $this->assertEquals($groups['g1']->getId(), $result[0]['id']);
        $this->assertEquals($groups['g3']->getId(), $result[1]['id']);
    }

    public function testGetGroupsWithObjectiveCountQuery()
    {
        $objectives = $this->context['objectives'];

        $result = $this->repo->getGroupsWithObjectiveCountQuery()->getSingleScalarResult();
        $this->assertEquals(3, $result);

        $result = $this->repo->getGroupsWithObjectiveCountQuery($objectives['o2'])->getSingleScalarResult();
        $this->assertEquals(2, $result);
    }

    public function testFindUsersWithObjective()
    {
        $objectives = $this->context['objectives'];
        $users = $this->context['users'];

        $result = $this->repo->findUsersWithObjective($objectives['o1']);
        $this->assertEquals(2, count($result));
        $this->assertContains($users['u1'], $result);
        $this->assertContains($users['u2'], $result);

        $result = $this->repo->findUsersWithObjective($objectives['o2']);
        $this->assertEquals(2, count($result));
        $this->assertContains($users['u2'], $result);
        $this->assertContains($users['u3'], $result);
    }

    public function testGetGroupUsersQuery()
    {
        $groups = $this->context['groups'];
        $users = $this->context['users'];

        $result = $this->repo->getGroupUsersQuery($groups['g2'])->getResult();
        $this->assertEquals(0, count($result));

        $result = $this->repo->getGroupUsersQuery($groups['g1'])->getResult();
        $this->assertEquals(2, count($result));
        $this->assertEquals($users['u2']->getId(), $result[0]['id']);
        $this->assertEquals($users['u3']->getId(), $result[1]['id']);
    }

    public function testGetGroupUsersCountQuery()
    {
        $groups = $this->context['groups'];

        $result = $this->repo->getGroupUsersCountQuery($groups['g1'])->getSingleScalarResult();
        $this->assertEquals(2, $result);
        $result = $this->repo->getGroupUsersCountQuery($groups['g2'])->getSingleScalarResult();
        $this->assertEquals(0, $result);
        $result = $this->repo->getGroupUsersCountQuery($groups['g3'])->getSingleScalarResult();
        $this->assertEquals(1, $result);
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
        //   - g2 (o1)
        //   - g3 (o2)
        //     - u2

        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $u3 = $this->persistUser('u3');

        $g1 = $this->persistGroup('g1');
        $g2 = $this->persistGroup('g2');
        $g3 = $this->persistGroup('g3');
        $g1->addUser($u2);
        $g1->addUser($u3);
        $g3->addUser($u2);

        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2', $c1);
        $c3 = $this->persistCompetency('c3', $c1);

        $s1 = $this->persistScale('s1');
        $l1 = $this->persistLevel('l1', $s1);

        $o1 = $this->persistObjective('o1', [
            [$c2, $c1, $l1],
        ]);
        $o2 = $this->persistObjective('o2', [
            [$c2, $c1, $l1],
            [$c3, $c1, $l1],
        ]);

        $o1->addUser($u1);
        $o1->addUser($u2);
        $o1->addGroup($g2);

        $o2->addUser($u2);
        $o2->addGroup($g1);
        $o2->addGroup($g3);

        return [
            'competencies' => [
                'c1' => $c1,
                'c2' => $c2,
                'c3' => $c3,
            ],
            'objectives' => [
                'o1' => $o1,
                'o2' => $o2,
            ],
            'users' => [
                'u1' => $u1,
                'u2' => $u2,
                'u3' => $u3,
            ],
            'groups' => [
                'g1' => $g1,
                'g2' => $g2,
                'g3' => $g3,
            ],
        ];
    }
}

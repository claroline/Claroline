<?php

namespace HeVinci\CompetencyBundle\Tests\Repository;

use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class AbilityRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->om->getRepository('HeVinciCompetencyBundle:Ability');
    }

    public function testFindByCompetency()
    {
        $scale = $this->persistScale('scale');
        $level = $this->persistLevel('l1', $scale);

        // framework 1
        $f1 = $this->persistCompetency('r1', null, $scale);
        $c1 = $this->persistCompetency('c1', $f1);
        $c2 = $this->persistCompetency('c2', $c1);
        $c3 = $this->persistCompetency('c3', $c2);
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $this->persistLink($c1, $a1, $level);
        $this->persistLink($c3, $a2, $level);

        // framework 2
        $f2 = $this->persistCompetency('r2', null, $scale);
        $a3 = $this->persistAbility('a3');
        $this->persistLink($f2, $a3, $level);

        $this->om->flush();

        $this->assertEquals(2, count($this->repo->findByCompetency($f1)));
        $this->assertEquals(1, count($this->repo->findByCompetency($f2)));
    }

    public function testFindOthersByCompetencyAndLevel()
    {
        $scale = $this->persistScale('scale');
        $l1 = $this->persistLevel('l1', $scale);
        $l2 = $this->persistLevel('l2', $scale);
        $c1 = $this->persistCompetency('c1', null, $scale);
        $c2 = $this->persistCompetency('c2', $c1); // extra data
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $a4 = $this->persistAbility('a4');
        $this->persistLink($c1, $a1, $l1);
        $this->persistLink($c1, $a2, $l1);
        $this->persistLink($c1, $a3, $l2);
        $this->persistLink($c1, $a4, $l2);

        $this->om->flush();

        $this->assertEquals(2, count($result = $this->repo->findOthersByCompetencyAndLevel($c1, $l1, $a3)));
        $this->assertEquals($a1, $result[0]);
        $this->assertEquals($a2, $result[1]);
        $this->assertEquals(1, count($result = $this->repo->findOthersByCompetencyAndLevel($c1, $l2, $a4)));
        $this->assertEquals($a3, $result[0]);
    }

    public function testDeleteOrphans()
    {
        $this->createLink(1);
        $this->createLink(2);
        $this->createLink(3);
        $this->persistAbility('Foo'); // not linked
        $this->om->flush();

        $this->assertEquals(4, $this->om->count('HeVinciCompetencyBundle:Ability'));
        $this->repo->deleteOrphans();
        $this->assertEquals(3, $this->om->count('HeVinciCompetencyBundle:Ability'));
    }

    public function testFindFirstByName()
    {
        $level = $this->persistLevel('l1', $this->persistScale('scale'));

        // framework 1
        $f1 = $this->persistCompetency('f1');
        $a1 = $this->persistAbility('FOO');
        $this->persistLink($f1, $a1, $level);

        // framework 2
        $f2 = $this->persistCompetency('f2');
        $c1 = $this->persistCompetency('c1', $f2);
        $a2 = $this->persistAbility('FOO B');
        $a3 = $this->persistAbility('BAR');
        $a4 = $this->persistAbility('FOO AB');
        $a5 = $this->persistAbility('FOO Z');
        $a6 = $this->persistAbility('FO');
        $a7 = $this->persistAbility('FOO D');
        $a8 = $this->persistAbility('FOO A');
        $this->persistLink($c1, $a2, $level);
        $this->persistLink($c1, $a3, $level);
        $this->persistLink($c1, $a4, $level);
        $this->persistLink($c1, $a5, $level);
        $this->persistLink($c1, $a6, $level);
        $this->persistLink($c1, $a7, $level);
        $this->persistLink($c1, $a8, $level);

        $this->om->flush();

        $this->assertEquals([], $this->repo->findFirstByName('AR', $f1));
        $this->assertEquals([$a3], $this->repo->findFirstByName('BA', $f1));
        $this->assertEquals([$a6, $a8, $a4, $a2, $a7], $this->repo->findFirstByName('FO', $f1));
        $this->assertEquals([$a8, $a4, $a2, $a7, $a5], $this->repo->findFirstByName('FOO', $f1));
        $this->assertEquals([$a8, $a4], $this->repo->findFirstByName('FOO A', $f1));
    }

    public function testFindByActivity()
    {
        $level = $this->persistLevel('l1', $this->persistScale('scale'));

        // framework 1
        $f1 = $this->persistCompetency('f1');
        $a1 = $this->persistAbility('a1');
        $this->persistLink($f1, $a1, $level);

        // framework 2
        $f2 = $this->persistCompetency('f2');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $this->persistLink($f2, $a2, $level);
        $this->persistLink($f2, $a3, $level);

        $activity = $this->createActivity('FOO');
        $a1->linkActivity($activity);
        $a3->linkActivity($activity);

        $this->om->flush();

        $this->assertEquals([$a1, $a3], $this->repo->findByActivity($activity));
    }

    /**
     * @expectedException Exception
     */
    public function testFindEvaluationsByCompetencyThrowsAnExceptionIfNotLeafCompetency()
    {
        $u1 = $this->persistUser('u1');
        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2', $c1);
        $this->om->flush();
        $this->repo->findEvaluationsByCompetency($c1, $u1);
    }

    public function testFindEvaluationsByCompetency()
    {
        // Users:
        //
        // u1
        // u2
        //
        // Frameworks:
        //
        // c1
        //   - a1 (l1)
        //     - ac1
        //       - e1 (u1, failed)
        //       - e2 (u1, passed)
        //       - e5 (u2, passed)
        //   - a2 (l2)
        //     - ac2
        //       - e3 (u1, passed)
        //   - a3 (l3)
        //     - ac2
        //       - e3 (u1, passed)
        // c2
        //   - a4 (l1)
        //     - ac3
        //       - e4 (u1, passed)
        //

        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2'); // extra data

        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2'); // extra data

        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $a4 = $this->persistAbility('a4'); // extra data

        $s1 = $this->persistScale('s1');
        $l1 = $this->persistLevel('l1', $s1, 0);
        $l2 = $this->persistLevel('l2', $s1, 1);
        $l3 = $this->persistLevel('l3', $s1, 2);

        $this->persistLink($c1, $a1, $l1);
        $this->persistLink($c1, $a2, $l2);
        $this->persistLink($c1, $a3, $l3);
        $this->persistLink($c2, $a4, $l1); // extra data

        $ac1 = $this->persistActivity('ac1');
        $ac2 = $this->persistActivity('ac2');
        $ac3 = $this->persistActivity('ac3'); // extra data

        $a1->linkActivity($ac1);
        $a2->linkActivity($ac2);
        $a3->linkActivity($ac2); // bound to 2 abilities
        $a4->linkActivity($ac3); // extra data

        $e1 = $this->persistEvaluation($ac1, $u1, AbstractEvaluation::STATUS_FAILED);
        $e2 = $this->persistEvaluation($ac1, $u1, AbstractEvaluation::STATUS_PASSED, $e1);
        $e3 = $this->persistEvaluation($ac2, $u1, AbstractEvaluation::STATUS_PASSED);
        $e4 = $this->persistEvaluation($ac3, $u1, AbstractEvaluation::STATUS_PASSED); // extra data
        $e5 = $this->persistEvaluation($ac1, $u2, AbstractEvaluation::STATUS_PASSED, null, $e1->getActivityParameters()); // extra data

        $this->om->flush();

        $result = $this->repo->findEvaluationsByCompetency($c1, $u1);

        $this->assertEquals(3, count($result));

        $this->assertEquals($e2->getId(), $result[0]['evaluationId']);
        $this->assertEquals('a1', $result[0]['abilityName']);
        $this->assertEquals('ac1', $result[0]['activityName']);
        $this->assertEquals('l1', $result[0]['levelName']);
        $this->assertEquals(AbstractEvaluation::STATUS_PASSED, $result[0]['status']);

        $this->assertEquals($e3->getId(), $result[1]['evaluationId']);
        $this->assertEquals('a2', $result[1]['abilityName']);
        $this->assertEquals('ac2', $result[1]['activityName']);
        $this->assertEquals('l2', $result[1]['levelName']);
        $this->assertEquals(AbstractEvaluation::STATUS_PASSED, $result[1]['status']);

        // As an activity can be bound to multiple abilities, a same evaluation of
        // an activity can be related to multiple abilities/levels as well. The repo
        // builds evaluation data by joining actual "evaluation" records and framework
        // structure. That's why the "e3" id appears twice in the result set.
        $this->assertEquals($e3->getId(), $result[2]['evaluationId']);
        $this->assertEquals('a3', $result[2]['abilityName']);
        $this->assertEquals('ac2', $result[2]['activityName']);
        $this->assertEquals('l3', $result[2]['levelName']);
        $this->assertEquals(AbstractEvaluation::STATUS_PASSED, $result[2]['status']);
    }

    private function createLink($index)
    {
        $competency = $this->persistCompetency('Competency ' . $index);
        $ability = $this->persistAbility('Ability ' . $index);
        $scale = $this->persistScale('Scale ' . $index);
        $level = $this->persistLevel('Level ' . $index, $scale, $index);
        $this->persistLink($competency, $ability, $level);
    }

    private function createActivity($name)
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUsername('jdoe');
        $user->setMail('jd@mail.com');
        $user->setPassword('123');

        $workspace = new Workspace();
        $workspace->setName('w1');
        $workspace->setCode('abc');
        $workspace->setGuid('abc123');
        $workspace->setCreator($user);

        $type = new ResourceType();
        $type->setName('activity');

        $node = new ResourceNode();
        $node->setName($name);
        $node->setClass('FooClass');
        $node->setCreator($user);
        $node->setResourceType($type);
        $node->setWorkspace($workspace);

        $activity = new Activity();
        $activity->setResourceNode($node);
        $activity->setDescription('activity...');

        $this->om->persist($user);
        $this->om->persist($workspace);
        $this->om->persist($type);
        $this->om->persist($node);
        $this->om->persist($activity);

        return $activity;
    }
}

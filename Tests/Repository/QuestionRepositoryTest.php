<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\SurveyBundle\Repository\QuestionRepository;
use UJM\ExoBundle\Testing\Persister;

class QuestionRepositoryTest extends TransactionalTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Persister
     */
    private $persist;

    /**
     * @var QuestionRepository
     */
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $manager = $this->client->getContainer()->get('ujm.exo.paper_manager');
        $this->persist = new Persister($this->om, $manager);
        $this->repo = $this->om->getRepository('UJMExoBundle:Question');
    }

    public function testFindByUser()
    {
        $q1 = $this->persist->qcmQuestion('qcm1');
        $q2 = $this->persist->qcmQuestion('qcm2');
        $q3 = $this->persist->qcmQuestion('qcm3');
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $c1 = $this->persist->category('c1');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u2);
        $q1->setCategory($c1);
        $q2->setCategory($c1);
        $q3->setCategory($c1);

        $this->om->flush();

        $questions = $this->repo->findByUser($u1);
        $this->assertEquals([$q1, $q2], $questions);
    }

    public function testFindByExercise()
    {
      //Il faut rajouter les steps..
        $q1 = $this->persist->qcmQuestion('qcm1');
        $q2 = $this->persist->qcmQuestion('qcm2');
        $q3 = $this->persist->qcmQuestion('qcm3'); // extr
        $e1 = $this->persist->exercise('ex1', [$q1, $q2]);
        $this->om->flush();
// var_dump($e1->getTitle());
// var_dump($q1->getTitle());
        $questions = $this->repo->findByExercise($e1);
        // var_dump($questions);
        // die();
        $this->assertEquals([$q1, $q2], $questions);
    }

    public function testFindByUserNotInExercise()
    {
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $q1 = $this->persist->qcmQuestion('q1');
        $q2 = $this->persist->qcmQuestion('q2');
        $q3 = $this->persist->qcmQuestion('q3');
        $q4 = $this->persist->qcmQuestion('q4');
        $q5 = $this->persist->qcmQuestion('q5');
        $e1 = $this->persist->exercise('e1', [$q1, $q2]);

        $q1->setUser($u1);
        $q2->setUser($u2);
        $q3->setUser($u1);
        $q4->setUser($u2);
        $q5->setUser($u1);

        $q5->setModel(true);

        $this->om->flush();

        $questions = $this->repo->findByUserNotInExercise($u1, $e1);
        $this->assertEquals([$q3, $q5], $questions);
        $questions = $this->repo->findByUserNotInExercise($u1, $e1, true);
        $this->assertEquals([$q5], $questions);
    }

    public function testFindByUserAndCategoryName()
    {
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $q1 = $this->persist->qcmQuestion('q1');
        $q2 = $this->persist->qcmQuestion('q2');
        $q3 = $this->persist->qcmQuestion('q3');
        $q4 = $this->persist->qcmQuestion('q4');
        $q5 = $this->persist->qcmQuestion('q5'); // extra
        $c1 = $this->persist->category('bar');
        $c2 = $this->persist->category('foo');
        $c3 = $this->persist->category('baz');

        $q1->setUser($u1);
        $q2->setUser($u2);
        $q3->setUser($u1);
        $q4->setUser($u2);
        $q5->setUser($u1);

        $q1->setCategory($c1);
        $q2->setCategory($c2);
        $q3->setCategory($c2);
        $q4->setCategory($c3);
        $q5->setCategory($c3);

        $this->om->flush();

        $questions = $this->repo->findByUserAndCategoryName($u1, 'ba');
        $this->assertEquals([$q1, $q5], $questions);
    }

    public function testFindByUserAndType()
    {
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $q1 = $this->persist->qcmQuestion('q1');
        $q2 = $this->persist->openQuestion('q2');
        $q3 = $this->persist->qcmQuestion('q3');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u2);

        $this->om->flush();

        $questions = $this->repo->findByUserAndType($u1, 'ope');
        $this->assertEquals([$q2], $questions);
    }

    public function testFindByUserAndTitle()
    {
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $q1 = $this->persist->qcmQuestion('q1');
        $q2 = $this->persist->qcmQuestion('q2a');
        $q3 = $this->persist->qcmQuestion('q3az');
        $q4 = $this->persist->qcmQuestion('q4');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u1);
        $q4->setUser($u2);

        $this->om->flush();

        $questions = $this->repo->findByUserAndTitle($u1, 'a');
        $this->assertEquals([$q2, $q3], $questions);
    }

    public function testFindByUserAndInvite()
    {
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $q1 = $this->persist->qcmQuestion('q1');
        $q2 = $this->persist->qcmQuestion('q2');
        $q3 = $this->persist->qcmQuestion('q3');
        $q4 = $this->persist->qcmQuestion('q4');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u1);
        $q4->setUser($u2);

        $q1->setInvite('...Bar');
        $q2->setInvite('...BAZ');
        $q3->setInvite('Foo');

        $this->om->flush();

        $questions = $this->repo->findByUserAndInvite($u1, 'ba');
        $this->assertEquals([$q1, $q2], $questions);
    }

    public function testFindByUserAndContent()
    {
        $u1 = $this->persist->user('u1');
        $u2 = $this->persist->user('u2');
        $q1 = $this->persist->qcmQuestion('q1');
        $q2 = $this->persist->qcmQuestion('--match--');
        $q3 = $this->persist->qcmQuestion('q3');
        $q4 = $this->persist->qcmQuestion('q4');
        $q5 = $this->persist->qcmQuestion('q5');
        $q6 = $this->persist->qcmQuestion('q6');
        $c1 = $this->persist->category('-match-');
        $c2 = $this->persist->category('c2');
        $e1 = $this->persist->exercise('e1', [$q1, $q2, $q3]);

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u1);
        $q4->setUser($u1);
        $q5->setUser($u1);
        $q6->setUser($u2);

        $q1->setCategory($c1);
        $q2->setCategory($c2);
        $q3->setType('---match---');
        $q4->setInvite('----match----');

        $this->om->flush();

        $questions = $this->repo->findByUserAndContent($u1, 'match');
        $this->assertEquals(4, count($questions));
        $this->assertContains($q1, $questions);
        $this->assertContains($q2, $questions);
        $this->assertContains($q3, $questions);
        $this->assertContains($q4, $questions);
        $questions = $this->repo->findByUserAndContent($u1, 'match', $e1);
        $this->assertEquals([$q4], $questions);
    }
}

<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\Question;

class InteractionRepositoryTest extends TransactionalTestCase
{
    private $om;
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->repo = $this->om->getRepository('UJMExoBundle:Question');
    }

    public function testFindByUser()
    {
        $q1 = $this->persistQcmQuestion('qcm1');
        $q2 = $this->persistQcmQuestion('qcm2');
        $q3 = $this->persistQcmQuestion('qcm3');
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $c1 = $this->persistCategory('c1');

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
        $q1 = $this->persistQcmQuestion('qcm1');
        $q2 = $this->persistQcmQuestion('qcm2');
        $q3 = $this->persistQcmQuestion('qcm3'); // extra
        $e1 = $this->persistExercise('ex1', [$q1, $q2]);
        $this->om->flush();

        $questions = $this->repo->findByExercise($e1);
        $this->assertEquals([$q1, $q2], $questions);
    }

    public function testFindByUserNotInExercise()
    {
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $q1 = $this->persistQcmQuestion('q1');
        $q2 = $this->persistQcmQuestion('q2');
        $q3 = $this->persistQcmQuestion('q3');
        $q4 = $this->persistQcmQuestion('q4');
        $q5 = $this->persistQcmQuestion('q5');
        $e1 = $this->persistExercise('e1', [$q1, $q2]);

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
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $q1 = $this->persistQcmQuestion('q1');
        $q2 = $this->persistQcmQuestion('q2');
        $q3 = $this->persistQcmQuestion('q3');
        $q4 = $this->persistQcmQuestion('q4');
        $q5 = $this->persistQcmQuestion('q5'); // extra
        $c1 = $this->persistCategory('bar');
        $c2 = $this->persistCategory('foo');
        $c3 = $this->persistCategory('baz');

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
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $q1 = $this->persistQcmQuestion('q1');
        $q2 = $this->persistOpenQuestion('q2');
        $q3 = $this->persistQcmQuestion('q3');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u2);

        $this->om->flush();

        $questions = $this->repo->findByUserAndType($u1, 'ope');
        $this->assertEquals([$q2], $questions);
    }

    public function testFindByUserAndTitle()
    {
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $q1 = $this->persistQcmQuestion('q1');
        $q2 = $this->persistQcmQuestion('q2a');
        $q3 = $this->persistQcmQuestion('q3az');
        $q4 = $this->persistQcmQuestion('q4');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u1);
        $q4->setUser($u2);

        $this->om->flush();

        $questions = $this->repo->findByUserAndTitle($u1, 'a');
        $this->assertEquals([$q2, $q3], $questions);
    }

    public function testFindByUserAndDescription()
    {
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $q1 = $this->persistQcmQuestion('q1');
        $q2 = $this->persistQcmQuestion('q2');
        $q3 = $this->persistQcmQuestion('q3');
        $q4 = $this->persistQcmQuestion('q4');

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u1);
        $q4->setUser($u2);

        $q3->setDescription('Foo');

        $this->om->flush();

        $questions = $this->repo->findByUserAndDescription($u1, 'esc');
        $this->assertEquals([$q1, $q2], $questions);
    }

    public function testFindByUserAndContent()
    {
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $q1 = $this->persistQcmQuestion('q1');
        $q2 = $this->persistQcmQuestion('--match--');
        $q3 = $this->persistQcmQuestion('q3');
        $q4 = $this->persistQcmQuestion('q4');
        $q5 = $this->persistQcmQuestion('q5');
        $q6 = $this->persistQcmQuestion('q6');
        $c1 = $this->persistCategory('-match-');
        $c2 = $this->persistCategory('c2');
        $e1 = $this->persistExercise('e1', [$q1, $q2, $q3]);

        $q1->setUser($u1);
        $q2->setUser($u1);
        $q3->setUser($u1);
        $q4->setUser($u1);
        $q5->setUser($u1);
        $q6->setUser($u2);

        $q1->setCategory($c1);
        $q2->setCategory($c2);
        $q3->setType('---match---');
        $q4->setDescription('----match----');

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

    private function persistQcmQuestion($title, array $choices = [])
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setDescription('Description...');

        $interactionQcm = new InteractionQCM();
        $interactionQcm->setQuestion($question);

        $this->om->persist($interactionQcm);
        $this->om->persist($question);

        return $question;
    }

    private function persistOpenQuestion($title)
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setDescription('Description...');

        $interactionQcm = new InteractionOpen();
        $interactionQcm->setQuestion($question);

        $this->om->persist($interactionQcm);
        $this->om->persist($question);

        return $question;
    }

    private function persistExercise($title, array $questions = [])
    {
        $exercise = new Exercise();
        $exercise->setTitle($title);

        for ($i = 0, $max = count($questions); $i < $max; ++$i) {
            $link = new ExerciseQuestion($exercise, $questions[$i]);
            $link->setOrdre($i);
            $this->om->persist($link);
        }

        $this->om->persist($exercise);

        return $exercise;
    }

    private function persistUser($username)
    {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setMail($username . '@mail.com');
        $this->om->persist($user);

        return $user;
    }

    private function persistCategory($name)
    {
        $category = new Category();
        $category->setValue($name);
        $this->om->persist($category);

        return $category;
    }
}

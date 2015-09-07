<?php

namespace UJM\ExoBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Interaction;
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
        $this->repo = $this->om->getRepository('UJMExoBundle:Interaction');
    }

    public function testFindByExercise()
    {
        $q1 = $this->persistQcmQuestion('qcm1');
        $q2 = $this->persistQcmQuestion('qcm2');
        $q3 = $this->persistQcmQuestion('qcm3'); // extra
        $e1 = $this->persistExercise('ex1', [$q1, $q2]);
        $this->om->flush();

        $interactions = $this->repo->findByExercise($e1);
        $this->assertEquals(2, count($interactions));
        $this->assertEquals($q1, $interactions[0]->getQuestion());
        $this->assertEquals($q2, $interactions[1]->getQuestion());
    }

    private function persistQcmQuestion($title, array $choices = [])
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setDateCreate(new \DateTime());

        $interaction = new Interaction();
        $interaction->setType('InteractionQCM');
        $interaction->setQuestion($question);
        $interaction->setInvite('Invite...');

        $interactionQcm = new InteractionQCM();
        $interactionQcm->setInteraction($interaction);

        $this->om->persist($interactionQcm);
        $this->om->persist($interaction);
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
}

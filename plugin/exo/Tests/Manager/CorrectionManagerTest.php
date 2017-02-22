<?php

namespace UJM\ExoBundle\Tests\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Manager\CorrectionManager;

class CorrectionManagerTest extends JsonDataTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var PaperGenerator */
    private $paperGenerator;
    /** @var CorrectionManager */
    private $manager;

    /** @var Exercise */
    private $exercise;
    /** @var Item[] */
    private $questions = [];
    /** @var Hint[] */
    private $hints = [];
    /** @var Paper */
    private $paper;
    /** @var Answer[] */
    private $answers = [];

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
        $this->manager = $this->client->getContainer()->get('ujm_exo.manager.correction');
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');

        $this->questions = [
            $this->persist->openQuestion('Open question 1'),
            $this->persist->openQuestion('Open question 2'),
        ];
        $this->hints[] = $this->persist->hint($this->questions[0], 'this is an hint', 1);
        $this->exercise = $this->persist->exercise('my exercise', $this->questions, $this->persist->user('bob'));

        $this->paper = $this->paperGenerator->create($this->exercise);
        // Set paper end because we can not correct unfinished papers
        $this->paper->setEnd(new \DateTime());

        // Create a question that needs to be corrected
        $answerToCorrect = new Answer();
        $answerToCorrect->setQuestionId($this->questions[0]->getUuid());
        $answerToCorrect->setIp('127.0.0.1');
        $this->paper->addAnswer($answerToCorrect);
        $this->answers[] = $answerToCorrect;

        // Create a corrected answer (should not be returned by api)
        $answer = new Answer();
        $answer->setQuestionId($this->questions[0]->getUuid());
        $answer->setIp('127.0.0.1');
        $answer->setScore(5);
        $this->paper->addAnswer($answer);
        $this->answers[] = $answer;

        $this->om->persist($this->paper);

        $this->om->flush();
    }

    public function testGetToCorrect()
    {
        $toCorrect = $this->manager->getToCorrect($this->exercise);

        $this->assertTrue(is_array($toCorrect));

        // Checks returned questions
        $this->assertTrue(is_array($toCorrect['questions']));
        $this->assertCount(1, $toCorrect['questions']);
        $this->assertEquals($this->questions[0]->getUuid(), $toCorrect['questions'][0]->id);

        // Checks returned answers
        $this->assertTrue(is_array($toCorrect['answers']));
        $this->assertCount(1, $toCorrect['answers']);
        $this->assertEquals($this->answers[0]->getUuid(), $toCorrect['answers'][0]->id);
    }

    /**
     * @expectedException \UJM\ExoBundle\Library\Validator\ValidationException
     */
    public function testSaveUnknownAnswer()
    {
        $toCorrect = new \stdClass();
        $toCorrect->id = uniqid(); // Unknown answer
        $toCorrect->questionId = uniqid();
        $toCorrect->score = 5;
        $toCorrect->feedback = 'this is a feedback';

        $this->manager->save([$toCorrect]);
    }

    public function testSave()
    {
        $toCorrect = new \stdClass();
        $toCorrect->id = $this->answers[0]->getUuid();
        $toCorrect->questionId = $this->answers[0]->getQuestionId();
        $toCorrect->score = 5;
        $toCorrect->feedback = 'this is a feedback';
        $toCorrect->type = 'application/x.open+json';

        // Paper score have never been calculated for now
        // We just take obtained score in test data (we can do it because their is no penalty)
        $previousScore = 0;
        foreach ($this->answers as $answer) {
            if ($answer->getScore()) {
                $previousScore += $answer->getScore();
            }
        }

        $this->manager->save([$toCorrect]);

        /** @var Answer $updatedAnswer */
        $updatedAnswer = $this->om->getRepository('UJMExoBundle:Attempt\Answer')->findOneBy([
            'uuid' => $this->answers[0]->getUuid(),
        ]);

        $this->assertFalse(is_null($updatedAnswer));
        $this->assertEquals($toCorrect->score, $updatedAnswer->getScore());
        $this->assertEquals($toCorrect->feedback, $updatedAnswer->getFeedback());

        // Checks paper score have been updated too
        $this->assertEquals($this->paper->getScore(), $previousScore + $toCorrect->score);
    }

    public function testSaveWithPenalties()
    {
        // Use an hint in the answer
        $this->answers[0]->addUsedHint($this->hints[0]->getUuid());
        $this->om->flush();

        $toCorrect = new \stdClass();
        $toCorrect->id = $this->answers[0]->getUuid();
        $toCorrect->questionId = $this->answers[0]->getQuestionId();
        $toCorrect->score = 5;
        $toCorrect->feedback = 'this is a feedback';
        $toCorrect->type = 'application/x.open+json';

        $this->manager->save([$toCorrect]);

        /** @var Answer $updatedAnswer */
        $updatedAnswer = $this->om->getRepository('UJMExoBundle:Attempt\Answer')->findOneBy([
            'uuid' => $this->answers[0]->getUuid(),
        ]);

        // Checks the score include hint penalty
        $this->assertEquals($toCorrect->score - $this->hints[0]->getPenalty(), $updatedAnswer->getScore());
    }
}

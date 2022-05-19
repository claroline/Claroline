<?php

namespace UJM\ExoBundle\Tests\Repository;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Repository\PaperRepository;

class PaperRepositoryTest extends TransactionalTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PaperGenerator
     */
    private $paperGenerator;

    /**
     * @var Persister
     */
    private $persist;

    /**
     * @var PaperRepository
     */
    private $repo;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Exercise
     */
    private $exercise;

    /**
     * @var Hint
     */
    private $hint;

    /**
     * A list of papers that can be used in all tests.
     *
     * @var Paper[]
     */
    private $papers = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get(ObjectManager::class);
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');
        $this->persist = new Persister($this->om);
        $this->repo = $this->om->getRepository(Paper::class);

        // Initialize some base data for tests
        $question = $this->persist->openQuestion('Open question');
        $this->hint = $this->persist->hint($question, 'hint text');

        $this->user = $this->persist->user('john');
        $this->exercise = $this->persist->exercise('Exercise 1', [
            $question,
        ], $this->user);

        $paper1 = $this->paperGenerator->create($this->exercise, $this->user);
        $this->om->persist($paper1);

        $paper2 = $this->paperGenerator->create($this->exercise, $this->user, $paper1);
        $this->om->persist($paper2);

        // Create a finished paper
        $paperFinished = $this->paperGenerator->create($this->exercise, $this->user, $paper2);
        $paperFinished->setEnd(new \DateTime());
        $this->om->persist($paperFinished);

        // Create an invalidated paper
        $paperInvalidated = $this->paperGenerator->create($this->exercise, $this->user, $paperFinished);
        $paperInvalidated->setInvalidated(true);
        $this->om->persist($paperInvalidated);

        // Create data that will never be returned to check conditions
        $paperOtherUser = $this->paperGenerator->create($this->exercise);
        $this->om->persist($paperOtherUser);

        $paperOtherExercise = $this->paperGenerator->create(
            $this->persist->exercise('Exercise 2', [], $this->user),
            $this->user
        );
        $this->om->persist($paperOtherExercise);

        // Creates some papers
        $this->papers = [
            $paper1,
            $paper2,
            $paperFinished,
            $paperInvalidated,
            $paperOtherUser,
            $paperOtherExercise,
        ];

        $this->om->flush();
    }

    public function testFindLastPaper()
    {
        $lastPaper = $this->repo->findLastPaper($this->exercise, $this->user);

        $this->assertInstanceOf('UJM\ExoBundle\Entity\Attempt\Paper', $lastPaper);
        $this->assertEquals(4, $lastPaper->getNumber());
        $this->assertEquals($this->user, $lastPaper->getUser());
        $this->assertEquals($this->exercise, $lastPaper->getExercise());
    }

    public function testFindLastPaperNoPaper()
    {
        // Let me introduce you Bob : the user who have no papers
        $bob = $this->persist->user('bob');
        $this->om->flush();

        $lastPaper = $this->repo->findLastPaper($this->exercise, $bob);
        $this->assertNull($lastPaper);
    }

    public function testFindUnfinishedPapers()
    {
        $papers = $this->repo->findUnfinishedPapers($this->exercise, $this->user);

        $this->assertTrue(is_array($papers));
        $this->assertCount(3, $papers); // result = count($this->papers) - other exercises - other users - finished papers
        $this->assertInstanceOf('UJM\ExoBundle\Entity\Attempt\Paper', $papers[0]);
    }

    public function testCountExercisePapers()
    {
        $papersCount = $this->repo->countExercisePapers($this->exercise);
        $this->assertEquals(5, $papersCount);
    }

    public function testCountUserFinishedPapers()
    {
        $finishedCount = $this->repo->countUserFinishedPapers($this->exercise, $this->user);
        $this->assertEquals(1, $finishedCount);
    }

    public function testIsFullyEvaluated()
    {
        // Add a corrected answer to the paper (aka answer with score) to check the query conditions
        $answer = new Answer();
        $answer->setIp('127.0.0.1');
        $answer->setScore(5);
        $answer->setData('some answer data');
        $answer->setQuestionId('does-not-need-a-real-one');
        $this->papers[0]->addAnswer($answer);

        $this->om->flush();

        $this->assertTrue($this->repo->isFullyEvaluated($this->papers[0]));
    }

    public function testIsNotFullyEvaluated()
    {
        // Add a non corrected answer to the paper (aka answer with no score)
        $answer = new Answer();
        $answer->setIp('127.0.0.1');
        $answer->setData('some answer data');
        $answer->setQuestionId('does-not-need-a-real-one');
        $this->papers[0]->addAnswer($answer);

        $this->om->flush();

        $this->assertFalse($this->repo->isFullyEvaluated($this->papers[0]));
    }

    public function testFindScore()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testFindPapersToCorrect()
    {
        // Add a non corrected answer to the paper (aka answer with no score)
        $answer = new Answer();
        $answer->setIp('127.0.0.1');
        $answer->setData('some answer data');
        $answer->setQuestionId('does-not-need-a-real-one');
        $this->papers[0]->addAnswer($answer);

        // Mark the paper has finished
        $this->papers[0]->setEnd(new \DateTime());

        $this->om->flush();

        $papers = $this->repo->findPapersToCorrect($this->exercise);

        $this->assertTrue(is_array($papers));
        $this->assertCount(1, $papers);
        $this->assertInstanceOf('UJM\ExoBundle\Entity\Attempt\Paper', $papers[0]);
    }

    public function testHasHint()
    {
        // Create an hint which is not linked to a question of the exercise
        $hint = $this->persist->hint(
            $this->persist->openQuestion('question not in exercise'),
            'hint not linked to paper'
        );
        $this->om->flush();

        $hasHint = $this->repo->hasHint($this->papers[0], $hint);
        $this->assertFalse($hasHint);

        $hasHint = $this->repo->hasHint($this->papers[0], $this->hint);
        $this->assertTrue($hasHint);
    }
}

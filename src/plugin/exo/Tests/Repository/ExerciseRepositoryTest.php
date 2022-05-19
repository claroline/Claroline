<?php

namespace UJM\ExoBundle\Tests\Repository;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Repository\ExerciseRepository;

class ExerciseRepositoryTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var ExerciseRepository */
    private $repo;
    /** @var Item[] */
    private $questions;
    /** @var Exercise[] */
    private $exercises = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get(ObjectManager::class);
        $this->persist = new Persister($this->om);
        $this->repo = $this->om->getRepository(Exercise::class);

        // Initialize some base data for tests
        $paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');
        $exerciseWithPapers = $this->persist->exercise('Exercise 1', [], $this->persist->user('john'));

        $paper1 = $paperGenerator->create($exerciseWithPapers);
        $this->om->persist($paper1);

        $paper2 = $paperGenerator->create($exerciseWithPapers);
        $this->om->persist($paper2);

        $this->questions = [
            $this->persist->openQuestion('Open question'),
        ];
        $exerciseWithQuestions = $this->persist->exercise('Exercise 2', $this->questions, $this->persist->user('bob'));

        $this->exercises = [
            $exerciseWithPapers,
            $exerciseWithQuestions,
        ];

        $this->om->flush();
    }

    public function testFindScores()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * The repository MUST return the list of exercises using the question.
     */
    public function testFindByQuestion()
    {
        $exercises = $this->repo->findByQuestion($this->questions[0]);

        $this->assertTrue(is_array($exercises));
        $this->assertCount(1, $exercises);
    }

    public function testInvalidatePapers()
    {
        $this->repo->invalidatePapers($this->exercises[0]);
        $this->om->clear(); // this is needed to force doctrine to reload the entities

        $papers = $this->om->getRepository(Paper::class)->findBy([
            'exercise' => $this->exercises[0],
        ]);

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            $this->assertTrue($paper->isInvalidated());
        }
    }
}

<?php

namespace UJM\ExoBundle\Tests\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Testing\Json\JsonDataTestCase;
use UJM\ExoBundle\Library\Testing\Persister;
use UJM\ExoBundle\Manager\ExerciseManager;

class ExerciseManagerTest extends JsonDataTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var PaperGenerator */
    private $paperGenerator;
    /** @var ExerciseManager */
    private $manager;
    /** @var Exercise */
    private $exercise;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
        $this->manager = $this->client->getContainer()->get('ujm_exo.manager.exercise');
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');

        $this->exercise = $this->persist->exercise('my exercise', [
            [$this->persist->openQuestion('Open question.')],
            [$this->persist->openQuestion('Open question.')],
        ], $this->persist->user('bob'));
        $this->om->flush();
    }

    public function testSerialize()
    {
        $data = $this->manager->serialize($this->exercise);

        // Checks the result of the serializer is returned
        $this->assertInstanceOf('\stdClass', $data);
        $this->assertEquals($this->exercise->getUuid(), $data->id);
    }

    public function testUpdate()
    {
        $validData = $this->loadTestData('exercise/valid/with-steps.json');

        $this->manager->update($this->exercise, $validData);

        // Checks some props
        $this->assertEquals($this->exercise->getTitle(), $validData->title);
        $this->assertCount($this->exercise->getSteps()->count(), $validData->steps);
    }

    /**
     * @expectedException \UJM\ExoBundle\Library\Validator\ValidationException
     */
    public function testUpdateWithInvalidData()
    {
        $invalidData = $this->loadTestData('exercise/invalid/no-pick.json');

        $this->manager->update($this->exercise, $invalidData);
    }

    /**
     * When an exercise is updated, the linked papers MUST be marked as invalid.
     */
    public function testUpdateInvalidatePapers()
    {
        // Create a bunch of papers
        $this->addPapersToExercise();
        $this->om->flush();

        // Update exercise
        $exerciseData = $this->loadTestData('exercise/valid/with-steps.json');
        $this->manager->update($this->exercise, $exerciseData);

        // this is needed to force doctrine to reload the entities
        $this->om->clear();

        // Check paper validity
        $papers = $this->om->getRepository('UJMExoBundle:Attempt\Paper')->findBy([
            'exercise' => $this->exercise,
        ]);

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            $this->assertTrue($paper->isInvalidated());
        }
    }

    public function testCopy()
    {
        $copy = $this->manager->copy($this->exercise);

        // Checks the copy has its own ids
        $this->assertNotEquals($this->exercise->getId(), $copy->getId());

        // Checks there is one more exercise in the DB
        $exercises = $this->om->getRepository('UJMExoBundle:Exercise')->findAll();
        $this->assertCount(2, $exercises);

        // Check the copy contains the correct amount of steps
        $this->assertCount($this->exercise->getSteps()->count(), $copy->getSteps());

        // Checks steps have been duplicated (and not just referenced)
        $firstStepOri = $this->exercise->getSteps()->get(0);
        $firstStepCopy = $copy->getSteps()->get(0);

        $this->assertNotEquals($firstStepOri->getId(), $firstStepCopy->getId());

        // Checks questions have been duplicated
        /** @var Item $questionOri */
        $questionOri = $firstStepOri->getStepQuestions()->get(0)->getQuestion();
        /** @var Item $questionCopy */
        $questionCopy = $firstStepCopy->getStepQuestions()->get(0)->getQuestion();

        $this->assertNotEquals($questionOri->getId(), $questionCopy->getId());
    }

    /**
     * An exercise MUST be deletable if it's not published or have no paper.
     */
    public function testIsDeletableIfNoPapers()
    {
        $this->exercise->getResourceNode()->setPublished(true);
        $this->om->flush();

        $this->assertTrue($this->manager->isDeletable($this->exercise));
    }

    /**
     * An exercise MUST be deletable if it's not published (even if there are papers).
     */
    public function testIsDeletableIfNotPublished()
    {
        $this->addPapersToExercise();
        $this->exercise->getResourceNode()->setPublished(false);
        $this->om->flush();

        $this->assertTrue($this->manager->isDeletable($this->exercise));
    }

    /**
     * An exercise MUST NOT be deletable if it's published and has papers.
     */
    public function testIsNotDeletableIfPapers()
    {
        $this->addPapersToExercise();
        $this->exercise->getResourceNode()->setPublished(true);
        $this->om->flush();

        $this->assertFalse($this->manager->isDeletable($this->exercise));
    }

    public function testPublishOncePublishedExercise()
    {
        $this->exercise->getResourceNode()->setPublished(false);
        $this->exercise->setPublishedOnce(true);
        $this->om->flush();

        $this->manager->publish($this->exercise);

        $this->assertTrue($this->exercise->getResourceNode()->isPublished());

        // Checks papers have been untouched
        $papers = $this->om->getRepository('UJMExoBundle:Attempt\Paper')->findBy([
            'exercise' => $this->exercise,
        ]);

        $this->assertCount(0, $papers);
    }

    public function testPublishNeverPublishedExerciseDeleteItsPapers()
    {
        $this->addPapersToExercise();
        $this->exercise->getResourceNode()->setPublished(false);
        $this->exercise->setPublishedOnce(false);
        $this->om->flush();

        $this->manager->publish($this->exercise);

        // Checks published flags
        $this->assertTrue($this->exercise->getResourceNode()->isPublished());
        $this->assertTrue($this->exercise->wasPublishedOnce());

        // Checks papers have been deleted
        $papers = $this->om->getRepository('UJMExoBundle:Attempt\Paper')->findBy([
            'exercise' => $this->exercise,
        ]);

        $this->assertCount(0, $papers);
    }

    public function testUnpublish()
    {
        $this->exercise->getResourceNode()->setPublished(true);
        $this->exercise->setPublishedOnce(true);
        $this->om->flush();

        $this->manager->unpublish($this->exercise);

        $this->assertFalse($this->exercise->getResourceNode()->isPublished());
        $this->assertTrue($this->exercise->wasPublishedOnce());
    }

    /**
     * @return Paper[]
     */
    private function addPapersToExercise()
    {
        $papers = [];

        $paper1 = $this->paperGenerator->create($this->exercise);
        $this->om->persist($paper1);
        $papers[] = $paper1;

        $paper2 = $this->paperGenerator->create($this->exercise);
        $this->om->persist($paper2);
        $papers[] = $paper2;

        return $papers;
    }
}

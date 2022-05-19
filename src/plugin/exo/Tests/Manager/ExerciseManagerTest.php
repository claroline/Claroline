<?php

namespace UJM\ExoBundle\Tests\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Options\ExerciseType;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->om = $this->client->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->persist = new Persister($this->om);
        $this->manager = $this->client->getContainer()->get('UJM\ExoBundle\Manager\ExerciseManager');
        $this->paperGenerator = $this->client->getContainer()->get('ujm_exo.generator.paper');

        $this->exercise = $this->persist->exercise('my exercise', [
            [$this->persist->openQuestion('Open question.')],
            [$this->persist->openQuestion('Open question.')],
        ], $this->persist->user('bob'));
        $this->exercise->setType(ExerciseType::CERTIFICATION);
        $this->om->flush();
    }

    public function testSerialize()
    {
        $data = $this->manager->serialize($this->exercise);

        // Checks the result of the serializer is returned
        $this->assertTrue(is_array($data));
        $this->assertEquals($this->exercise->getUuid(), $data['id']);
    }

    public function testUpdate()
    {
        $validData = $this->loadTestData('exercise/valid/with-steps.json');

        $this->manager->update($this->exercise, $validData);

        // Checks some props
        $this->assertEquals($this->exercise->getType(), $validData['parameters']['type']);
        $this->assertCount($this->exercise->getSteps()->count(), $validData['steps']);
    }

    public function testUpdateWithInvalidData()
    {
        $this->expectException(InvalidDataException::class);

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
        $papers = $this->om->getRepository(Paper::class)->findBy([
            'exercise' => $this->exercise,
        ]);

        /** @var Paper $paper */
        foreach ($papers as $paper) {
            $this->assertTrue($paper->isInvalidated());
        }
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
     * An exercise MUST deletable if there are papers but quiz is not a certification.
     */
    public function testIsDeletableIfNotCertificationAndPapers()
    {
        $this->addPapersToExercise();
        $this->exercise->getResourceNode()->setPublished(true);
        $this->exercise->setType(ExerciseType::FORMATIVE);
        $this->om->flush();

        $this->assertTrue($this->manager->isDeletable($this->exercise));
    }

    /**
     * An exercise MUST NOT be deletable if it has papers and is certification.
     */
    public function testIsNotDeletableIfCertificationAndPapers()
    {
        $this->addPapersToExercise();
        $this->exercise->getResourceNode()->setPublished(true);
        $this->om->flush();

        $this->assertFalse($this->manager->isDeletable($this->exercise));
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

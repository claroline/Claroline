<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\DataFixtures\LoadOptionsData;

class ApiManagerTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var QuestionManager */
    private $manager;
    /** @var string */
    private $formatDir;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->manager = $this->client->getContainer()->get('ujm.exo.question_manager');
        $this->formatDir = realpath(__DIR__ . '/../../../../../../json-quiz/json-quiz/format');
    }

    /**
     * Choice question
     * @expectedException \UJM\ExoBundle\Transfer\Json\ValidationException
     */
    public function testImportQuestionThrowsOnValidationError()
    {
        $data = file_get_contents("{$this->formatDir}/question/choice/examples/invalid/no-solution-score.json");
        $this->manager->importQuestion(json_decode($data));
    }
    
     /**
     * Match question
     * @expectedException \UJM\ExoBundle\Transfer\Json\ValidationException
     */
    public function testImportMatchQuestionWithoutSolutionScoreThrowsOnValidationError()
    {
        $data = file_get_contents("{$this->formatDir}/question/match/examples/invalid/no-solution-score.json");
        $this->manager->importQuestion(json_decode($data));
    }

    /**
     * @dataProvider validQcmQuestionProvider
     * @param string $dataFilename
     */
    public function testQcmQuestionRoundTrip($dataFilename)
    {
        $this->loadQuestionTypeFixture();

        $originalCompData = $this->loadData("question/valid/complete/{$dataFilename}");
        $originalEvalData = $this->loadData("question/valid/evaluation/{$dataFilename}");

        $this->manager->importQuestion($originalCompData);

        $questions = $this->om->getRepository('UJMExoBundle:Question')->findAll();
        $this->assertEquals(1, count($questions), 'Expected one (and only one) question to be created');

        $exportedCompData = $this->manager->exportQuestion($questions[0], true);
        $exportedEvalData = $this->manager->exportQuestion($questions[0], false);

        $this->assertEqualsWithoutIds($originalCompData, $exportedCompData);
        $this->assertEqualsWithoutIds($originalEvalData, $exportedEvalData);
        $this->assertQuestionIdConsistency($exportedCompData);
        $this->assertQuestionIdConsistency($exportedEvalData);
        $this->assertQcmIdConsistency($exportedCompData);
        $this->assertQcmIdConsistency($exportedEvalData);
    }

    public function validQcmQuestionProvider()
    {
        return [
          ['qcm-1'],
          ['qcm-2'],
          ['qcm-3'],
          ['qcm-4'],
          ['qcm-5']
        ];
    }
    
     /**
     * @dataProvider validMatchQuestionProvider
     * @param string $dataFilename
     */
    public function testMatchQuestionRoundTrip($dataFilename)
    {
        $this->loadQuestionTypeFixture();

        $originalCompData = $this->loadData("question/valid/complete/{$dataFilename}");
        $originalEvalData = $this->loadData("question/valid/evaluation/{$dataFilename}");

        $this->manager->importQuestion($originalCompData);

        $questions = $this->om->getRepository('UJMExoBundle:Question')->findAll();
        $this->assertEquals(1, count($questions), 'Expected one (and only one) question to be created');

        $exportedCompData = $this->manager->exportQuestion($questions[0], true);
        $exportedEvalData = $this->manager->exportQuestion($questions[0], false);

        $this->assertEqualsWithoutIds($originalCompData, $exportedCompData);
        //die;
        //$this->assertEqualsWithoutIds($originalEvalData, $exportedEvalData);
        //$this->assertQuestionIdConsistency($exportedCompData);
        //$this->assertQuestionIdConsistency($exportedEvalData);
        //$this->assertQcmIdConsistency($exportedCompData);
        //$this->assertQcmIdConsistency($exportedEvalData);
    }

    
    public function validMatchQuestionProvider()
    {
        return [
          ['match-1'],
          //['qcm-2'],
          //['qcm-3'],
          //['qcm-4'],
          //['qcm-5']
        ];
    }

    private function loadQuestionTypeFixture()
    {
        $fixture = new LoadOptionsData();
        $fixture->load($this->om);
    }

    private function loadData($fileRelativeName) {
        $file = realpath(__DIR__ . "/../Data/json/{$fileRelativeName}.json");

        return json_decode(file_get_contents($file));
    }

    /**
     * Ensures two objects are identical except for the value
     * any of their "id" properties. The exception also applies
     * to nested objects.
     *
     * @param \stdClass $expected
     * @param \stdClass $actual
     * @param null $msg
     */
    private function assertEqualsWithoutIds(\stdClass $expected, \stdClass $actual, $msg = null)
    {
        // shortcut for deep copies (clone will keep nested references)
        $expectedCopy = json_decode(json_encode($expected));
        $actualCopy = json_decode(json_encode($actual));
       
        $removeIds = function (\stdClass $object) use (&$removeIds) {
            foreach (get_object_vars($object) as $property => $value) {
                if ($property === 'id') {
                    unset($object->id);
                } elseif ($property === 'firstId'){
                    unset($object->firstId);
                } elseif ($property === 'secondId'){
                    unset($object->secondId);
                } elseif (is_object($value)) {
                    $removeIds($value);
                } elseif (is_array($value)) {
                    foreach ($value as $key => $element) {
                        $removeIds($element);
                    }
                }
            }
        };

        $removeIds($expectedCopy);
        $removeIds($actualCopy);  

        $this->assertEquals($expectedCopy, $actualCopy, $msg);
    }

    /**
     * Ensures id properties common to all question (e.g. in hints).
     *
     * @param \stdClass $data
     */
    private function assertQuestionIdConsistency(\stdClass $data)
    {
        if (isset($data->hints)) {
            $this->assertDistinctStringIds($data->hints);
        }
    }

    /**
     * Ensures id properties specific to the qcm (e.g. in choices
     * and solutions) are consistent.
     *
     * @param \stdClass $data
     */
    private function assertQcmIdConsistency(\stdClass $data)
    {
        $this->assertDistinctStringIds($data->choices);

        if (isset($data->solutions)) {
            $this->assertDistinctStringIds($data->solutions);

            foreach ($data->solutions as $solution) {
                $this->assertContainsId($data->choices, $solution->id);
            }
        }
    }

    /**
     * Ensures the id property of each element of a collection
     * is a distinct string.
     *
     * @param array $collection
     */
    private function assertDistinctStringIds(array $collection)
    {
        $collectedIds = [];

        foreach ($collection as $object) {
            $this->assertEquals('string', gettype($object->id));
            $this->assertFalse(in_array($object->id, $collectedIds));
            $collectedIds[] = $object->id;
        }
    }

    /**
     * Ensures a collection contains an element with a given
     * id property.
     *
     * @param array $collection
     * @param $id
     */
    private function assertContainsId(array $collection, $id)
    {
        foreach ($collection as $object) {
            if ($object->id === $id) {
                return;
            }
        }

        $this->assertTrue(false, "Failed asserting that collection contains {$id}");
    }
}

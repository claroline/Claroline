<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\DataFixtures\LoadOptionsData;
use UJM\ExoBundle\Transfer\Json\Validator;

class ApiManagerTest extends TransactionalTestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ApiManager
     */
    private $manager;

    /**
     * @var string
     */
    private $formatDir;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->validator = new Validator();
        $this->manager = new ApiManager($this->om, $this->validator);
        $this->formatDir = realpath(__DIR__ . '/../../../../../../json-quiz/json-quiz/format');
    }

    /**
     * @expectedException \UJM\ExoBundle\Transfer\Json\ValidationException
     */
    public function testImportQuestionThrowsIfValidationError()
    {
        $data = file_get_contents("{$this->formatDir}/question/choice/examples/invalid/no-solution-score.json");
        $this->manager->importQuestion(json_decode($data));
    }

    /**
     * @expectedException \UJM\ExoBundle\Transfer\Json\ValidationException
     */
    public function testImportExerciseThrowsIfValidationError()
    {
        $data = file_get_contents("{$this->formatDir}/quiz/examples/invalid/no-steps.json");
        $this->manager->importExercise($data);
    }

    /**
     * @dataProvider validQcmQuestionProvider
     * @param string $dataFilename
     */
    public function testQcmQuestionRoundTrip($dataFilename)
    {
        $this->loadQuestionTypeFixture();

        $compFile = __DIR__ . "/data/choice/{$dataFilename}-comp.json";
        $evalFile = __DIR__ . "/data/choice/{$dataFilename}-eval.json";
        $originalCompData = json_decode(file_get_contents($compFile));
        $originalEvalData = json_decode(file_get_contents($evalFile));
        $this->manager->importQuestion($originalCompData);

        $questions = $this->om->getRepository('UJMExoBundle:Question')->findAll();
        $this->assertEquals(1, count($questions), 'Expected one (and only one) question to be created');

        $exportedCompData = $this->manager->exportQuestion($questions[0], true);
        $exportedEvalData = $this->manager->exportQuestion($questions[0], false);

        $this->assertEqualsWithoutIds($originalCompData, $exportedCompData);
        $this->assertEqualsWithoutIds($originalEvalData, $exportedEvalData);
    }

    /**
     * @dataProvider validQuizProvider
     * @param string $dataFilename
     */
    public function testSchemaRoundTrip($dataFilename)
    {
        $this->markTestIncomplete();
        $data = file_get_contents("{$this->formatDir}/quiz/examples/valid/{$dataFilename}");
        $this->manager->importExercise($data);
    }

    public function validQcmQuestionProvider()
    {
        return [
          ['qcm-1'],
          ['qcm-2']
        ];
    }

    public function validQuizProvider()
    {
        return [
            ['quiz-metadata.json']
        ];
    }

    private function assertEqualsWithoutIds(\stdClass $expected, \stdClass $actual)
    {
        $expectedCopy = clone $expected;
        $actualCopy = clone $actual;

        $removeIds = function (\stdClass $object) use (&$removeIds) {
            foreach (get_object_vars($object) as $property => $value) {
                if ($property === 'id') {
                    unset($object->id);
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

        $this->assertEquals($expectedCopy, $actualCopy);
    }

    private function loadQuestionTypeFixture()
    {
        $fixture = new LoadOptionsData();
        $fixture->load($this->om);
    }
}

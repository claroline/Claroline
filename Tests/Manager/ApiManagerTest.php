<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
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
        $this->manager->importQuestion($data);
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
     * @dataProvider validQuestionProvider
     * @param string $dataFilename
     */
    public function testQuestionRoundTrip($dataFilename)
    {
        $this->markTestIncomplete();
        $data = file_get_contents("{$this->formatDir}/question/{$dataFilename}");
        $this->manager->importQuestion($data);
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

    public function validQuestionProvider()
    {
        return [
            ['choice/examples/valid/extended.json']
        ];
    }

    public function validQuizProvider()
    {
        return [
            ['quiz-metadata.json']
        ];
    }
}

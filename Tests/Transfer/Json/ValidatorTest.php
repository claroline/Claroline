<?php

namespace UJM\ExoBundle\Transfer\Json;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ValidatorTest extends TransactionalTestCase
{
    private $validator;
    private $formatDir;

    protected function setUp()
    {
        parent::setUp();
        $this->validator = $this->client->getContainer()->get('ujm.exo.json_validator');
        $this->formatDir = realpath(__DIR__ . '/../../../../../../../json-quiz/json-quiz/format');
    }

    public function testValidateQuestionWithNoType()
    {
        $errors = $this->validator->validateQuestion(new \stdClass());
        $expected = [[
            'property' => '',
            'message' => 'Question cannot be validated due to missing property "type"'
        ]];
        $this->assertEquals($expected, $errors);
    }

    public function testValidateQuestionWithUnknownType()
    {
        $question = new \stdClass();
        $question->type = 'application/x.foo+json';
        $errors = $this->validator->validateQuestion($question);
        $expected = [[
            'property' => 'type',
            'message' => "Unknown question type 'application/x.foo+json'"
        ]];
        $this->assertEquals($expected, $errors);
    }

    public function testInvalidQuestionData()
    {
        $data = file_get_contents("{$this->formatDir}/question/choice/examples/invalid/no-solution-id.json");
        $question = json_decode($data);
        $this->assertGreaterThan(0, count($this->validator->validateQuestion($question)));
    }

    /**
     * @dataProvider validQuestionProvider
     * @param string$dataFilename
     */
    public function testValidQuestionData($dataFilename)
    {
        $data = file_get_contents("{$this->formatDir}/question/$dataFilename");
        $question = json_decode($data);
        $this->assertEquals(0, count($this->validator->validateQuestion($question)));
    }

    public function testValidateExercise()
    {
        $data = file_get_contents("{$this->formatDir}/quiz/examples/valid/one-question-step.json");
        $quiz = json_decode($data);
        $this->assertEquals(0, count($this->validator->validateExercise($quiz)));

        $data = file_get_contents("{$this->formatDir}/quiz/examples/invalid/no-steps.json");
        $quiz = json_decode($data);
        $this->assertGreaterThan(0, count($this->validator->validateExercise($quiz)));
    }

    public function validQuestionProvider()
    {
        return [
            ['choice/examples/valid/extended.json'],
            ['cloze/examples/valid/simple-input.json'],
            ['match/examples/valid/basic.json'],
            ['sort/examples/valid/basic.json'],
        ];
    }
}

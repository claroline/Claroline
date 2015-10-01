<?php

namespace UJM\ExoBundle\Transfer\Json;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $formatDir;

    protected function setUp()
    {
        $this->formatDir = realpath(__DIR__ . '/../../../../../../../json-quiz/json-quiz/format');
    }

    public function testValidateQuestionWithNoType()
    {
        $validator = new Validator();
        $errors = $validator->validateQuestion(new \stdClass());
        $expected = [[
            'property' => '',
            'message' => 'Question cannot be validated due to missing property "type"'
        ]];
        $this->assertEquals($expected, $errors);
    }

    public function testValidateQuestionWithUnknownType()
    {
        $validator = new Validator();
        $question = new \stdClass();
        $question->type = 'application/x.foo+json';
        $errors = $validator->validateQuestion($question);
        $expected = [[
            'property' => 'type',
            'message' => "Unknown question type 'application/x.foo+json'"
        ]];
        $this->assertEquals($expected, $errors);
    }

    public function testInvalidQuestionData()
    {
        $validator = new Validator();
        $data = file_get_contents("{$this->formatDir}/question/choice/examples/invalid/no-solution-id.json");
        $question = json_decode($data);
        $this->assertGreaterThan(0, count($validator->validateQuestion($question)));
    }

    /**
     * @dataProvider validQuestionProvider
     * @param string$dataFilename
     */
    public function testValidQuestionData($dataFilename)
    {
        $validator = new Validator();
        $data = file_get_contents("{$this->formatDir}/question/$dataFilename");
        $question = json_decode($data);
        $this->assertEquals(0, count($validator->validateQuestion($question)));
    }

    public function testValidateExercise()
    {
        $validator = new Validator();

        $data = file_get_contents("{$this->formatDir}/quiz/examples/valid/one-question-step.json");
        $quiz = json_decode($data);
        $this->assertEquals(0, count($validator->validateExercise($quiz)));

        $data = file_get_contents("{$this->formatDir}/quiz/examples/invalid/no-steps.json");
        $quiz = json_decode($data);
        $this->assertGreaterThan(0, count($validator->validateExercise($quiz)));
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

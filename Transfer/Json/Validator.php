<?php

namespace UJM\ExoBundle\Transfer\Json;

use JMS\DiExtraBundle\Annotation as DI;
use JVal\Utils;
use JVal\Validator as SchemaValidator;

/**
 * @DI\Service("ujm.exo.json_validator")
 */
class Validator
{
    private $handlerCollector;
    private $kernelDir;

    /**
     * @DI\InjectParams({
     *     "collector" = @DI\Inject("ujm.exo.question_handler_collector"),
     *     "kernelDir" = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param QuestionHandlerCollector  $collector
     * @param string                    $kernelDir
     */
    public function __construct(QuestionHandlerCollector $collector, $kernelDir)
    {
        $this->handlerCollector = $collector;
        $this->kernelDir = $kernelDir;
    }

    /**
     * Validates a JSON-decoded question against the available
     * question schemas. Returns an array of validation errors.
     *
     * @param \stdClass $question
     * @param bool      $requireSolution
     * @return array
     */
    public function validateQuestion(\stdClass $question, $requireSolution = true)
    {
        if (isset($question->type)) {
            if ($this->handlerCollector->hasHandlerForMimeType($question->type)) {
                $handler = $this->handlerCollector->getHandlerForMimeType($question->type);
                $schema = $this->getSchema($handler->getJsonSchemaUri());
                $validator = $this->getValidator();
                $errors = $validator->validate($question, $schema);

                if ($requireSolution
                    && !isset($question->solution)
                    && !isset($question->solutions)) {
                    $errors[] = [
                        'path' => '',
                        'message' => 'a solution(s) property is required'
                    ];
                }

                if (count($errors) === 0) {
                    $errors = $handler->validateAfterSchema($question);
                }

                return $errors;
            }

            return [[
                'path' => 'type',
                'message' => "Unknown question type '{$question->type}'"
            ]];
        }

        return [[
            'path' => '',
            'message' => 'Question cannot be validated due to missing property "type"'
        ]];
    }

    /**
     * Validates a json-decoded exercise against the quiz schema.
     *
     * @param \stdClass $quiz
     * @return array
     */
    public function validateExercise(\stdClass $quiz)
    {
        $schema = $this->getSchema('http://json-quiz.github.io/json-quiz/schemas/quiz/schema.json');
        $validator = $this->getValidator();

        return $validator->validate($quiz, $schema);
    }

    private function getValidator()
    {
        $hook = function ($uri) {
            return $this->uriToFile($uri);
        };

        return SchemaValidator::buildDefault($hook);
    }

    private function getSchema($uri)
    {
        return Utils::loadJsonFromFile($this->uriToFile($uri));
    }

    private function uriToFile($uri)
    {
        $schemaDir = realpath("{$this->kernelDir}/../vendor/json-quiz/json-quiz/format");
        $baseUri = 'http://json-quiz.github.io/json-quiz/schemas';

        return str_replace($baseUri, $schemaDir, $uri);
    }
}

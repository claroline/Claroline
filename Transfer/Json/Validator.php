<?php

namespace UJM\ExoBundle\Transfer\Json;

use JMS\DiExtraBundle\Annotation as DI;
use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator as SchemaValidator;

/**
 * @DI\Service("ujm.exo.json_validator")
 */
class Validator
{
    private $handlerCollector;

    /**
     * @DI\InjectParams({
     *     "collector" = @DI\Inject("ujm.exo.question_handler_collector")
     * })
     *
     * @param QuestionHandlerCollector $collector
     */
    public function __construct(QuestionHandlerCollector $collector)
    {
        $this->handlerCollector = $collector;
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
            $handler = $this->handlerCollector->getHandlerForMimeType($question->type);

            if ($handler) {
                $schema = $this->getSchema($handler->getJsonSchemaUri(), true);
                $validator = new SchemaValidator();
                $validator->check($question, $schema);
                $errors = $validator->getErrors();

                if ($requireSolution
                    && !isset($question->solution)
                    && !isset($question->solutions)) {
                    $errors[] = [
                        'property' => '',
                        'message' => 'a solution(s) property is required'
                    ];
                }

                return $errors;
            }

            return [[
                'property' => 'type',
                'message' => "Unknown question type '{$question->type}'"
            ]];
        }

        return [[
            'property' => '',
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
        $schema = $this->getSchema('quiz');
        $validator = new SchemaValidator();
        $validator->check($quiz, $schema);

        return $validator->getErrors();
    }

    private function getSchema($schemaPathId, $isUri = false)
    {
        $retriever = new UriRetriever();
        $retriever->setUriRetriever(new LocalSchemaRetriever());

        $schemaUri = !$isUri ?
            LocalSchemaRetriever::uriFromPathId($schemaPathId) :
            $schemaPathId;

        $schema = $retriever->retrieve($schemaUri);
        $refResolver = new RefResolver($retriever);
        $refResolver->resolve($schema);

        return $schema;
    }
}

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
    /**
     * Validates a json-decoded question against the available
     * question schemas. Returns an array of validation errors.
     *
     * @param \stdClass $question
     * @return array
     */
    public function validateQuestion(\stdClass $question)
    {
        if (isset($question->type)) {
            $types = implode('|', ['choice', 'cloze', 'match', 'sort']);
            $typePattern = "#application/x\\.({$types})\\+json#";

            if (preg_match($typePattern, $question->type, $matches)) {
                $schema = $this->getSchema("question/{$matches[1]}");
                $validator = new SchemaValidator();
                $validator->check($question, $schema);

                return $validator->getErrors();
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
        $schema = $this->getSchema('quiz');
        $validator = new SchemaValidator();
        $validator->check($quiz, $schema);

        return $validator->getErrors();
    }

    private function getSchema($schemaPathId)
    {
        $retriever = new UriRetriever();
        $retriever->setUriRetriever(new LocalSchemaRetriever());

        $schemaUri = LocalSchemaRetriever::uriFromPathId($schemaPathId);
        $schema = $retriever->retrieve($schemaUri);

        $refResolver = new RefResolver($retriever);
        $refResolver->resolve($schema);

        return $schema;
    }
}

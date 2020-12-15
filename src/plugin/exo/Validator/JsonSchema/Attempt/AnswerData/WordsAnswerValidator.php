<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class WordsAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/words/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param \stdClass $question
     * @param array     $options
     *
     * @return array
     */
    public function validateAfterSchema($question, array $options = [])
    {
        return [];
    }
}

<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class SelectionAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/selection/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param \stdClass $question
     *
     * @return array
     */
    public function validateAfterSchema($question, array $options = [])
    {
        // Checks the content type of the answer match the content type of the question

        return [];
    }
}

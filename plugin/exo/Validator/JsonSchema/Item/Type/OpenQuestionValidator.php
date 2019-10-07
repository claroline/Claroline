<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class OpenQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/open/schema.json';
    }

    public function validateAfterSchema($question, array $options = [])
    {
        return [];
    }
}

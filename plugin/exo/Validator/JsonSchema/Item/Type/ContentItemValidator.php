<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class ContentItemValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'content/schema.json';
    }

    public function validateAfterSchema($question, array $options = [])
    {
        return [];
    }
}

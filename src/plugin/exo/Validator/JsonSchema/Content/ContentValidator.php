<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Content;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * Validates Content data.
 */
class ContentValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri(): string
    {
        return 'content.json';
    }

    public function validateAfterSchema(mixed $data, array $options = []): array
    {
        return [];
    }
}

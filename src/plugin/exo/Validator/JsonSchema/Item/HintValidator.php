<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * Validates Hint data.
 */
class HintValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri(): string
    {
        return 'hint.json';
    }

    public function validateAfterSchema(mixed $hint, array $options = []): array
    {
        $errors = [];

        if (in_array(Validation::REQUIRE_SOLUTIONS, $options) && empty($hint['value'])) {
            $errors[] = [
                'path' => '/value',
                'message' => 'Hint requires a "value" property',
            ];
        }

        return $errors;
    }
}

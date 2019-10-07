<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Content;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * Validates Content data.
 */
class ContentValidator extends JsonSchemaValidator
{
    /**
     * {@inheritdoc}
     */
    public function getJsonSchemaUri()
    {
        return 'content/schema.json';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAfterSchema($content, array $options = [])
    {
        return [];
    }
}

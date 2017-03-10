<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Content;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * Validates Content data.
 *
 * @DI\Service("ujm_exo.validator.content")
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

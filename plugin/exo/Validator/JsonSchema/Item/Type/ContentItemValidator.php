<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.item_content")
 */
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

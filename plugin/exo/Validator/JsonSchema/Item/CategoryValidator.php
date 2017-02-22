<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.category")
 */
class CategoryValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'category/schema.json';
    }

    public function validateAfterSchema($data, array $options = [])
    {
        return [];
    }
}

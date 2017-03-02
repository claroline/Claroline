<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.question_open")
 */
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

<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("ujm_exo.validator.question_boolean")
 */
class BooleanQuestionValidator extends ChoiceQuestionValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/boolean/schema.json';
    }
}

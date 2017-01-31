<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.answer_graphic")
 */
class GraphicAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/graphic/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param \stdClass $answerData
     * @param array     $options
     *
     * @return array
     */
    public function validateAfterSchema($answerData, array $options = [])
    {
        $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;

        if (empty($question)) {
            throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
        }

        $image = $question->getImage();
        $errors = [];

        foreach ($answerData as $index => $coords) {
            if ($coords->x > $image->getWidth()) {
                $errors[] = [
                    'path' => "/[{$index}].x",
                    'message' => 'Position exceeds image width',
                ];
            }
            if ($coords->y > $image->getHeight()) {
                $errors[] = [
                    'path' => "/[{$index}].y",
                    'message' => 'Position exceeds image height',
                ];
            }
        }

        return $errors;
    }
}

<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.question_selection")
 */
class SelectionQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/selection/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param \stdClass $question
     * @param array     $options
     *
     * @return array
     */
    public function validateAfterSchema($question, array $options = [])
    {
        $errors = [];

        if (in_array(Validation::REQUIRE_SOLUTIONS, $options)) {
            $errors = $this->validateSolutions($question);
        }

        return $errors;
    }

    /**
     * Validates the solution of the question.
     *
     * @param \stdClass $question
     *
     * @return array
     */
    protected function validateSolutions(\stdClass $question)
    {
        $errors = [];

        if ($question->mode === 'highlight' || $question->mode === 'select') {
            // check solution IDs are consistent with selectionId IDs
            $selectionIds = array_map(function (\stdClass $selection) {
                return $selection->id;
            }, $question->selections);

            if (count($question->selections) !== count($question->solutions)) {
                $errors[] = [
                    'path' => '/solutions',
                    'message' => 'there must be the same number of solutions and selections',
                ];
            }

            foreach ($question->solutions as $index => $solution) {
                if (!in_array($solution->selectionId, $selectionIds)) {
                    $errors[] = [
                        'path' => "/solutions[{$index}]",
                        'message' => "id {$solution->selectionId} doesn't match any selection id",
                    ];
                }
            }
        }

        if ($question->mode === 'highlight') {
            // check solution IDs are consistent with selectionId IDs
            $colorIds = array_map(function (\stdClass $color) {
                return $color->id;
            }, $question->colors);

            foreach ($question->solutions as $index => $solution) {
                foreach ($solution->answers as $ianswer => $answer) {
                    if (!in_array($answer->colorId, $colorIds)) {
                        $errors[] = [
                            'path' => "/solutions[{$index}]/answers[$ianswer]",
                            'message' => "id {$answer->colorId} doesn't match any color id",
                        ];
                    }
                }
            }
        }

        return $errors;
    }
}

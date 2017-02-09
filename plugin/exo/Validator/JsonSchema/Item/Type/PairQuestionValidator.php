<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.question_pair")
 */
class PairQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/pair/schema.json';
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

        $itemIds = [];
        // there is not 2 items at the same coords
        $usedCoords = [];
        foreach ($question->items as $index => $item) {
            $itemIds[] = $item->id;
            if (!empty($item->coordinates)) {
                if (in_array($item->coordinates, $usedCoords)) {
                    $errors[] = [
                        'path' => "/items[{$index}]",
                        'message' => 'two items cannot be pinned at the same coordinates.',
                    ];
                    break;
                }

                // max Y coordinate < rows
                if ($question->rows <= $item->coordinates[1]) {
                    $errors[] = [
                        'path' => "/items[{$index}]",
                        'message' => 'pinned items must be in the grid.',
                    ];
                    break;
                }

                $usedCoords[] = $item->coordinates;
            }
        }

        if (empty($errors)) {
            // no shuffle if no item pinned
            if (empty($usedCoords) && $question->random) {
                $errors[] = [
                    'path' => '/random',
                    'message' => 'you must pin at least one item to use random.',
                ];
            }

            // solutions references
            foreach ($question->solutions as $indexSolution => $solution) {
                foreach ($solution->itemIds as $index => $item) {
                    if (!in_array($item, $itemIds)) {
                        $errors[] = [
                            'path' => "solutions[{$indexSolution}]/itemIds[{$index}]",
                            'message' => 'solution itemIds must reference question items.',
                        ];
                    }
                }
            }
        }

        return $errors;
    }
}

<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class SetQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/set/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param array $question
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
     * Checks :
     *  - The solution `itemId` must match the `items` IDs.
     *  - The solution `setId` must match the `sets` IDs.
     *  - An odd `itemId` must match the `items` IDS.
     *  - There is at least one solution with a positive score.
     *
     * @return array
     */
    protected function validateSolutions(array $question)
    {
        $errors = [];

        // check solution IDs are consistent with set IDs
        $setIds = array_map(function (array $set) {
            return $set['id'];
        }, $question['sets']);

        // check solution IDs are consistent with member IDs
        $itemIds = array_map(function (array $item) {
            return $item['id'];
        }, $question['items']);

        // Validate associations
        if (!empty($question['solutions']['associations'])) {
            $maxScore = -1;
            foreach ($question['solutions']['associations'] as $index => $association) {
                if (!in_array($association['setId'], $setIds)) {
                    $errors[] = [
                        'path' => "/solutions/associations[{$index}]",
                        'message' => "id {$association['setId']} doesn't match any set id",
                    ];
                }

                if (!in_array($association['itemId'], $itemIds)) {
                    $errors[] = [
                        'path' => "/solutions/associations[{$index}]",
                        'message' => "id {$association['itemId']} doesn't match any item id",
                    ];
                }

                if ($association['score'] > $maxScore) {
                    $maxScore = $association['score'];
                }
            }

            // Checks there is a positive score solution
            if ($maxScore <= 0) {
                $errors[] = [
                    'path' => '/solutions',
                    'message' => 'There is no solution with a positive score',
                ];
            }
        }

        // Validate odd
        if (!empty($question['solutions']['odd'])) {
            foreach ($question['solutions']['odd'] as $index => $odd) {
                if (!in_array($odd['itemId'], $itemIds)) {
                    $errors[] = [
                        'path' => "/solutions/odd[{$index}]",
                        'message' => "id {$odd['itemId']} doesn't match any item id",
                    ];
                }
            }
        }

        return $errors;
    }
}

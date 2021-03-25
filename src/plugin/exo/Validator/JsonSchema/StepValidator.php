<?php

namespace UJM\ExoBundle\Validator\JsonSchema;

use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;
use UJM\ExoBundle\Validator\JsonSchema\Content\ContentValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator;

class StepValidator extends JsonSchemaValidator
{
    /**
     * @var ItemValidator
     */
    private $itemValidator;

    /**
     * @var ContentValidator
     */
    private $contentValidator;

    /**
     * StepValidator constructor.
     */
    public function __construct(
        ItemValidator $itemValidator,
        ContentValidator $contentValidator)
    {
        $this->itemValidator = $itemValidator;
        $this->contentValidator = $contentValidator;
    }

    public function getJsonSchemaUri()
    {
        return 'step/schema.json';
    }

    public function validateAfterSchema($step, array $options = [])
    {
        $errors = [];

        if (isset($step['parameters'])) {
            $errors = array_merge($errors, $this->validateParameters($step['parameters']));
        }

        if (isset($step['picking'])) {
            $errors = array_merge($errors, $this->validatePicking($step['picking']));
            if (isset($step['picking']['pick']) && isset($step['items'])
                && count($step['items']) < $step['picking']['pick']) {
                $errors[] = [
                    'path' => '/picking/pick',
                    'message' => 'the property `pick` cannot be greater than the number of items of the step',
                ];
            }
        }

        if (isset($step['items'])) {
            // Apply custom validation to step items
            array_map(function (array $item) use (&$errors, $options) {
                if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $item['type'])) {
                    $validationOptions = $options;

                    // Item is a Question
                    if (!isset($item['hasExpectedAnswers']) || !$item['hasExpectedAnswers']) {
                        $solutionsKey = array_search(Validation::REQUIRE_SOLUTIONS, $validationOptions);

                        if (false !== $solutionsKey) {
                            unset($validationOptions[$solutionsKey]);
                        }
                    }
                    $itemErrors = $this->itemValidator->validateAfterSchema($item, $validationOptions);
                } else {
                    // Item is a Content
                    $itemErrors = $this->contentValidator->validateAfterSchema($item, $options);
                }

                if (!empty($itemErrors)) {
                    $errors = array_merge($errors, $itemErrors);
                }
            }, $step['items']);
        }

        return $errors;
    }

    private function validateParameters(array $parameters)
    {
        return [];
    }

    private function validatePicking(array $picking)
    {
        $errors = [];

        if (isset($picking['randomPick']) && Recurrence::NEVER !== $picking['randomPick'] && !isset($picking['pick'])) {
            // Random pick is enabled but the number of steps to pick is missing
            $errors[] = [
                'path' => '/picking/randomPick',
                'message' => 'The property `pick` is required when `randomPick` is not "never"',
            ];
        }

        // We can not keep the randomOrder from previous papers as we generate a new subset of items for each attempt
        if (isset($picking['randomPick']) && Recurrence::ALWAYS === $picking['randomPick']
            && isset($picking['randomOrder']) && Recurrence::ONCE === $picking['randomOrder']) {
            // Incompatible randomOrder and randomPick properties
            $errors[] = [
                'path' => '/picking/randomOrder',
                'message' => 'The property `randomOrder` cannot be "once" when `randomPick` is "always"',
            ];
        }

        return $errors;
    }
}

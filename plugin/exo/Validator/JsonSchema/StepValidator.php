<?php

namespace UJM\ExoBundle\Validator\JsonSchema;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;
use UJM\ExoBundle\Validator\JsonSchema\Content\ContentValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator;

/**
 * @DI\Service("ujm_exo.validator.step")
 */
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
     *
     * @param ItemValidator    $itemValidator
     * @param ContentValidator $contentValidator
     *
     * @DI\InjectParams({
     *     "itemValidator" = @DI\Inject("ujm_exo.validator.item"),
     *     "contentValidator"  = @DI\Inject("ujm_exo.validator.content")
     * })
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

        if (isset($step->parameters)) {
            $errors = array_merge($errors, $this->validateParameters($step->parameters));
            if (isset($step->parameters->pick) && isset($step->items)
                && count($step->items) < $step->parameters->pick) {
                $errors[] = [
                    'path' => '/parameters/pick',
                    'message' => 'the property `pick` cannot be greater than the number of items of the step',
                ];
            }
        }

        if (isset($step->items)) {
            // Apply custom validation to step items
            array_map(function (\stdClass $item) use (&$errors, $options) {
                if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $item->type)) {
                    // Item is a Question
                    $itemErrors = $this->itemValidator->validateAfterSchema($item, $options);
                } else {
                    // Item is a Content
                    $itemErrors = $this->contentValidator->validateAfterSchema($item, $options);
                }

                if (!empty($itemErrors)) {
                    $errors = array_merge($errors, $itemErrors);
                }
            }, $step->items);
        }

        return $errors;
    }

    private function validateParameters(\stdClass $parameters)
    {
        $errors = [];

        if (isset($parameters->randomPick) && Recurrence::NEVER !== $parameters->randomPick && !isset($parameters->pick)) {
            // Random pick is enabled but the number of steps to pick is missing
            $errors[] = [
                'path' => '/parameters/randomPick',
                'message' => 'The property `pick` is required when `randomPick` is not "never"',
            ];
        }

        // We can not keep the randomOrder from previous papers as we generate a new subset of items for each attempt
        if (isset($parameters->randomPick) && Recurrence::ALWAYS === $parameters->randomPick
            && isset($parameters->randomOrder) && Recurrence::ONCE === $parameters->randomOrder) {
            // Incompatible randomOrder and randomPick properties
            $errors[] = [
                'path' => '/parameters/randomOrder',
                'message' => 'The property `randomOrder` cannot be "once" when `randomPick` is "always"',
            ];
        }

        return $errors;
    }
}

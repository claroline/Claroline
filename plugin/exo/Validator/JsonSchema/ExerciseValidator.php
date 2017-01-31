<?php

namespace UJM\ExoBundle\Validator\JsonSchema;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\ShowCorrectionAt;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.exercise")
 */
class ExerciseValidator extends JsonSchemaValidator
{
    /**
     * @var StepValidator
     */
    private $stepValidator;

    /**
     * ExerciseValidator constructor.
     *
     * @param StepValidator $stepValidator
     *
     * @DI\InjectParams({
     *     "stepValidator" = @DI\Inject("ujm_exo.validator.step")
     * })
     */
    public function __construct(StepValidator $stepValidator)
    {
        $this->stepValidator = $stepValidator;
    }

    public function getJsonSchemaUri()
    {
        return 'quiz/schema.json';
    }

    public function validateAfterSchema($exercise, array $options = [])
    {
        $errors = [];

        if (isset($exercise->parameters)) {
            $errors = array_merge($errors, $this->validateParameters($exercise->parameters));
            if (isset($exercise->parameters->pick) && isset($exercise->steps)
                && count($exercise->steps) < $exercise->parameters->pick) {
                $errors[] = [
                    'path' => '/parameters/pick',
                    'message' => 'the property `pick` cannot be greater than the number of steps of the exercise',
                ];
            }
        }

        if (isset($exercise->steps)) {
            // Apply custom validation to step items
            array_map(function ($step) use (&$errors, $options) {
                $errors = array_merge($errors, $this->stepValidator->validateAfterSchema($step, $options));
            }, $exercise->steps);
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

        // We can not keep the randomOrder from previous papers as we generate a new subset of steps for each attempt
        if (isset($parameters->randomPick) && Recurrence::ALWAYS === $parameters->randomPick
            && isset($parameters->randomOrder) && Recurrence::ONCE === $parameters->randomOrder) {
            // Incompatible randomOrder and randomPick properties
            $errors[] = [
                'path' => '/parameters/randomOrder',
                'message' => 'The property `randomOrder` cannot be "once" when `randomPick` is "always"',
            ];
        }

        if (isset($parameters->showCorrectionAt) && ShowCorrectionAt::AFTER_DATE === $parameters->showCorrectionAt && empty($parameters->correctionDate)) {
            // Correction is shown at a date, but the date is not specified
            $errors[] = [
                'path' => '/parameters/correctionDate',
                'message' => 'The property `correctionDate` is required when `showCorrectionAt` is "date"',
            ];
        }

        if (isset($parameters->correctionDate)) {
            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters->correctionDate);
            if (!$dateTime || $dateTime->format('Y-m-d\TH:i:s') !== $parameters->correctionDate) {
                $errors[] = [
                    'path' => '/parameters/correctionDate',
                    'message' => 'Invalid date format',
                ];
            }
        }

        return $errors;
    }
}

<?php

namespace UJM\ExoBundle\Validator\JsonSchema;

use UJM\ExoBundle\Library\Options\Picking;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\ShowCorrectionAt;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class ExerciseValidator extends JsonSchemaValidator
{
    /**
     * @var StepValidator
     */
    private $stepValidator;

    /**
     * ExerciseValidator constructor.
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

        if (isset($exercise['parameters'])) {
            $errors = array_merge($errors, $this->validateParameters($exercise['parameters']));
        }

        if (isset($exercise['picking'])) {
            $errors = array_merge($errors, $this->validatePicking($exercise['picking']));
            if (Picking::STANDARD === $exercise['picking']['type']
                && isset($exercise['picking']['pick']) && isset($exercise['steps'])
                && count($exercise['steps']) < $exercise['picking']['pick']) {
                $errors[] = [
                    'path' => '/picking/pick',
                    'message' => 'the property `pick` cannot be greater than the number of steps of the exercise',
                ];
            }
        }

        if (isset($exercise['steps'])) {
            // Apply custom validation to step items
            array_map(function ($step) use (&$errors, $options) {
                $errors = array_merge($errors, $this->stepValidator->validateAfterSchema($step, $options));
            }, $exercise['steps']);
        }

        return $errors;
    }

    private function validateParameters(array $parameters)
    {
        $errors = [];

        if (isset($parameters['showCorrectionAt']) && ShowCorrectionAt::AFTER_DATE === $parameters['showCorrectionAt'] && empty($parameters['correctionDate'])) {
            // Correction is shown at a date, but the date is not specified
            $errors[] = [
                'path' => '/parameters/correctionDate',
                'message' => 'The property `correctionDate` is required when `showCorrectionAt` is "date"',
            ];
        }

        if (isset($parameters['correctionDate'])) {
            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters['correctionDate']);
            if (!$dateTime || $dateTime->format('Y-m-d\TH:i:s') !== $parameters['correctionDate']) {
                $errors[] = [
                    'path' => '/parameters/correctionDate',
                    'message' => 'Invalid date format',
                ];
            }
        }

        return $errors;
    }

    private function validatePicking(array $picking)
    {
        $errors = [];

        if (Picking::STANDARD === $picking['type']) {
            if (isset($picking['randomPick']) && Recurrence::NEVER !== $picking['randomPick'] && !isset($picking['pick'])) {
                // Random pick is enabled but the number of steps to pick is missing
                $errors[] = [
                    'path' => '/picking/randomPick',
                    'message' => 'The property `pick` is required when `randomPick` is not "never"',
                ];
            }

            // We can not keep the randomOrder from previous papers as we generate a new subset of steps for each attempt
            if (isset($picking['randomPick']) && Recurrence::ALWAYS === $picking['randomPick']
                && isset($picking['randomOrder']) && Recurrence::ONCE === $picking['randomOrder']) {
                // Incompatible randomOrder and randomPick properties
                $errors[] = [
                    'path' => '/picking/randomOrder',
                    'message' => 'The property `randomOrder` cannot be "once" when `randomPick` is "always"',
                ];
            }
        } elseif (Picking::TAGS === $picking['type']) {
            if (Recurrence::NEVER === $picking['randomPick']) {
                $errors[] = [
                    'path' => '/picking/randomPick',
                    'message' => 'The property `randomPick` cannot be "never" when picking `type` is "tags"',
                ];
            }
        }

        return $errors;
    }
}

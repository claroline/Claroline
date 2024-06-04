<?php

namespace UJM\ExoBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;

class QuizValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ExerciseValidator $validator
    ) {
    }

    public function validate(array $data, string $mode, array $options = []): array
    {
        $validationOptions = [];
        $dataToValidate = $this->removeUnexpectedSolutions($data);

        if (!empty($data['parameters']) && !empty($data['parameters']['hasExpectedAnswers'])) {
            $validationOptions[] = Validation::REQUIRE_SOLUTIONS;
        }

        // forward validation to the old JSONSchema validator
        return $this->validator->validate($dataToValidate, $validationOptions);
    }

    public function getUniqueFields(): array
    {
        return [
            'code' => 'code',
        ];
    }

    public static function getClass(): string
    {
        return Exercise::class;
    }

    private function removeUnexpectedSolutions($data)
    {
        $newData = $data;

        if (isset($newData['steps'])) {
            foreach ($newData['steps'] as $stepIdx => $step) {
                if (isset($step['items'])) {
                    foreach ($step['items'] as $itemIdx => $item) {
                        if (isset($item['solutions']) && isset($item['hasExpectedAnswers']) && !$item['hasExpectedAnswers']) {
                            unset($newData['steps'][$stepIdx]['items'][$itemIdx]['solutions']);
                        }
                    }
                }
            }
        }

        return $newData;
    }
}

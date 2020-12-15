<?php

namespace Claroline\AudioPlayerBundle\Validator\Quiz\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class WaveformQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/waveform/schema.json';
    }

    public function validateAfterSchema($question, array $options = [])
    {
        $errors = [];

        if (in_array(Validation::REQUIRE_SOLUTIONS, $options)) {
            $errors = $this->validateSolutions($question);
        }

        return $errors;
    }

    /**
     * Checks :
     *  - There is at least one solution with a positive score.
     *
     * @param array $question
     *
     * @return array
     */
    protected function validateSolutions(array $question)
    {
        $errors = [];

        $maxScore = -1;

        foreach ($question['solutions'] as $solution) {
            if ($solution['score'] > $maxScore) {
                $maxScore = $solution['score'];
            }
        }

        // check there is a positive score solution
        if ($maxScore <= 0) {
            $errors[] = [
                'path' => '/solutions',
                'message' => 'There is no solution with a positive score',
            ];
        }

        return $errors;
    }
}

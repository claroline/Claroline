<?php

namespace Claroline\AudioPlayerBundle\Validator\Quiz\JsonSchema\Attempt\AnswerData;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("claroline.validator.audio.answer_waveform")
 */
class WaveformAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/waveform/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param array $answerData
     * @param array $options
     *
     * @return array
     */
    public function validateAfterSchema($answerData, array $options = [])
    {
        $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;

        if (empty($question)) {
            throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
        }

        $errors = [];
        $done = [];

        foreach ($answerData as $i => $sectionA) {
            $startA = isset($sectionA['startTolerance']) ? $sectionA['start'] - $sectionA['startTolerance'] : $sectionA['start'];
            $endA = isset($sectionA['endTolerance']) ? $sectionA['end'] + $sectionA['endTolerance'] : $sectionA['end'];

            foreach ($answerData as $j => $sectionB) {
                if ($i !== $j && !isset($done[$j])) {
                    $startB = isset($sectionB['startTolerance']) ? $sectionB['start'] - $sectionB['startTolerance'] : $sectionB['start'];
                    $endB = isset($sectionB['endTolerance']) ? $sectionB['end'] + $sectionB['endTolerance'] : $sectionB['end'];

                    if ($startA >= $startB && $startA <= $endB) {
                        $errors[] = [
                            'path' => "/[{$i}].start",
                            'message' => "Start position of [{$i}] is in [{$j}]",
                        ];
                    }
                    if ($endA >= $startB && $endA <= $endB) {
                        $errors[] = [
                            'path' => "/[{$i}].end",
                            'message' => "End position of [{$i}] is in [{$j}]",
                        ];
                    }
                    if ($startA < $startB && $endA > $endB) {
                        $errors[] = [
                            'path' => "/[{$i}]",
                            'message' => "Section [{$i}] is overlayed by [{$j}]",
                        ];
                    }
                }
            }
            $done[$i] = true;
        }

        return $errors;
    }
}

<?php

namespace Claroline\AudioPlayerBundle\Library\Quiz\Item\Definition;

use Claroline\AudioPlayerBundle\Entity\Quiz\ItemType\WaveformQuestion;
use Claroline\AudioPlayerBundle\Entity\Quiz\Misc\Section;
use Claroline\AudioPlayerBundle\Serializer\Quiz\WaveformQuestionSerializer;
use Claroline\AudioPlayerBundle\Validator\Quiz\JsonSchema\Attempt\AnswerData\WaveformAnswerValidator;
use Claroline\AudioPlayerBundle\Validator\Quiz\JsonSchema\Item\Type\WaveformQuestionValidator;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\Definition\AbstractDefinition;

/**
 * Waveform question definition.
 */
class WaveformDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly WaveformQuestionValidator $validator,
        private readonly WaveformAnswerValidator $answerValidator,
        private readonly WaveformQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return 'application/x.waveform+json';
    }

    public static function getEntityClass(): string
    {
        return WaveformQuestion::class;
    }

    protected function getQuestionValidator(): WaveformQuestionValidator
    {
        return $this->validator;
    }

    protected function getAnswerValidator(): WaveformAnswerValidator
    {
        return $this->answerValidator;
    }

    protected function getQuestionSerializer(): WaveformQuestionSerializer
    {
        return $this->serializer;
    }

    /**
     * @param WaveformQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
    {
        $corrected = new CorrectedAnswer();

        /** @var Section $section */
        foreach ($question->getSections() as $section) {
            if (is_array($answer)) {
                $found = false;

                foreach ($answer as $selection) {
                    if ($selection['start'] >= $section->getStart() - $section->getStartTolerance()
                        && $selection['start'] <= $section->getStart() + $section->getStartTolerance()
                        && $selection['end'] >= $section->getEnd() - $section->getEndTolerance()
                        && $selection['end'] <= $section->getEnd() + $section->getEndTolerance()
                    ) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $section->getScore() > 0 ? $corrected->addExpected($section) : $corrected->addUnexpected($section);
                } elseif ($section->getScore() > 0) {
                    $corrected->addMissing($section);
                }
            } elseif ($section->getScore() > 0) {
                $corrected->addMissing($section);
            }
        }
        if (is_array($answer) && $question->getPenalty()) {
            foreach ($answer as $selection) {
                $found = false;

                foreach ($question->getSections() as $section) {
                    if ($selection['start'] >= $section->getStart() - $section->getStartTolerance()
                        && $selection['start'] <= $section->getStart() + $section->getStartTolerance()
                        && $selection['end'] >= $section->getEnd() - $section->getEndTolerance()
                        && $selection['end'] <= $section->getEnd() + $section->getEndTolerance()
                    ) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $corrected->addPenalty(new GenericPenalty($question->getPenalty()));
                }
            }
        }

        return $corrected;
    }

    /**
     * @param WaveformQuestion $question
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getSections()->toArray(), function (Section $section) {
            return 0 < $section->getScore();
        });
    }

    /**
     * @param WaveformQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getSections()->toArray();
    }

    /**
     * @param WaveformQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $sections = [];

        foreach ($answersData as $answerData) {
            foreach ($answerData as $sectionAnswer) {
                if (isset($sectionAnswer['start']) && isset($sectionAnswer['end'])) {
                    $isInSection = false;

                    foreach ($question->getSections() as $section) {
                        if ($sectionAnswer['start'] >= $section->getStart() - $section->getStartTolerance()
                            && $sectionAnswer['start'] <= $section->getStart() + $section->getStartTolerance()
                            && $sectionAnswer['end'] >= $section->getEnd() - $section->getEndTolerance()
                            && $sectionAnswer['end'] <= $section->getEnd() + $section->getEndTolerance()
                        ) {
                            $sectionId = $section->getUuid();
                            $sections[$sectionId] = isset($sections[$sectionId]) ? $sections[$sectionId] + 1 : 1;
                            $isInSection = true;
                            break;
                        }
                    }
                    if (!$isInSection) {
                        $sections['_others'] = isset($sections['_others']) ? $sections['_others'] + 1 : 1;
                    }
                }
            }
        }

        return [
            'sections' => $sections,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * No additional identifier to regenerate.
     */
    public function refreshIdentifiers(AbstractItem $item): void
    {
    }

    /**
     * @param WaveformQuestion $question
     */
    public function getCsvTitles(AbstractItem $question): array
    {
        return [$question->getQuestion()->getContentText()];
    }

    /**
     * @param WaveformQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];

        foreach ($data as $selection) {
            $answers[] = "[{$selection['start']},{$selection['end']}]";
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}

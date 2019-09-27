<?php

namespace Claroline\AudioPlayerBundle\Library\Quiz\Item\Definition;

use Claroline\AudioPlayerBundle\Entity\Quiz\ItemType\WaveformQuestion;
use Claroline\AudioPlayerBundle\Entity\Quiz\Misc\Section;
use Claroline\AudioPlayerBundle\Serializer\Quiz\WaveformQuestionSerializer;
use Claroline\AudioPlayerBundle\Validator\Quiz\JsonSchema\Attempt\AnswerData\WaveformAnswerValidator;
use Claroline\AudioPlayerBundle\Validator\Quiz\JsonSchema\Item\Type\WaveformQuestionValidator;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\Definition\AbstractDefinition;

/**
 * Waveform question definition.
 *
 * @DI\Service("claroline.definition.audio.question_waveform")
 * @DI\Tag("ujm_exo.definition.item")
 */
class WaveformDefinition extends AbstractDefinition
{
    /**
     * @var WaveformQuestionValidator
     */
    private $validator;

    /**
     * @var WaveformAnswerValidator
     */
    private $answerValidator;

    /**
     * @var WaveformQuestionSerializer
     */
    private $serializer;

    /**
     * WaveformDefinition constructor.
     *
     * @param WaveformQuestionValidator  $validator
     * @param WaveformAnswerValidator    $answerValidator
     * @param WaveformQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("claroline.validator.audio.question_waveform"),
     *     "answerValidator" = @DI\Inject("claroline.validator.audio.answer_waveform"),
     *     "serializer"      = @DI\Inject("Claroline\AudioPlayerBundle\Serializer\Quiz\WaveformQuestionSerializer")
     * })
     */
    public function __construct(
        WaveformQuestionValidator $validator,
        WaveformAnswerValidator $answerValidator,
        WaveformQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the waveform question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return 'application/x.waveform+json';
    }

    /**
     * Gets the waveform question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return WaveformQuestion::class;
    }

    /**
     * Gets the waveform question validator.
     *
     * @return WaveformQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the waveform answer validator.
     *
     * @return WaveformAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the waveform question serializer.
     *
     * @return WaveformQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param WaveformQuestion $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        /** @var Section $section */
        foreach ($question->getSections() as $section) {
            if (is_array($answer)) {
                $found = false;

                foreach ($answer as $selection) {
                    if ($selection['start'] >= $section->getStart() - $section->getStartTolerance() &&
                        $selection['start'] <= $section->getStart() &&
                        $selection['end'] >= $section->getEnd() &&
                        $selection['end'] <= $section->getEnd() + $section->getEndTolerance()
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
                    if ($selection['start'] >= $section->getStart() - $section->getStartTolerance() &&
                        $selection['start'] <= $section->getStart() &&
                        $selection['end'] >= $section->getEnd() &&
                        $selection['end'] <= $section->getEnd() + $section->getEndTolerance()
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
     * @param AbstractItem $question
     *
     * @return array
     */
    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getSections()->toArray(), function (Section $section) {
            return 0 < $section->getScore();
        });
    }

    /**
     * @param WaveformQuestion $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return $question->getSections()->toArray();
    }

    /**
     * @param AbstractItem $waveformQuestion
     * @param array        $answersData
     * @param int          $total
     *
     * @return array
     */
    public function getStatistics(AbstractItem $waveformQuestion, array $answersData, $total)
    {
        $sections = [];

        foreach ($answersData as $answerData) {
            foreach ($answerData as $sectionAnswer) {
                if (isset($sectionAnswer['start']) && isset($sectionAnswer['end'])) {
                    $isInSection = false;

                    foreach ($waveformQuestion->getSections() as $section) {
                        if ($sectionAnswer['start'] >= $section->getStart() - $section->getStartTolerance() &&
                            $sectionAnswer['start'] <= $section->getStart() &&
                            $sectionAnswer['end'] >= $section->getEnd() &&
                            $sectionAnswer['end'] <= $section->getEnd() + $section->getEndTolerance()
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
     *
     * @param AbstractItem $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        return;
    }

    public function getCsvTitles(AbstractItem $item)
    {
        return [$item->getQuestion()->getContentText()];
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
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

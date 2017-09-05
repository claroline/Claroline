<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\GraphicQuestion;
use UJM\ExoBundle\Entity\Misc\Area;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\GraphicQuestionSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\GraphicAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\GraphicQuestionValidator;

/**
 * Graphic question definition.
 *
 * @DI\Service("ujm_exo.definition.question_graphic")
 * @DI\Tag("ujm_exo.definition.item")
 */
class GraphicDefinition extends AbstractDefinition
{
    /**
     * @var GraphicQuestionValidator
     */
    private $validator;

    /**
     * @var GraphicAnswerValidator
     */
    private $answerValidator;

    /**
     * @var GraphicQuestionSerializer
     */
    private $serializer;

    /**
     * GraphicDefinition constructor.
     *
     * @param GraphicQuestionValidator  $validator
     * @param GraphicAnswerValidator    $answerValidator
     * @param GraphicQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_graphic"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_graphic"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_graphic")
     * })
     */
    public function __construct(
        GraphicQuestionValidator $validator,
        GraphicAnswerValidator $answerValidator,
        GraphicQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the graphic question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::GRAPHIC;
    }

    /**
     * Gets the graphic question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\GraphicQuestion';
    }

    /**
     * Gets the graphic question validator.
     *
     * @return GraphicQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the graphic question serializer.
     *
     * @return GraphicQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * Gets the graphic answer validator.
     *
     * @return GraphicAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * @param GraphicQuestion $question
     * @param $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        /** @var Area $area */
        foreach ($question->getAreas() as $area) {
            if (is_array($answer)) {
                foreach ($answer as $coords) {
                    if ($this->isPointInArea($area, $coords->x, $coords->y)) {
                        if ($area->getScore() > 0) {
                            $corrected->addExpected($area);
                        } else {
                            $corrected->addUnexpected($area);
                        }
                    } elseif ($area->getScore() > 0) {
                        $corrected->addMissing($area);
                    }
                }
            } elseif ($area->getScore() > 0) {
                $corrected->addMissing($area);
            }
        }

        return $corrected;
    }

    public function expectAnswer(AbstractItem $question)
    {
        return array_filter($question->getAreas()->toArray(), function (Area $area) {
            return 0 < $area->getScore();
        });
    }

    public function getStatistics(AbstractItem $graphicQuestion, array $answers)
    {
        // TODO : implement

        return [];
    }

    /**
     * Refreshes image and areas UUIDs.
     *
     * @param GraphicQuestion $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        // generate image id
        $item->getImage()->refreshUuid();

        /** @var Area $area */
        foreach ($item->getAreas() as $area) {
            $area->refreshUuid();
        }
    }

    /**
     * No additional content fields to process.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $item
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $item)
    {
        return;
    }

    private function isPointInArea(Area $area, $x, $y)
    {
        $coords = explode(',', $area->getValue());

        if (count($coords) === 2) {
            if ($area->getShape() !== GraphicQuestion::SHAPE_CIRCLE) {
                // must be old "square" shape
                $coords[] = $coords[0] + $area->getSize();
                $coords[] = $coords[1] + $area->getSize();

                return $this->isPointInRect($coords, $x, $y);
            } else {
                // must be circle
                $r = $area->getSize() / 2;
                $cx = $coords[0] + $r;
                $cy = $coords[1] + $r;

                // coordinates relative to the circle center
                $x = abs($cx - $x);
                $y = abs($cy - $y);

                // inside the circle if distance to center <= radius
                return $x * $x + $y * $y <= $r * $r;
            }
        }

        // must be rect
        return $this->isPointInRect($coords, $x, $y);
    }

    private function isPointInRect($coords, $x, $y)
    {
        return
            $x >= $coords[0] &&
            $x <= $coords[2] &&
            $y >= $coords[1] &&
            $y <= $coords[3];
    }

    public function getCsvTitles(AbstractItem $item)
    {
        return ['graphic-'.$item->getQuestion()->getUuid()];
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        $data = json_decode($answer->getData());
        $answers = [];
        foreach ($data as $point) {
            $answers[] = "[{$point->x},{$point->y}]";
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}

<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Entity\ItemType\GraphicQuestion;
use UJM\ExoBundle\Entity\Misc\Area;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Csv\ArrayCompressor;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\GraphicQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\GraphicAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\GraphicQuestionValidator;

/**
 * Graphic question definition.
 */
class GraphicDefinition extends AbstractDefinition
{
    public function __construct(
        private readonly GraphicQuestionValidator $validator,
        private readonly GraphicAnswerValidator $answerValidator,
        private readonly GraphicQuestionSerializer $serializer
    ) {
    }

    public static function getMimeType(): string
    {
        return ItemType::GRAPHIC;
    }

    public static function getEntityClass(): string
    {
        return GraphicQuestion::class;
    }

    protected function getQuestionValidator(): GraphicQuestionValidator
    {
        return $this->validator;
    }

    protected function getQuestionSerializer(): GraphicQuestionSerializer
    {
        return $this->serializer;
    }

    protected function getAnswerValidator(): GraphicAnswerValidator
    {
        return $this->answerValidator;
    }

    /**
     * @param GraphicQuestion $question
     */
    public function correctAnswer(AbstractItem $question, mixed $answer): CorrectedAnswer
    {
        $corrected = new CorrectedAnswer();

        /** @var Area $area */
        foreach ($question->getAreas() as $area) {
            if (is_array($answer)) {
                foreach ($answer as $coords) {
                    if ($this->isPointInArea($area, $coords['x'], $coords['y'])) {
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

    /**
     * @param GraphicQuestion $question
     */
    public function expectAnswer(AbstractItem $question): array
    {
        return array_filter($question->getAreas()->toArray(), function (Area $area) {
            return 0 < $area->getScore();
        });
    }

    /**
     * @param GraphicQuestion $question
     */
    public function allAnswers(AbstractItem $question): array
    {
        return $question->getAreas()->toArray();
    }

    /**
     * @param GraphicQuestion $question
     */
    public function getStatistics(AbstractItem $question, array $answersData, int $total): array
    {
        $areas = [];

        foreach ($answersData as $answerData) {
            $areasToInc = [];

            foreach ($answerData as $areaAnswer) {
                if (isset($areaAnswer['x']) && isset($areaAnswer['y'])) {
                    $isInArea = false;

                    foreach ($question->getAreas() as $area) {
                        if ($this->isPointInArea($area, $areaAnswer['x'], $areaAnswer['y'])) {
                            $areasToInc[$area->getUuid()] = true;
                            $isInArea = true;
                        }
                    }
                    if (!$isInArea) {
                        $areas['_others'] = isset($areas['_others']) ? $areas['_others'] + 1 : 1;
                    }
                }
            }
            foreach (array_keys($areasToInc) as $areaId) {
                $areas[$areaId] = isset($areas[$areaId]) ? $areas[$areaId] + 1 : 1;
            }
        }

        return [
            'areas' => $areas,
            'total' => $total,
            'unanswered' => $total - count($answersData),
        ];
    }

    /**
     * Refreshes image and areas UUIDs.
     *
     * @param GraphicQuestion $question
     */
    public function refreshIdentifiers(AbstractItem $question): void
    {
        // generate image id
        $question->getImage()->refreshUuid();

        /** @var Area $area */
        foreach ($question->getAreas() as $area) {
            $area->refreshUuid();
        }
    }

    private function isPointInArea(Area $area, $x, $y): bool
    {
        $coords = explode(',', $area->getValue());

        if (2 === count($coords)) {
            if (GraphicQuestion::SHAPE_CIRCLE !== $area->getShape()) {
                // must be old "square" shape
                $coords[] = (float) $coords[0] + $area->getSize();
                $coords[] = (float) $coords[1] + $area->getSize();

                return $this->isPointInRect($coords, $x, $y);
            } else {
                // must be a circle
                $r = $area->getSize() / 2;
                $cx = (float) $coords[0] + $r;
                $cy = (float) $coords[1] + $r;

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

    private function isPointInRect($coords, $x, $y): bool
    {
        return
            $x >= $coords[0]
            && $x <= $coords[2]
            && $y >= $coords[1]
            && $y <= $coords[3];
    }

    /**
     * @param GraphicQuestion $question
     */
    public function getCsvAnswers(AbstractItem $question, Answer $answer): array
    {
        $data = json_decode($answer->getData(), true);
        $answers = [];
        foreach ($data as $point) {
            $answers[] = "[{$point['x']},{$point['y']}]";
        }

        $compressor = new ArrayCompressor();

        return [$compressor->compress($answers)];
    }
}

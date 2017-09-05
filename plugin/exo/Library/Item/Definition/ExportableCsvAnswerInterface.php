<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;

/**
 * Interface for the definition of a question type.
 */
interface ExportableCsvAnswerInterface
{
    /**
     * Validates question answer.
     *
     * @param mixed        $answer
     * @param AbstractItem $question
     * @param array        $options
     *
     * @return array
     */
    public function getCsvTitles(AbstractItem $question);

    public function getCsvAnswers(AbstractItem $question, Answer $answer);
}

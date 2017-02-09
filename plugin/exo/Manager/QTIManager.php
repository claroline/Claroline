<?php

namespace UJM\ExoBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * QTIManager.
 *
 * @DI\Service("ujm_exo.manager.qti")
 */
class QTIManager
{
    /**
     * Exports an Exercise into an assessment test.
     *
     * @param Exercise $exercise
     *
     * @return \ZipArchive
     */
    public function exportExercise(Exercise $exercise)
    {
        return new \ZipArchive();
    }

    /**
     * Imports an assessment test as a new Exercise.
     */
    public function importTest()
    {
    }

    /**
     * Exports questions into an assessment item.
     *
     * @param Item[] $questions
     *
     * @return \ZipArchive
     */
    public function exportQuestions(array $questions)
    {
        return new \ZipArchive();
    }

    /**
     * Imports an assessment item as a new Question.
     *
     * @param mixed $data
     *
     * @return array
     */
    public function importItems($data)
    {
        return [];
    }
}

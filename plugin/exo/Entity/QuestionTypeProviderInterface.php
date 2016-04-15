<?php

namespace UJM\ExoBundle\Entity;

/**
 * Interface forcing AbstractQuestion children to return
 * statically a question type identifier (as current version
 * of PHP forbids abstract static methods).
 */
interface QuestionTypeProviderInterface
{
    /**
     * Returns a question type identifier specific to the interaction
     * (e.g. "InteractionHole", "InteractionMatch", etc.).
     *
     * @return string
     */
    public static function getQuestionType();
}

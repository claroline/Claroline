<?php

namespace UJM\ExoBundle\Library\Question;

/**
 * References the types of Question managed by the bundle.
 */
final class QuestionType
{
    /**
     * The user has to choose one (or many) proposition(s) in a set.
     *
     * @var string
     */
    const CHOICE = 'application/x.choice+json';

    /**
     * The user has to fill hole(s) in a text.
     *
     * @var string
     */
    const CLOZE = 'application/x.cloze+json';

    /**
     * The user has to find element(s) on an image.
     *
     * @var string
     */
    const GRAPHIC = 'application/x.graphic+json';

    /**
     * The user has to associate elements together.
     *
     * @var string
     */
    const MATCH = 'application/x.match+json';

    /**
     * The user has to associate one element to another.
     *
     * @var string
     */
    const PAIR = 'application/x.pair+json';

    /**
     * The user has to classify elements into categories.
     *
     * @var string
     */
    const SET = 'application/x.set+json';

    /**
     * The user has to write his answer using predefined keywords.
     *
     * @var string
     */
    const WORDS = 'application/x.words+json';

    /**
     * The user has to write his answer.
     *
     * @var string
     */
    const OPEN = 'application/x.open+json';

    /**
     * Get the list of managed question types.
     *
     * @return array
     */
    public static function getList()
    {
        return [
            static::CHOICE,
            static::CLOZE,
            static::GRAPHIC,
            static::MATCH,
            static::PAIR,
            static::SET,
            static::WORDS,
            static::OPEN,
        ];
    }

    public static function isSupported($type)
    {
        return in_array($type, static::getList());
    }
}

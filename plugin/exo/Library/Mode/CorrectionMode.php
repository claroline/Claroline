<?php

namespace UJM\ExoBundle\Library\Mode;

/**
 * Defines when the marks of an Exercise become available.
 */
final class CorrectionMode
{
    /**
     * The marks are available once the User has validated his Paper.
     *
     * @var int
     */
    const AFTER_END = 1;

    /**
     * The marks are available once the User has validated his Paper for his last attempt
     * (Exercise must define `maxAttempts`).
     *
     * @var int
     */
    const AFTER_LAST_ATTEMPT = 2;

    /**
     * The marks are available after a fixed date
     * (Exercise must define `dateCorrection`).
     *
     * @var int
     */
    const AFTER_DATE = '3';

    /**
     * The marks will never be available to Users.
     *
     * @var int
     */
    const NEVER = '4';

    /**
     * Returns the list of all CorrectionMode available (the value is the corresponding translation key).
     *
     * @return array
     */
    public static function getList()
    {
        return [
            static::AFTER_END => 'at_the_end_of_assessment',
            static::AFTER_LAST_ATTEMPT => 'after_the_last_attempt',
            static::AFTER_DATE => 'from',
            static::NEVER => 'never',
        ];
    }
}

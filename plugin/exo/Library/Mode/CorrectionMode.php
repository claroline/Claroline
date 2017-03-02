<?php

namespace UJM\ExoBundle\Library\Mode;

/**
 * Defines when the marks of an Exercise become available.
 *
 * @deprecated use Options\ShowCorrectionAt instead
 */
final class CorrectionMode
{
    /**
     * The solutions are available once the User has validated his Paper.
     *
     * @var int
     */
    const AFTER_END = 1;

    /**
     * The solutions are available once the User has validated his Paper for his last attempt
     * (Exercise must define `maxAttempts`).
     *
     * @var int
     */
    const AFTER_LAST_ATTEMPT = 2;

    /**
     * The solutions are available after a fixed date
     * (Exercise must define `dateCorrection`).
     *
     * @var int
     */
    const AFTER_DATE = '3';

    /**
     * The solutions will never be available to Users.
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

<?php

namespace UJM\ExoBundle\Library\Options;

/**
 * Defines when the marks of an Exercise become available.
 */
final class ShowCorrectionAt
{
    /**
     * The solutions are available once the User has validated his Paper.
     *
     * @var string
     */
    const AFTER_END = 'validation';

    /**
     * The solutions are available once the User has validated his Paper for his last attempt
     * (Exercise must define `maxAttempts`).
     *
     * @var string
     */
    const AFTER_LAST_ATTEMPT = 'lastAttempt';

    /**
     * The solutions are available after a fixed date
     * (Exercise must define `dateCorrection`).
     *
     * @var string
     */
    const AFTER_DATE = 'date';

    /**
     * The solutions will never be available to Users.
     *
     * @var string
     */
    const NEVER = 'never';
}

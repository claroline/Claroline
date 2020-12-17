<?php

namespace UJM\ExoBundle\Library\Options;

/**
 * Defines when the marks of an Exercise become available.
 */
final class ShowScoreAt
{
    /**
     * The marks are available at the same time than the Correction.
     *
     * @see \UJM\ExoBundle\Library\Options\ShowCorrectionAt
     *
     * @var string
     */
    const WITH_CORRECTION = 'correction';

    /**
     * The marks are available once the User has validated his Paper.
     *
     * @var string
     */
    const AFTER_END = 'validation';

    /**
     * The marks will never be displayed.
     *
     * @var string
     */
    const NEVER = 'never';
}

<?php

namespace UJM\ExoBundle\Library\Options;

/**
 * Defines the recurrence of an action.
 */
final class Recurrence
{
    /**
     * The action is never executed.
     *
     * @var string
     */
    const NEVER = 'never';

    /**
     * The action is executed one time.
     *
     * @var string
     */
    const ONCE = 'once';

    /**
     * The action is executed each time.
     *
     * @var string
     */
    const ALWAYS = 'always';
}

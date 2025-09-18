<?php

namespace Claroline\EvaluationBundle\Component\Resource;

/**
 * An interface implemented by ResourceComponent which wants to integrate the evaluation system.
 */
interface EvaluatedResourceInterface
{
    /**
     * Does the resource type implement the score system?
     */
    public static function supportsScore(): bool;

    /**
     * Does the resource type implement the attempt system?
     */
    public static function supportsAttempts(): bool;
}

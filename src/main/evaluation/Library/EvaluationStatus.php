<?php

namespace Claroline\EvaluationBundle\Library;

final class EvaluationStatus
{
    public const NOT_ATTEMPTED = 'not_attempted';
    /** @deprecated no replacement, either check for NOT_ATTEMPTED or INCOMPLETE */
    public const TODO = 'todo';
    /** @deprecated no replacement, either check for NOT_ATTEMPTED or INCOMPLETE */
    public const OPENED = 'opened';
    public const INCOMPLETE = 'incomplete';
    public const PARTICIPATED = 'participated';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const PASSED = 'passed';
    public const UNKNOWN = 'unknown';

    public const PRIORITY = [
        self::NOT_ATTEMPTED => 0,
        self::TODO => 0,
        self::UNKNOWN => 1,
        self::OPENED => 2,
        self::PARTICIPATED => 3,
        self::INCOMPLETE => 4,
        self::COMPLETED => 5,
        self::FAILED => 6,
        self::PASSED => 7,
    ];

    public static function isTerminated(string $status): bool
    {
        return in_array($status, [
            EvaluationStatus::COMPLETED,
            EvaluationStatus::PASSED,
            EvaluationStatus::PARTICIPATED,
            EvaluationStatus::FAILED,
        ]);
    }

    public static function all(): array
    {
        return [
            self::NOT_ATTEMPTED,
            self::TODO,
            self::UNKNOWN,
            self::OPENED,
            self::PARTICIPATED,
            self::INCOMPLETE,
            self::COMPLETED,
            self::FAILED,
            self::PASSED,
        ];
    }
}

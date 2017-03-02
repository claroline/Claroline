<?php

namespace UJM\ExoBundle\Library\Options;

/**
 * Defines Validators options.
 */
final class Validation
{
    /**
     * Do not perform JSON Schema validation.
     *
     * @var string
     */
    const NO_SCHEMA = 'noSchema';

    /**
     * The `solutions` property of the items is required.
     *
     * @var string
     */
    const REQUIRE_SOLUTIONS = 'requireSolutions';

    /**
     * The `score` property of the items must be validated.
     *
     * @var string
     */
    const VALIDATE_SCORE = 'validateScore';

    /**
     * The question used for validation.
     * Used to validate answers against a specific question data.
     * We need to find a better way to pass it to validator.
     */
    const QUESTION = 'question';
}

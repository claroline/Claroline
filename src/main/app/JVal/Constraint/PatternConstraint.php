<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal\Constraint;

use JVal\Constraint;
use JVal\Context;
use JVal\Exception\Constraint\InvalidRegexException;
use JVal\Exception\Constraint\InvalidTypeException;
use JVal\Types;
use JVal\Utils;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "pattern" keyword.
 */
class PatternConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['pattern'];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return Types::TYPE_STRING === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        $context->enterNode('pattern');

        if (!is_string($schema->pattern)) {
            throw new InvalidTypeException($context, Types::TYPE_STRING);
        }

        if (!Utils::isValidRegex($schema->pattern)) {
            throw new InvalidRegexException($context);
        }

        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (!Utils::matchesRegex($instance, $schema->pattern)) {
            $context->addViolation('should match regex "%s"', [$schema->pattern]);
        }
    }
}

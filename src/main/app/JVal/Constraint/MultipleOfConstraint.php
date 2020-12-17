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
use JVal\Exception\Constraint\InvalidTypeException;
use JVal\Exception\Constraint\NotStrictlyPositiveException;
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "multipleOf" keyword.
 */
class MultipleOfConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['multipleOf'];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return Types::TYPE_INTEGER === $type || Types::TYPE_NUMBER === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        $context->enterNode('multipleOf');

        if (!Types::isA($schema->multipleOf, Types::TYPE_NUMBER)) {
            throw new InvalidTypeException($context, Types::TYPE_NUMBER);
        }

        if ($schema->multipleOf <= 0) {
            throw new NotStrictlyPositiveException($context);
        }

        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        $divider = $schema->multipleOf;
        $modulus = fmod($instance, $divider);
        $precision = abs(0.0000000001);
        $diff = (float) ($modulus - $divider);

        if (-$precision < $diff && $diff < $precision) {
            $fMod = 0.0;
        } else {
            $decimals1 = mb_strpos($instance, '.') ?
                mb_strlen($instance) - mb_strpos($instance, '.') - 1 :
                0;
            $decimals2 = mb_strpos($divider, '.') ?
                mb_strlen($divider) - mb_strpos($divider, '.') - 1 :
                0;
            $fMod = (float) round($modulus, max($decimals1, $decimals2));
        }

        if (!$fMod) {
            $context->addViolation('should be a multiple of %s', [$divider]);
        }
    }
}

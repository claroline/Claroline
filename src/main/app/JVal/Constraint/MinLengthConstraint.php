<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal\Constraint;

use JVal\Context;
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "minLength" keyword.
 */
class MinLengthConstraint extends AbstractCountConstraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['minLength'];
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
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        $length = extension_loaded('mbstring') ?
            mb_strlen($instance, mb_detect_encoding($instance)) :
            strlen($instance);

        if ($length < $schema->minLength) {
            $context->addViolation(
                'should be greater than or equal to %s characters',
                [$schema->minLength]
            );
        }
    }
}

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

/**
 * Constraint for the "minItems" keyword.
 */
class MinItemsConstraint extends AbstractCountConstraint
{
    public function keywords()
    {
        return ['minItems'];
    }

    public function supports($type)
    {
        return Types::TYPE_ARRAY === $type;
    }

    public function apply($instance, \stdClass $schema, Context $context, Walker $walker)
    {
        if (count($instance) < $schema->minItems) {
            $context->addViolation(
                'number of items should be greater than or equal to %s',
                [$schema->minItems]
            );
        }
    }
}

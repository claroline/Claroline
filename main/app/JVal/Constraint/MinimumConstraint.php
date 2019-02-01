<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal\Constraint;

use JVal\Context;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "minimum" and "exclusiveMinimum" keywords.
 */
class MinimumConstraint extends AbstractRangeConstraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['minimum', 'exclusiveMinimum'];
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (false === $schema->exclusiveMinimum) {
            if ($instance < $schema->minimum) {
                $context->addViolation('should be greater than or equal to %s', [$schema->minimum]);
            }
        } elseif ($instance <= $schema->minimum) {
            $context->addViolation('should be greater than %s', [$schema->minimum]);
        }
    }
}

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
 * Constraint for the "minProperties" keyword.
 */
class MinPropertiesConstraint extends AbstractCountConstraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['minProperties'];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return Types::TYPE_OBJECT === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (count(get_object_vars($instance)) < $schema->minProperties) {
            $context->addViolation(
                'number of properties should be greater than or equal to %s',
                [$schema->minProperties]
            );
        }
    }
}

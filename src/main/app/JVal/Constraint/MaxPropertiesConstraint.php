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
 * Constraint for the "maxProperties" keyword.
 */
class MaxPropertiesConstraint extends AbstractCountConstraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['maxProperties'];
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
        if (count(get_object_vars($instance)) > $schema->maxProperties) {
            $context->addViolation(
                'number of properties should be lesser than or equal to %s',
                [$schema->maxProperties]
            );
        }
    }
}

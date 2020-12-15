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
use JVal\Exception\Constraint\NotPrimitiveTypeException;
use JVal\Exception\Constraint\NotUniqueException;
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "type" keyword.
 */
class TypeConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        $context->enterNode('type');

        if (is_string($schema->type)) {
            if (!Types::isPrimitive($schema->type)) {
                throw new NotPrimitiveTypeException($context);
            }
        } elseif (is_array($schema->type)) {
            foreach ($schema->type as $index => $type) {
                $context->enterNode($index);

                if (!is_string($type)) {
                    throw new InvalidTypeException($context, Types::TYPE_STRING);
                }

                if (!Types::isPrimitive($type)) {
                    throw new NotPrimitiveTypeException($context);
                }

                $context->leaveNode();
            }

            if (count(array_unique($schema->type)) !== count($schema->type)) {
                throw new NotUniqueException($context);
            }
        } else {
            throw new InvalidTypeException($context, [Types::TYPE_STRING, Types::TYPE_ARRAY]);
        }

        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (is_string($schema->type)) {
            if (!Types::isA($instance, $schema->type)) {
                $context->addViolation('instance must be of type %s', [$schema->type]);
            }
        } else {
            if (!Types::isOneOf($instance, $schema->type)) {
                $context->addViolation('instance does not match any of the expected types');
            }
        }
    }
}

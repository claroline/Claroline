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
use JVal\Exception\Constraint\EmptyArrayException;
use JVal\Exception\Constraint\InvalidTypeException;
use JVal\Exception\Constraint\NotUniqueException;
use JVal\Types;
use JVal\Walker;

/**
 * Constraint for the "required" keyword.
 */
class RequiredConstraint implements Constraint
{
    public function keywords(): array
    {
        return ['required'];
    }

    public function supports($type): bool
    {
        return Types::TYPE_OBJECT === $type;
    }

    public function normalize(\stdClass $schema, Context $context, Walker $walker): void
    {
        $context->enterNode('required');

        if (!is_array($schema->required)) {
            throw new InvalidTypeException($context, Types::TYPE_ARRAY);
        }

        $requiredCount = count($schema->required);
        if (0 === $requiredCount) {
            throw new EmptyArrayException($context);
        }

        foreach ($schema->required as $index => $property) {
            if (!is_string($property)) {
                $context->enterNode($index);

                throw new InvalidTypeException($context, Types::TYPE_STRING);
            }
        }

        if ($requiredCount !== count(array_unique($schema->required))) {
            throw new NotUniqueException($context);
        }

        $context->leaveNode();
    }

    public function apply($instance, \stdClass $schema, Context $context, Walker $walker): void
    {
        foreach ($schema->required as $property) {
            if (!property_exists($instance, $property)) {
                $context->addViolation('property "%s" is missing', [$property], $property);
            }
        }
    }
}

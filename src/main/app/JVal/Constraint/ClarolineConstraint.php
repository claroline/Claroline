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
class ClarolineConstraint implements Constraint
{
    public function keywords(): array
    {
        return ['claroline'];
    }

    public function supports($type): bool
    {
        return Types::TYPE_OBJECT === $type;
    }

    public function normalize(\stdClass $schema, Context $context, Walker $walker): void
    {
        $context->enterNode('claroline');

        if (isset($schema->requiredAtCreation)) {
            if (!is_array($schema->requiredAtCreation)) {
                throw new InvalidTypeException($context, Types::TYPE_ARRAY);
            }

            $requiredCount = count($schema->requiredAtCreation);
            if (0 === $requiredCount) {
                throw new EmptyArrayException($context);
            }

            foreach ($schema->requiredAtCreation as $index => $property) {
                if (!is_string($property)) {
                    $context->enterNode($index);

                    throw new InvalidTypeException($context, Types::TYPE_STRING);
                }
            }

            if ($requiredCount !== count(array_unique($schema->requiredAtCreation))) {
                throw new NotUniqueException($context);
            }
        }

        $context->leaveNode();
    }

    public function apply($instance, \stdClass $schema, Context $context, Walker $walker, ?array $options = []): void
    {
        if (isset($schema->claroline)) {
            if (isset($schema->claroline->requiredAtCreation) && in_array('create', $options)) {
                $this->applyRequired($instance, $schema->claroline, $context, $walker, $options);
            }
            if (isset($schema->claroline->ids) && in_array('update', $options)) {
            }
        }
    }

    private function applyRequired($instance, \stdClass $schema, Context $context, Walker $walker, ?array $options = []): void
    {
        foreach ($schema->requiredAtCreation as $property) {
            if (in_array('create', $options) && !property_exists($instance, $property)) {
                $context->addViolation('property "%s" is missing', [$property], $property);
            }
        }
    }
}

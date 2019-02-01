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
use stdClass;

/**
 * Constraint for the "dependencies" keyword.
 */
class DependenciesConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['dependencies'];
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
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        $context->enterNode('dependencies');

        if (!is_object($schema->dependencies)) {
            throw new InvalidTypeException($context, Types::TYPE_OBJECT);
        }

        foreach ($schema->dependencies as $property => $value) {
            $context->enterNode($property);

            if (is_object($value)) {
                $walker->parseSchema($value, $context);
            } elseif (is_array($value)) {
                if (0 === $propertyCount = count($value)) {
                    throw new EmptyArrayException($context);
                }

                foreach ($value as $index => $subProperty) {
                    if (!is_string($subProperty)) {
                        $context->enterNode($index);

                        throw new InvalidTypeException($context, Types::TYPE_STRING);
                    }
                }

                if (count(array_unique($value)) !== $propertyCount) {
                    throw new NotUniqueException($context);
                }
            } else {
                throw new InvalidTypeException($context, [Types::TYPE_OBJECT, Types::TYPE_ARRAY]);
            }

            $context->leaveNode();
        }

        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        foreach ($schema->dependencies as $property => $value) {
            if (property_exists($instance, $property)) {
                if (is_object($value)) {
                    // Schema dependencies (see §5.4.5.2.1)
                    $walker->applyConstraints($instance, $value, $context);
                } else {
                    // Property dependencies (see §5.4.5.2.2)
                    foreach ($value as $propertyDependency) {
                        if (!property_exists($instance, $propertyDependency)) {
                            $context->addViolation(
                                'dependency property "%s" is missing',
                                [$propertyDependency]
                            );
                        }
                    }
                }
            }
        }
    }
}

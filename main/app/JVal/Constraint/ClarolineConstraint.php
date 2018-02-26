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
 * Constraint for the "required" keyword.
 */
class ClarolineConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['claroline'];
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
        $context->enterNode('claroline');

        if (isset($schema->requiredAtCreation)) {
            if (!is_array($schema->requiredAtCreation)) {
                throw new InvalidTypeException($context, Types::TYPE_ARRAY);
            }

            if (0 === $requiredCount = count($schema->requiredAtCreation)) {
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

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker, array $options = [])
    {
        if (isset($schema->claroline)) {
            if (isset($schema->claroline->requiredAtCreation) && in_array('create', $options)) {
                $this->applyRequired($instance, $schema->claroline, $context, $walker, $options);
            }
            if (isset($schema->claroline->ids) && in_array('update', $options)) {
            }
        }
    }

    private function applyRequired($instance, stdClass $schema, Context $context, Walker $walker, array $options = [])
    {
        foreach ($schema->requiredAtCreation as $property) {
            if (in_array('create', $options) && !property_exists($instance, $property)) {
                $context->addViolation('property "%s" is missing', [$property]);
            }
        }
    }
}

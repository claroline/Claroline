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
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "items" and "additionalItems" keywords.
 */
class ItemsConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['items', 'additionalItems'];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return Types::TYPE_ARRAY === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        $this->createDefaults($schema);

        $context->enterNode('items');
        $this->parseItemsProperty($schema, $context, $walker);

        $context->enterSibling('additionalItems');
        $this->parseAdditionalItemsProperty($schema, $context, $walker);
        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (is_object($schema->items)) {
            // 8.2.3.1. If items is a schema, then the child instance must be
            // valid against this schema, regardless of its index, and
            // regardless of the value of "additionalItems".
            foreach ($instance as $index => $item) {
                $context->enterNode($index);
                $walker->applyConstraints($item, $schema->items, $context);
                $context->leaveNode();
            }
        } else { // "items" is an array
            $itemSize = count($schema->items);

            foreach ($instance as $index => $item) {
                $context->enterNode($index);

                // 8.2.3.2.  If the index is less than, or equal to, the size of
                // "items", the child instance must be valid against the
                // corresponding schema in the "items" array; otherwise, it must
                // be valid against the schema defined by "additionalItems".
                //
                // NOTE: this is adapted for 0-based indexation.
                if ($index < $itemSize) {
                    $walker->applyConstraints($item, $schema->items[$index], $context);
                } elseif (false === $schema->additionalItems) {
                    $context->addViolation('additional items are not allowed');
                } else {
                    $walker->applyConstraints($item, $schema->additionalItems, $context);
                }

                $context->leaveNode();
            }
        }
    }

    private function createDefaults(stdClass $schema)
    {
        if (!property_exists($schema, 'items')) {
            $schema->items = new stdClass();
        }

        if (!property_exists($schema, 'additionalItems') || true === $schema->additionalItems) {
            $schema->additionalItems = new stdClass();
        }
    }

    private function parseItemsProperty(stdClass $schema, Context $context, Walker $walker)
    {
        if (is_object($schema->items)) {
            $walker->parseSchema($schema->items, $context);
        } elseif (is_array($schema->items)) {
            foreach ($schema->items as $index => $item) {
                $context->enterNode($index);

                if (!is_object($item)) {
                    throw new InvalidTypeException($context, Types::TYPE_OBJECT);
                }

                $walker->parseSchema($item, $context);
                $context->leaveNode();
            }
        } else {
            throw new InvalidTypeException($context, [Types::TYPE_OBJECT, Types::TYPE_ARRAY]);
        }
    }

    private function parseAdditionalItemsProperty(stdClass $schema, Context $context, Walker $walker)
    {
        if (is_object($schema->additionalItems)) {
            $walker->parseSchema($schema->additionalItems, $context);
        } elseif (!is_bool($schema->additionalItems)) {
            throw new InvalidTypeException($context, [Types::TYPE_OBJECT, Types::TYPE_BOOLEAN]);
        }
    }
}

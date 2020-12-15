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
use JVal\Exception\Constraint\InvalidRegexException;
use JVal\Exception\Constraint\InvalidTypeException;
use JVal\Types;
use JVal\Utils;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "properties", "additionalProperties" and
 * "patternProperties" keywords.
 */
class PropertiesConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['properties', 'additionalProperties', 'patternProperties'];
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
        $this->createDefaults($schema);

        $context->enterNode('properties');
        $this->parsePropertiesProperty($schema, $context, $walker);

        $context->enterSibling('additionalProperties');
        $this->parseAdditionalPropertiesProperty($schema, $context, $walker);

        $context->enterSibling('patternProperties');
        $this->parsePatternPropertiesProperty($schema, $context, $walker);
        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        // implementation of the algorithms described in 5.4.4.4 and in 8.3
        foreach ($instance as $property => $value) {
            $schemas = [];

            if (isset($schema->properties->{$property})) {
                $schemas[] = $schema->properties->{$property};
            }

            foreach ($schema->patternProperties as $regex => $propertySchema) {
                if (Utils::matchesRegex($property, $regex)) {
                    $schemas[] = $propertySchema;
                }
            }

            if (empty($schemas)) {
                if (is_object($schema->additionalProperties)) {
                    $schemas[] = $schema->additionalProperties;
                } elseif (false === $schema->additionalProperties) {
                    $context->addViolation('additional property "%s" is not allowed', [$property]);
                }
            }

            $context->enterNode($property);

            foreach ($schemas as $childSchema) {
                $walker->applyConstraints($value, $childSchema, $context);
            }

            $context->leaveNode();
        }
    }

    private function createDefaults(stdClass $schema)
    {
        if (!property_exists($schema, 'properties')) {
            $schema->properties = new stdClass();
        }

        if (!property_exists($schema, 'additionalProperties')
            || true === $schema->additionalProperties) {
            $schema->additionalProperties = new stdClass();
        }

        if (!property_exists($schema, 'patternProperties')) {
            $schema->patternProperties = new stdClass();
        }
    }

    private function parsePropertiesProperty(stdClass $schema, Context $context, Walker $walker)
    {
        if (!is_object($schema->properties)) {
            throw new InvalidTypeException($context, Types::TYPE_OBJECT);
        }

        foreach ($schema->properties as $property => $value) {
            $context->enterNode($property);

            if (!is_object($value)) {
                throw new InvalidTypeException($context, Types::TYPE_OBJECT);
            }

            $walker->parseSchema($value, $context);
            $context->leaveNode();
        }
    }

    private function parseAdditionalPropertiesProperty(stdClass $schema, Context $context, Walker $walker)
    {
        if (is_object($schema->additionalProperties)) {
            $walker->parseSchema($schema->additionalProperties, $context);
        } elseif (!is_bool($schema->additionalProperties)) {
            throw new InvalidTypeException($context, [Types::TYPE_OBJECT, Types::TYPE_BOOLEAN]);
        }
    }

    private function parsePatternPropertiesProperty(stdClass $schema, Context $context, Walker $walker)
    {
        if (!is_object($schema->patternProperties)) {
            throw new InvalidTypeException($context, Types::TYPE_OBJECT);
        }

        foreach ($schema->patternProperties as $regex => $value) {
            $context->enterNode($regex);

            if (!Utils::isValidRegex($regex)) {
                throw new InvalidRegexException($context);
            }

            if (!is_object($value)) {
                throw new InvalidTypeException($context, Types::TYPE_OBJECT);
            }

            $walker->parseSchema($value, $context);
            $context->leaveNode();
        }
    }
}

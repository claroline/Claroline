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
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Base class for constraints based on a set of sub-schemas.
 */
abstract class AbstractOfConstraint implements Constraint
{
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
        $keyword = $this->keywords()[0];
        $context->enterNode($keyword);

        if (!is_array($schema->{$keyword})) {
            throw new InvalidTypeException($context, Types::TYPE_ARRAY);
        }

        if (0 === count($schema->{$keyword})) {
            throw new EmptyArrayException($context);
        }

        foreach ($schema->{$keyword} as $index => $subSchema) {
            $context->enterNode($index);

            if (!is_object($subSchema)) {
                throw new InvalidTypeException($context, Types::TYPE_OBJECT);
            }

            $walker->parseSchema($subSchema, $context);
            $context->leaveNode();
        }

        $context->leaveNode();
    }
}

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
use JVal\Exception\Constraint\LessThanZeroException;
use JVal\Types;
use JVal\Walker;
use stdClass;

/**
 * Base class for constraints based on a specific number of elements.
 */
abstract class AbstractCountConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function normalize(stdClass $schema, Context $context, Walker $walker)
    {
        $keyword = $this->keywords()[0];
        $context->enterNode($keyword);

        if (!is_int($schema->{$keyword})) {
            throw new InvalidTypeException($context, Types::TYPE_INTEGER);
        }

        if ($schema->{$keyword} < 0) {
            throw new LessThanZeroException($context);
        }

        $context->leaveNode();
    }
}

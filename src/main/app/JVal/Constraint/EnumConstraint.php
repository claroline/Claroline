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
use JVal\Utils;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "enum" keyword.
 */
class EnumConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['enum'];
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
        $context->enterNode('enum');

        if (!is_array($schema->enum)) {
            throw new InvalidTypeException($context, Types::TYPE_ARRAY);
        }

        if (0 === count($schema->enum)) {
            throw new EmptyArrayException($context);
        }

        foreach ($schema->enum as $i => $aItem) {
            foreach ($schema->enum as $j => $bItem) {
                if ($i !== $j && Utils::areEqual($aItem, $bItem)) {
                    throw new NotUniqueException($context);
                }
            }
        }

        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        $hasMatch = false;

        foreach ($schema->enum as $value) {
            if (Utils::areEqual($instance, $value)) {
                $hasMatch = true;
                break;
            }
        }

        if (!$hasMatch) {
            $context->addViolation('should match one element in enum');
        }
    }
}

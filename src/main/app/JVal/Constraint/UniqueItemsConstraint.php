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
use JVal\Utils;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "uniqueItems" keyword.
 */
class UniqueItemsConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['uniqueItems'];
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
        if (!is_bool($schema->uniqueItems)) {
            $context->enterNode('uniqueItems');

            throw new InvalidTypeException($context, Types::TYPE_BOOLEAN);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        if (true === $schema->uniqueItems) {
            foreach ($instance as $i => $aItem) {
                foreach ($instance as $j => $bItem) {
                    if ($i !== $j && Utils::areEqual($aItem, $bItem)) {
                        $context->addViolation('elements must be unique');
                        break 2;
                    }
                }
            }
        }
    }
}

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
 * Constraint for the "not" keyword.
 */
class NotConstraint implements Constraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['not'];
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
        $context->enterNode('not');

        if (!is_object($schema->not)) {
            throw new InvalidTypeException($context, Types::TYPE_OBJECT);
        }

        $walker->parseSchema($schema->not, $context);
        $context->leaveNode();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        $altContext = $context->duplicate();
        $walker->applyConstraints($instance, $schema->not, $altContext);

        if ($altContext->countViolations() === $context->countViolations()) {
            $context->addViolation('should not match schema in "not"');
        }
    }
}

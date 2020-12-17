<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal\Constraint;

use JVal\Context;
use JVal\Walker;
use stdClass;

/**
 * Constraint for the "anyOf" keyword.
 */
class AnyOfConstraint extends AbstractOfConstraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['anyOf'];
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        $accumulatingContext = $context->duplicate();
        $hasMatch = false;

        foreach ($schema->anyOf as $subSchema) {
            $originalCount = $accumulatingContext->countViolations();
            $walker->applyConstraints($instance, $subSchema, $accumulatingContext);

            if ($accumulatingContext->countViolations() === $originalCount) {
                $hasMatch = true;
                break;
            }
        }

        if (!$hasMatch) {
            $context->mergeViolations($accumulatingContext);
            $context->addViolation('instance must match at least one of the schemas listed in anyOf');
        }
    }
}

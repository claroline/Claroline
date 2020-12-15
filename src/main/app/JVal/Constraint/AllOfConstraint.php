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
 * Constraint for the "allOf" keyword.
 */
class AllOfConstraint extends AbstractOfConstraint
{
    /**
     * {@inheritdoc}
     */
    public function keywords()
    {
        return ['allOf'];
    }

    /**
     * {@inheritdoc}
     */
    public function apply($instance, stdClass $schema, Context $context, Walker $walker)
    {
        $originalCount = $context->countViolations();

        foreach ($schema->allOf as $subSchema) {
            $walker->applyConstraints($instance, $subSchema, $context);
        }

        if ($context->countViolations() > $originalCount) {
            $context->addViolation('instance must match all the schemas listed in allOf');
        }
    }
}

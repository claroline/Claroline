<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NoDuplicate extends Constraint
{
    public $message = 'Each value must be unique.';

    /**
     * If the property is null, elements of the collection will
     * be directly compared, otherwise the validator will expect
     * the collection to contain objects with the property accessible
     * (directly or via a getter) and will compare only the returned
     * values.
     *
     * @var mixed
     */
    public $property = null;
}

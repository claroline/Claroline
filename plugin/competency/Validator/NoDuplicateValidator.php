<?php

namespace HeVinci\CompetencyBundle\Validator;

use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator ensuring that an array or any object implementing
 * the Traversable interface has no duplicates. If the constraint
 * doesn't specify a property, it will operate on the plain values
 * of the collection. Otherwise, it will expect elements of the
 * collection to be objects with this property accessible, and will
 * compare only the property values.
 */
class NoDuplicateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new \InvalidArgumentException(
                'array or Traversable instance expected'
            );
        }

        $items = [];

        foreach ($value as $element) {
            if ($constraint->property) {
                if (!is_object($element)) {
                    throw new UnexpectedTypeException($element, 'object');
                }

                $accessor = PropertyAccess::createPropertyAccessor();
                $item = $accessor->getValue($element, $constraint->property);
            } else {
                $item = $element;
            }

            if (in_array($item, $items)) {
                $this->context->addViolation($constraint->message);
                break;
            }

            $items[] = $item;
        }
    }
}

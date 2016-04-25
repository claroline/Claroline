<?php

namespace FormaLibre\ReservationBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CsvResource extends Constraint
{
    public function validatedBy()
    {
        return 'csv_resource_validator';
    }
}

<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("csv_user_validator")
 */
class CsvUserValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $lines = str_getcsv(file_get_contents($value), PHP_EOL, ',');

        foreach ($lines as $line) {
            $linesTab = explode(',', $line);
            $nbElements = count($linesTab);

            if ($nbElements < 5) {
                $this->context->addViolation($constraint->message);
                break 1;
            }
        }
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Validator("filesize_validator")
 */
class FileSizeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $validUnits = array('B', 'KB', 'MB', 'GB', 'TB');
        $value = str_replace(' ', '', $value);

        $replacements = array('');
        $patterns = array('/(\d+)/');
        $unit = preg_replace($patterns, $replacements, $value);
        $found = false;

        foreach ($validUnits as $validUnit) {
            if ($unit === $validUnit) {
                $found = true;
            }
        }

        if (!$found) {
            $this->context->addViolation($constraint->message);
        }
    }
}

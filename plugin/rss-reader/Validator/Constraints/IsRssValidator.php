<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsRssValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $content = @file_get_contents($value);

        if (!$content) {
            $this->context->addViolation($constraint->message, array('{{ username }}' => $value));
        }
    }
}

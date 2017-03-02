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

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("fileupload_validator")
 */
class FileUploadValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $url = $this->context->getRoot()->get('fileUrl')->getData();
        if (!isset($value) && !isset($url)) {
            $this->context->addViolation($constraint->message);
        }
    }
}

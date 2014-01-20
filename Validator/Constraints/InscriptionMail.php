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

/**
 * @Annotation
 */
class InscriptionMail extends Constraint
{
    public $message = 'The placeholders %username% and %password% are required.';

    public function validatedBy()
    {
        return 'inscription_mail_validator';
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SendToNames extends Constraint
{
    public $message = 'The user, group or workspace {{ name }} does not exist.';

    public function validatedBy()
    {
        return 'send_to_name_validator';
    }
}

<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SendToNames extends Constraint
{
    public $message = 'The user or group {{ name }} does not exist.';

    public function validatedBy()
    {
        return 'send_to_name_validator';
    }
}

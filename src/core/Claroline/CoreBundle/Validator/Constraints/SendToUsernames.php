<?php

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SendToUsernames extends Constraint
{
    public $message = 'The user {{ username }} does not exist.';

    public function validatedBy()
    {
        return 'send_to_username_validator';
    }
}


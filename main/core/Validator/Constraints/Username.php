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
class Username extends Constraint
{
    public $error = 'special_char_not_allowed';

    public function validatedBy()
    {
        return 'username_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Badge\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AtLeastOneTranslation extends Constraint
{
    public $message = "badge_need_at_least_one_translation";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

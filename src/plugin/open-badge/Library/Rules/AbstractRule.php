<?php

namespace Claroline\OpenBadgeBundle\Library\Rules;

use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;

abstract class AbstractRule
{
    abstract public static function getType(): string;

    /**
     * Gets the list of Users who meet the rules.
     * NB. You don't need to filter users who already own the badge, this is down in the generic layer.
     *
     * @return User[]
     */
    abstract public function getQualifiedUsers(Rule $rule): iterable;

    abstract public function getEvidenceMessage(): string;
}

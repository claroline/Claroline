<?php

namespace Icap\BadgeBundle\Rule;

use Icap\BadgeBundle\Rule\Entity\Rule;

interface RulableInterface
{
    /**
     * @return Rule[]
     */
    public function getRules();

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule[] $rules
     *
     * @return RulableInterface
     */
    public function setRules($rules);

    /**
     * @return bool
     */
    public function hasRules();

    /**
     * @return array
     */
    public function getRestriction();
}

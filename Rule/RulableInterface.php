<?php

namespace Claroline\CoreBundle\Rule;

use Claroline\CoreBundle\Entity\Rule\Rule;

interface RulableInterface
{
    /**
     * @return Rule[]
     */
    public function getRules();

    /**
     * @param \Claroline\CoreBundle\Entity\Rule\Rule[] $rules
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
 
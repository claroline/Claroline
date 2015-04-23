<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule;

use Claroline\CoreBundle\Rule\Entity\Rule;

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

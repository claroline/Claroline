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

abstract class Rulable implements RulableInterface
{
    /**
     * @return bool
     */
    public function hasRules()
    {
        return 0 < count($this->getRules());
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array();
    }
}

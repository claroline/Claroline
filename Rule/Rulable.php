<?php

namespace Icap\BadgeBundle\Rule;

abstract class Rulable implements RulableInterface
{
    /**
     * @return bool
     */
    public function hasRules()
    {
        return (0 < count($this->getRules()));
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return array();
    }
}

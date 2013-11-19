<?php

namespace Claroline\CoreBundle\Badge\Constraints;

interface ConstraintInterface
{
    /**
     * @return bool
     */
    public function validate();
}

<?php

namespace Claroline\CoreBundle\Event\Log;

interface RestrictionnableInterface
{
    /**
     * @return array
     */
    public static function getRestriction();
}

<?php

namespace Claroline\AppBundle\Component;

/**
 * ComponentInterface is the interface implemented by all claroline components.
 */
interface ComponentInterface
{
    public static function getShortName(): string;
}

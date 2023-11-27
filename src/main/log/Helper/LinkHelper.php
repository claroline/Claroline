<?php

namespace Claroline\LogBundle\Helper;

class LinkHelper
{
    public static function link(string $label, string $target = null): string
    {
        if ($target) {
            return "<a href='$target'>$label</a>";
        }

        return $label;
    }
}

<?php

namespace Claroline\LogBundle\Helper;

class TextHelper
{
    public static function bold(string $text): string
    {
        return "<b>$text</b>";
    }

    public static function italic(string $text): string
    {
        return "<i>$text</i>";
    }
}

<?php

namespace Claroline\LogBundle\Helper;

class ColorHelper
{
    public static function info(string $text): string
    {
        return static::color($text, 'info');
    }

    public static function warning(string $text): string
    {
        return static::color($text, 'warning');
    }

    public static function danger(string $text): string
    {
        return static::color($text, 'danger');
    }

    public static function success(string $text): string
    {
        return static::color($text, 'success');
    }

    public static function learning(string $text): string
    {
        return static::color($text, 'learning');
    }

    private static function color(string $text, string $variant): string
    {
        return "<span class='text-$variant'>$text</span>";
    }
}

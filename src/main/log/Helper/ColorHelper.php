<?php

namespace Claroline\LogBundle\Helper;

class ColorHelper
{
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const DANGER = 'danger';
    public const SUCCESS = 'success';

    public static function info(string $text): string
    {
        return static::color($text, self::INFO);
    }

    public static function warning(string $text): string
    {
        return static::color($text, self::WARNING);
    }

    public static function danger(string $text): string
    {
        return static::color($text, self::DANGER);
    }

    public static function success(string $text): string
    {
        return static::color($text, self::SUCCESS);
    }

    public static function color(string $text, string $variant): string
    {
        return "<span class='text-$variant'>$text</span>";
    }
}

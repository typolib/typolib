<?php
namespace Typolib;

class Locale
{
    private static $locale_list = ['fr', 'en', 'es', 'ro'];

    public static function getLocaleList()
    {
        return self::$locale_list;
    }

    public static function isSupportedLocale($locale)
    {
        if (in_array($locale, self::$locale_list)) {
            return true;
        } else {
            return false;
        }
    }
}

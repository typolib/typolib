<?php
namespace Typolib;

/**
 * Utils class
 *
 * @package Typolib
 */
class Utils
{
    public static function sanitizeFileName($name)
    {
        return preg_replace('/[^a-zA-Z0-9-_\.]/', '', $name);
    }
}

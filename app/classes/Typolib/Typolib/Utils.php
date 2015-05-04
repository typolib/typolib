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

    private static function deleteFolder($folder)
    {
        if (is_dir($folder)) {
            $objects = scandir($folder);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($folder . '/' . $object) == 'dir') {
                        Utils::deleteFolder($folder . '/' . $object);
                    } else {
                        unlink($folder . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($folder);

            return true;
        }

        return false;
    }
}

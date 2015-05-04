<?php
namespace Typolib;

/**
 * Code class
 *
 * @package Typolib
 */
class Code
{
    // attributes

    public $name;
    public $locale;
    public $folder;
    public $path;

    public function __construct($name, $locale)
    {
        $this->name = $name;
        $this->locale = $locale;
        $false_name = Utils::sanitizeFileName($this->name);
        $this->path = DATA_ROOT . "code/$this->locale/$false_name";
        $this->createCode();
    }

    public function createCode()
    {
        if (!file_exists($this->path)) {
            $var = "<?php \$code = ['name' => '" . $this->name . "', ['1' => ['regle blabla', 'ifthen'], '2' ]] ";
            mkdir($this->path, 0777, true);
            file_put_contents($this->path . '/rules.php', $var);
            file_put_contents($this->path . '/exceptions.php', '');

            return true;
        }

        return false;
    }

    public static function deleteCode($name, $locale)
    {
        $folder = DATA_ROOT . "code/$locale/$name";
        Code::deleteFolder($folder);
    }

    private static function deleteFolder($folder)
    {
        if (is_dir($folder)) {
            $objects = scandir($folder);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($folder . "/" . $object) == "dir") {
                        Code::deleteFolder($folder . "/" . $object);
                    } else {
                        unlink($folder . "/" . $object);
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

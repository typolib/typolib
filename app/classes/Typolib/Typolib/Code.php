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
        if (Locale::isSupportedLocale($locale)) {
            $this->name = $name;
            $this->locale = $locale;
            $false_name = Utils::sanitizeFileName($this->name);
            $this->path = DATA_ROOT . "code/$this->locale/$false_name";
            $this->createCode();

            return true;
        }

        return false;
    }

    public function createCode()
    {
        if (! file_exists($this->path)) {
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

        return Utils::deleteFolder($folder);
    }
}

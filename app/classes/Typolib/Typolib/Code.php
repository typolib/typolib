<?php
namespace Typolib;

/**
 * Code class
 *
 * This class provides methods to manage a code: create, delete or update,
 * check if a code exists.
 *
 * @package Typolib
 */
class Code
{
    private $name;
    private $locale;
    private $path;

    /**
     * Constructor that initializes all the arguments then call the method
     * to create the code if the locale is supported.
     *
     * @param  String  $name   The name of the new code.
     * @param  String  $locale The locale of the new code.
     * @return boolean True if the code has been created.
     */
    public function __construct($name, $locale)
    {
        if (Locale::isSupportedLocale($locale)) {
            $this->name = $name;
            $this->locale = $locale;
            $false_name = Utils::sanitizeFileName($this->name);
            $this->path = DATA_ROOT . RULES_REPO . "/$this->locale/$false_name";
            $this->createCode();

            return true;
        }

        return false;
    }

    /**
     * Creates an code, its directory and its files (rules.php and exceptions.php).
     *
     * @return boolean True if the code doesn't exist and has been created.
     */
    private function createCode()
    {
        if (! file_exists($this->path)) {
            $code = ['name' => $this->name];
            mkdir($this->path, 0777, true);
            file_put_contents($this->path . '/rules.php', serialize($code));
            file_put_contents($this->path . '/exceptions.php', '');

            return true;
        }

        return false;
    }

    /**
     * Deletes a code. Calls deleteFolder method to delete all the files related
     * to the code.
     *
     * @param  String  $name   The name of the code to delete.
     * @param  String  $locale The locale of the code to delete.
     * @return boolean True if the function succeeds.
     */
    public static function deleteCode($name, $locale)
    {
        $folder = DATA_ROOT . RULES_REPO . "/$locale/$name";

        return Utils::deleteFolder($folder);
    }

    /**
     * Check if the code exists in the rule repository.
     *
     * @param  String  $name   The name of the code we search.
     * @param  String  $locale The locale of the code we search.
     * @return boolean True if the code exists.
     */
    public static function existCode($name, $locale)
    {
        $folder = DATA_ROOT . RULES_REPO . "/$locale/$name";

        return file_exists($folder);
    }
}

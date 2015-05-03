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

    public $codeId;
    public $codeName;
    public $codeLanguage;
    public $codeFolder;
    public $codePath;
    public static $codeIds = 0;

    public function Code($codeName, $codeLanguage)
    {
        self::$codeIds = self::$codeIds + 1;
        $this->codeId = self::$codeIds;
        $this->codeName = $codeName;
        $this->codeLanguage = $codeLanguage;
        $this->codeFolder = 'Code' . $this->codeId . '-' . $this->codeName;
    }

    public function createCode()
    {
        $this->codePath = '../../../../data/' . $this->codeFolder;
        if (!file_exists($this->codePath)) {
            mkdir($this->codePath, 0777, true);
            file_put_contents('rules.php', '');
            file_put_contents('exceptions.php', '');

            return true;
        }

        return false;
    }

    public function deleteCode()
    {
        if (is_dir($this->codePath)) {
            $objects = scandir($this->codePath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($this->codePath . "/" . $object) == "dir") {
                        deleteCode($this->codePath . "/" . $object);
                    } else {
                        unlink($this->codePath . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($this->codePath);

            return true;
        }

        return false;
    }
}

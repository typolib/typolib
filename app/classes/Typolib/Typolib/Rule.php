<?php
namespace Typolib;

/**
 * Rule class
 *
 * @package Typolib
 */
class Rule
{
    public $id;
    public $content;
    public $type;
    public static $ifThenRuleArray = [];
    public static $all_ids = [];

    public function __construct($name_code, $locale_code, $content, $type)
    {
        if (Code::existCode($name_code, $locale_code)) {
            $this->content = $content;
            $this->type = $type;
            $this->createRule($name_code, $locale_code);

            return true;
        }

        return false;
    }

    public function createRule($name_code, $locale_code)
    {
        $folder = DATA_ROOT . "code/$locale_code/$name_code/rules.php";
        $code = Rule::getArrayRules($name_code, $locale_code);
        $code['rules'][] = ['content' => $this->content, 'type' => $this->type];

        //Get the last inserted id
        end($code['rules']);
        $this->id = key($code['rules']);

        file_put_contents($folder, serialize($code));
    }

    public static function manageRule($name_code, $locale_code, $id, $action, $value = '')
    {
        $folder = DATA_ROOT . "code/$locale_code/$name_code/rules.php";

        $code = Rule::getArrayRules($name_code, $locale_code);
        if ($code != null && Rule::existRule($code, $id)) {
            switch ($action) {
                case 'delete':
                    unset($code['rules'][$id]);
                    break;

                case 'update_content':
                    $code['rules'][$id]['content'] = $value;
                    break;

                case 'update_type':
                    $code['rules'][$id]['type'] = $value;
                    break;
            }
            file_put_contents($folder, serialize($code));

            return true;
        }

        return false;
    }

    public static function existRule($code, $id)
    {
        return array_key_exists($id, $code['rules']);
    }

    public static function getArrayRules($name_code, $locale_code)
    {
        if (Code::existCode($name_code, $locale_code)) {
            $folder = DATA_ROOT . "code/$locale_code/$name_code/rules.php";

            return unserialize(file_get_contents($folder));
        }
    }

    /**
     * Add a "if x then y" rule to the global array
     *
     * @param string $userString the string entered by the user
     */
    public function addRuleToIfThenArrayRule($userString)
    {
        $pieces = explode(" ", $userString);
        $inputCharacter = $pieces[1];
        $newCharacter = $pieces[3];

        self::$ifThenRuleArray[$inputCharacter] = $newCharacter; // if a value with the same key is added, the previous value will be replaced by the new one
    }

    /**
     * Display all the rules of the "if then" array
     */
    public static function displayIfThenArrayRule()
    {
        foreach (self::$ifThenRuleArray as $key => $value) {
            echo "Input character: $key => New character: $value<br />\n";
        }
    }

    public static function generateRuleId()
    {
        $array = Rule::scanDirectory(DATA_ROOT . 'code');
        if (empty($array)) {
            $id = 0;
        } else {
            $id = max($array);
        }

        return ++$id;
    }

    public static function scanDirectory($dir)
    {
        if (is_dir($dir)) {
            $me = opendir($dir);
            while ($child = readdir($me)) {
                if ($child != '.' && $child != '..') {
                    $folder = $dir . DIRECTORY_SEPARATOR . $child;
                    if ($child == 'rules.php') {
                        $code = unserialize(file_get_contents($folder));
                        foreach (array_keys($code['rules']) as $key => $value) {
                            self::$all_ids[] = $value;
                        }
                    }
                    Rule::scanDirectory($folder);
                }
            }
            unset($code);
        }

        return self::$all_ids;
    }
}

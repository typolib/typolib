<?php
namespace Typolib;

/**
 * Rule class
 *
 * This class provides methods to manage a rule: create, delete or update,
 * check if a rule exists and get all the rules for a specific code.
 *
 * @package Typolib
 */
class Rule
{
    private $id;
    private $content;
    private $type;
    private static $ifThenRuleArray = [];
    private static $all_ids = [];

    /**
     * Constructor that initializes all the arguments then call the method to
     * create the rule if the code exists.
     *
     * @param  String  $name_code   The code name from which the rule depends.
     * @param  String  $locale_code The locale code from which the rule depends.
     * @param  String  $content     The content of the new rule.
     * @param  String  $type        The type of the new rule.
     * @return boolean True if the rule has been created.
     */
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

    /**
     * Creates a rule into the rules.php file located inside the code directory.
     *
     * @param String $name_code   The code name from which the rule depends.
     * @param String $locale_code The locale code from which the rule depends.
     */
    public function createRule($name_code, $locale_code)
    {
        $folder = DATA_ROOT . RULES_REPO . "/$locale_code/$name_code/rules.php";
        $code = Rule::getArrayRules($name_code, $locale_code);
        $code['rules'][] = ['content' => $this->content, 'type' => $this->type];

        //Get the last inserted id
        end($code['rules']);
        $this->id = key($code['rules']);

        file_put_contents($folder, serialize($code));
    }

    /**
     * Allows deleting a rule, or updating the content or the type of a rule.
     *
     * @param  String  $name_code   The code name from which the rule depends.
     * @param  String  $locale_code The locale code from which the rule depends.
     * @param  integer $id          The identity of the rule.
     * @param  String  $action      The action to perform: 'delete', 'update_content'
     *                              or 'update_type'.
     * @param  String  $value       The new content or type of the rule. If action
     *                              is 'delete' the value must be empty.
     * @return boolean True if the function succeeds.
     */
    public static function manageRule($name_code, $locale_code, $id, $action, $value = '')
    {
        $folder = DATA_ROOT . RULES_REPO . "/$locale_code/$name_code/rules.php";

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

    /**
     * Check if the rule exists in a rules array.
     *
     * @param  array   $code The array in which the rule must be searched.
     * @param  integer $id   The identity of the rule we search.
     * @return boolean True if the rule exists
     */
    public static function existRule($code, $id)
    {
        return array_key_exists($id, $code['rules']);
    }

    /**
     * Get an array of all the rules for a specific code.
     *
     * @param String $name_code   The code name from which the rules depend.
     * @param String $locale_code The locale code from which the rules depend.
     */
    public static function getArrayRules($name_code, $locale_code)
    {
        if (Code::existCode($name_code, $locale_code)) {
            $folder = DATA_ROOT . RULES_REPO . "/$locale_code/$name_code/rules.php";

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

    /**
     * Unused for now
     */
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

    /**
     * Scan the directory and put all the rules id in an array
     *
     * @param  String $dir The directory to be scanned.
     * @return array  $all_ids The array which contains all the rules id.
     */
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

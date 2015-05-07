<?php
namespace Typolib;

/**
 * Exception class
 *
 * This class provides methods to manage an exception: create, delete or update,
 * check if an exception exists and get all the exceptions for a specific code.
 *
 * @package Typolib
 */
class Exception
{
    private $id;
    private $content;
    private $rule_id;

    /**
     * Constructor that initializes all the arguments then call the method to create
     * the exception if the code and rule exist.
     *
     * @param  String  $code_name   The code name from which the exception depends.
     * @param  String  $code_locale The locale code from which the exception depends.
     * @param  integer $rule_id     The rule identity from which the exception depends.
     * @param  String  $content     The content of the new exception.
     * @return boolean True if the exception has been created.
     */
    public function __construct($code_name, $code_locale, $rule_id, $content)
    {
        $code = Rule::getArrayRules($code_name, $code_locale);
        if ($code != null && Rule::existRule($code, $rule_id)) {
            $this->content = $content;
            $this->rule_id = $rule_id;
            $this->createException($code_name, $code_locale);

            return true;
        }

        return false;
    }

    /**
     * Creates an exception into the exceptions.php file located inside the code
     * directory.
     *
     * @param String $code_name   The code name from which the exception depends.
     * @param String $code_locale The locale code from which the exception depends.
     */
    private function createException($code_name, $code_locale)
    {
        $folder = DATA_ROOT . RULES_REPO . "/$code_locale/$code_name/exceptions.php";
        $exception = Exception::getArrayExceptions($code_name, $code_locale);
        $exception['exceptions'][] = ['rule_id' => $this->rule_id,
                                      'content' => $this->content, ];

        //Get the last inserted id
        end($exception['exceptions']);
        $this->id = key($exception['exceptions']);

        file_put_contents($folder, serialize($exception));
    }

    /**
     * Allows deleting an exception, or updating the content of an exception.
     *
     * @param  String  $code_name   The code name from which the exception depends.
     * @param  String  $code_locale The locale code from which the exception depends.
     * @param  integer $id          The identity of the exception
     * @param  String  $action      The action to perform: 'delete' or 'update_content'
     * @param  String  $value       The new content of the exception. If action is
     *                              'delete' the value must be empty.
     * @return boolean True if the function succeeds.
     */
    public static function manageException($code_name, $code_locale, $id, $action, $value = '')
    {
        $folder = DATA_ROOT . RULES_REPO . "/$code_locale/$code_name/exceptions.php";

        $exception = Exception::getArrayExceptions($code_name, $code_locale);
        if ($exception != null && Exception::existException($exception, $id)) {
            switch ($action) {
                case 'delete':
                    unset($exception['exceptions'][$id]);
                    break;

                case 'update_content':
                    $exception['exceptions'][$id]['content'] = $value;
                    break;

            }
            file_put_contents($folder, serialize($exception));

            return true;
        }

        return false;
    }

    /**
     * Check if the exception exists in an exceptions array.
     *
     * @param  array   $exception The array in which the exception must be searched.
     * @param  integer $id        The identity of the exception we search.
     * @return boolean True if the exception exists
     */
    private static function existException($exception, $id)
    {
        return array_key_exists($id, $exception['exceptions']);
    }

    /**
     * Get an array of all the exceptions for a specific code.
     *
     * @param String $code_name   The code name from which the exceptions depend.
     * @param String $code_locale The locale code from which the exceptions depend.
     */
    private static function getArrayExceptions($code_name, $code_locale)
    {
        if (Code::existCode($code_name, $code_locale)) {
            $folder = DATA_ROOT . RULES_REPO . "/$code_locale/$code_name/exceptions.php";

            return unserialize(file_get_contents($folder));
        }
    }
}

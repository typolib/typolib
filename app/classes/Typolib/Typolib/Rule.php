<?php

class Rule
{
    // attributes
    public $ruleId;
    public $ruleContent;
    public $ruleType;
    public static $ifThenRuleArray = [];
    public static $ruleIds = 0;

    public function Rule()
    {
        self::$ruleIds = self::$ruleIds + 1;
        $this->ruleId = self::$ruleIds;
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
            echo "Input caracter : $key => New caracter : $value<br />\n";
        }
    }
}

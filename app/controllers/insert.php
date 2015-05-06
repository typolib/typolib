<?php

use Typolib\Code;
use Typolib\PullRequest;
use Typolib\Rule;

$code = new Code('firefox', 'fr');

$ru = new Rule('firefox', 'fr', 'regle test', 'ifthen');
Rule::manageRule('firefox', 'fr', 0, 'update_content', 'test switch');
//$code = new Code('typolib', 'fr');

//$ru = new Rule('typolib', 'fr', 'regle machin', 'ifthen');

$pr = new PullRequest("Great feature");
//$pr->createNewBranch();

$pr->getMasterSha();
/*
$file_name = DATA_ROOT . 'typolib-rules/test.php';
// Update content in repository
file_put_contents($file_name, "Règle 1\nRègle 2\n");

$pr->commitAndPush();

/*
if (isset($_GET['rule'])) {
    include MODELS . 'inserted.php';
    include VIEWS . 'inserted.php';
} else {
    include MODELS . 'insert.php';
    include VIEWS . 'insert.php';
}
*/

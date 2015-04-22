<?php

use Typolib\PullRequest;

$pr = new PullRequest("Great feature");
$pr->createNewBranch();

$file_name = DATA_ROOT . 'typolib/data/test.php';
// Update content in repository
file_put_contents($file_name, "Règle 1\nRègle 2\n");

$pr->commitAndPush();

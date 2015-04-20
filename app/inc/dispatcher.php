<?php
namespace Transvision;

$template     = true;
$page         = $urls[$url['path']];

$title = '<a href="/" id="typolib-title">Typolib’</a>';

switch ($url['path']) {
    case '/':
        $controller = 'insert';
        $show_title = false;
        break;
    case 'insert':
        $controller = 'insert';
        $page_title = 'Adding new rules';
        break;
    default:
        $controller = 'insert';
        break;
}

if ($template) {
    ob_start();

    if (isset($view)) {
        include VIEWS . $view . '.php';
    } else {
        include CONTROLLERS . $controller . '.php';
    }

    $content = ob_get_contents();
    ob_end_clean();

    // display the page
    //require_once VIEWS . 'templates/base.php';
} else {
    if (isset($view)) {
        include VIEWS . $view . '.php';
    } else {
        include CONTROLLERS . $controller . '.php';
    }
}

// Log script performance in PHP integrated developement server console
Utils::logScriptPerformances();

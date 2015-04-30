<?php

const PRODUCT = 'Typolib’';

// Bump this constant with each new release
const VERSION = '0.1dev';

// Constants for the project
define('INSTALL_ROOT',  $server_config['install'] . '/');
define('APP_SOURCES',   $server_config['config'] . '/sources/');
define('WEB_ROOT',      INSTALL_ROOT . 'web/');
define('APP_ROOT',      INSTALL_ROOT . 'app/');
define('DATA_ROOT',     INSTALL_ROOT . 'data/');
define('INC',           APP_ROOT . 'inc/');
define('VIEWS',         APP_ROOT . 'views/');
define('MODELS',        APP_ROOT . 'models/');
define('CONTROLLERS',   APP_ROOT . 'controllers/');
define('CACHE_ENABLED', isset($_GET['nocache']) ? false : true);
define('CACHE_PATH',    INSTALL_ROOT . 'cache/');

// Github configuration
define('TYPOLIB_GITHUB_ACCOUNT',  $server_config['typolib_github_account']);
define('CLIENT_GITHUB_ACCOUNT',   $server_config['client_github_account']);
define('CLIENT_GITHUB_PASSWORD',  $server_config['client_github_password']);
define('CLIENT_GITHUB_EMAIL',     $server_config['client_github_email']);
define('CLIENT_GITHUB_COMMITTER', $server_config['client_github_committer']);

if (file_exists(CACHE_PATH . 'lastdataupdate.txt')) {
    define('CACHE_TIME',  time() - filemtime(CACHE_PATH . 'lastdataupdate.txt'));
} else {
    // 05h20 cache (because we extract data every 6h and extraction lasts 25mn)
    define('CACHE_TIME',  19200);
}

// Special modes for the app
define('DEBUG', (strstr(VERSION, 'dev') || isset($_GET['debug'])) ? true : false);

// Set to True to log page time generation and memory used while in DEBUG mode
const PERF_CHECK = false;

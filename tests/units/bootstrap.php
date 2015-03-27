<?php
define('CACHE_PATH', realpath(__DIR__ . '/../testfiles/cache/') . '/');

// We always work with UTF8 encoding
mb_internal_encoding('UTF-8');

// Make sure we have a timezone set
date_default_timezone_set('Europe/Paris');

const DEBUG = true;
const CACHE_ENABLED = true;
const CACHE_TIME = 19200;

require __DIR__ . '/../../vendor/autoload.php';

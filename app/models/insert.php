<?php
use Transvision\Utils;

$ruletypes_selector = Utils::getHtmlSelectOptions(['test' => 'Test', 'ifthen' => 'If … then …', 'contains' => 'Contains', 'startswith' => 'Starts with'], 'ifthen', true);

$code_selector = Utils::getHtmlSelectOptions(['fr-firefox' => 'Firefox fr', 'fr-mozorg' => 'mozilla.org fr', 'fr-gaia' => 'Firefox OS fr'], 'fr-gaia', true);

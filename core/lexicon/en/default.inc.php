<?php

$_tmp = [
    'elements-path' => 'Root directory of file templates',
    'elements-path_desc' => 'This directory should contain files with the extension *.tpl for Fenom',
    'options' => 'Fenom options',
    'options_desc' => 'JSON encoded string with Fenom options, like {"disable_cache": true}',
    'use-php' => 'Use PHP',
    'use-php_desc' => 'You can enable the potentially dangerous use of PHP in templates',
    'use-modx' => 'Use MODX',
    'use-modx_desc' => 'You can enable the potentially dangerous use of MODX instance in templates',
];
/** @var array $_lang */
$_lang = array_merge($_lang, MMX\Fenom\App::prepareLexicon($_tmp, 'setting_' . MMX\Fenom\App::NAMESPACE));

unset($_tmp);
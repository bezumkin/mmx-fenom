<?php

$_tmp = [
    'elements-path' => 'Корневая директория файловых шаблонов',
    'elements-path_desc' => 'В этой директории должны лежать файлы с расширением *.tpl для Fenom',
    'options' => 'Настройки Fenom',
    'options_desc' => 'Закодированная в JSON строка с настройками, например {"disable_cache": true}',
    'use-php' => 'Использовать PHP',
    'use-php_desc' => 'Вы можете включить потенциально опасное использование PHP в шаблонах',
    'use-modx' => 'Использовать MODX',
    'use-modx_desc' => 'Вы можете включить потенциально опасное использование объекта MODX в шаблонах',
];
/** @var array $_lang */
$_lang = array_merge($_lang, MMX\Fenom\App::prepareLexicon($_tmp, 'setting_' . MMX\Fenom\App::NAMESPACE));

unset($_tmp);
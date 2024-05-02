<?php

namespace MMX\Fenom\Providers;

use Fenom\Provider;
use RuntimeException;

class FileProvider extends Provider
{
    protected string $path;

    public function __construct(string $path)
    {
        parent::__construct($path);
        $this->path = $path;
    }

    protected function _getTemplatePath($tpl): string
    {
        $ext = strtolower(pathinfo($tpl, PATHINFO_EXTENSION));
        if (!in_array($ext, ['tpl', 'html'])) {
            $tpl .= '.tpl';
        }

        try {
            return parent::_getTemplatePath($tpl);
        } catch (RuntimeException $e) {
            throw new RuntimeException('File template "' . $tpl . '" not found in "' . $this->path . '"');
        }
    }
}
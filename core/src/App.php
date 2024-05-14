<?php

namespace MMX\Fenom;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use MMX\Fenom\Models\ChunkTime;
use MMX\Fenom\Models\TemplateTime;
use MODX\Revolution\modSystemEvent;
use MODX\Revolution\modX;
use Throwable;

class App extends \Fenom
{
    public const NAME = 'mmxFenom';
    public const NAMESPACE = 'mmx-fenom';

    public modX $modx;

    public function __construct(modX $modx)
    {
        parent::__construct(new Providers\ChunkProvider());
        $this->modx = $modx;

        $this->setCompileDir(self::getCachePath());
        $this->addProvider('template', new Providers\TemplateProvider());

        $path = $this->modx->getOption(self::NAMESPACE . '.elements-path', null, MODX_CORE_PATH . 'elements/', true);
        if (is_readable($path)) {
            $this->addProvider('file', new Providers\FileProvider($path));
        } else {
            $this->logEntry(modX::LOG_LEVEL_INFO, '"' . $path . '" is not readable, file provider disabled');
        }

        $this->setDefaultOptions();
        $this->setDefaultModifiers();
    }

    public static function getCachePath(bool $create = true): string
    {
        $cache = MODX_CORE_PATH . 'cache/' . self::NAMESPACE;

        $fs = new Filesystem(new LocalFilesystemAdapter($cache));
        if ($create && !$fs->fileExists('/')) {
            $fs->createDirectory('/');
        }

        return $cache;
    }

    public static function clearCache(): void
    {
        $fs = new Filesystem(new LocalFilesystemAdapter(self::getCachePath(false)));
        $fs->deleteDirectory('/');
    }

    public function handleEvent(?modSystemEvent $event): void
    {
        if (!$event) {
            return;
        }

        if ($event->name === 'OnSiteRefresh') {
            $this::clearCache();
            $this->logEntry(modX::LOG_LEVEL_INFO, $this->modx->lexicon('refresh_default'));
        }

        if ($event->name === 'OnChunkSave' && $chunk = $event->params['chunk']) {
            ChunkTime::query()->updateOrCreate(['id' => $chunk->id], ['timestamp' => date('Y-m-d H:i:s')]);
        }

        if ($event->name === 'OnTemplateSave' && $template = $event->params['template']) {
            TemplateTime::query()->updateOrCreate(['id' => $template->id], ['timestamp' => date('Y-m-d H:i:s')]);
        }
    }

    public static function prepareLexicon(array $arr, string $prefix = ''): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            $key = !$prefix ? $k : "$prefix.$k";
            if (is_array($v)) {
                $out += self::prepareLexicon($v, $key);
            } else {
                $out[$key] = $v;
            }
        }

        return $out;
    }

    protected function setDefaultOptions(): void
    {
        $options = [
            'disable_cache' => false,
            'force_compile' => false,
            'auto_reload' => true,
            'force_verify' => true,
        ];
        try {
            $tmp = $this->modx->getOption(self::NAMESPACE . '.options', null, '{}', true);
            if ($tmp && ($tmp = json_decode($tmp, true, 512, JSON_THROW_ON_ERROR)) && is_array($tmp)) {
                $options = array_merge($options, $tmp);
            }
        } catch (Throwable $e) {
            $this->logEntry(modX::LOG_LEVEL_ERROR, 'Could not read options from system setting');
        }

        if (!$this->modx->getOption(self::NAMESPACE . '.use-php')) {
            $options['disable_native_funcs'] = true;
            $this->removeAccessor('php');
            $this->removeAccessor('call');
        }
        if ($this->modx->getOption(self::NAMESPACE . '.use-modx')) {
            $this->addAccessorSmart('modx', 'modx', $this::ACCESSOR_PROPERTY);
        }

        $this->setOptions($options);
    }

    protected function setDefaultModifiers(): void
    {
        $this->_modifiers['esc'] =
        $this->_modifiers['tag'] = static function ($string) {
            $string = preg_replace('/&amp;(#\d+|[a-z]+);/i', '&$1;', htmlspecialchars($string));

            return str_replace(['[', ']', '`', '{', '}'], ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'], $string);
        };

        $this->_modifiers['print'] = function ($var, $wrap = true) {
            $output = print_r($var, true);
            $output = $this->_modifiers['esc']($output);
            if ($wrap) {
                $output = '<pre>' . $output . '</pre>';
            }

            return $output;
        };

        $this->_modifiers['dump'] = function ($var, $wrap = true) {
            $output = var_export($var, true);
            $output = $this->_modifiers['esc']($output);
            if ($wrap) {
                $output = '<pre>' . $output . '</pre>';
            }

            return $output;
        };
    }

    protected function logEntry(int $level, string $message): void
    {
        $this->modx->log($level, '[' . $this::NAME . '] ' . $message);
    }

    public function fetch($template, array $vars = [])
    {
        try {
            return parent::fetch($template, $vars);
        } catch (Throwable $e) {
            $this->logEntry(modX::LOG_LEVEL_ERROR, $e->getMessage());
        }

        return '';
    }

    public function render($template, array $vars = []): string
    {
        return $this->fetch($template, $vars);
    }
}
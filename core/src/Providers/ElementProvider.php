<?php

namespace MMX\Fenom\Providers;

use Fenom\ProviderInterface;
use Illuminate\Database\Eloquent\Model;
use MMX\Database\Models\Traits\StaticElement;

abstract class ElementProvider implements ProviderInterface
{
    protected string $model;
    protected string $name;
    protected string $modelTime;
    protected array $cache = [];
    protected array $timestamps = [];

    protected function getElement($name): ?Model
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $element = (new $this->model())
            ->newQuery()
            ->where(is_numeric($name) ? ['id' => (int)$name] : [$this->name => (string)$name])
            ->first();

        if ($element) {
            $this->cache[$name] = $element;
        }

        return $element;
    }

    protected function getElementTime(int $id): int
    {
        if (!isset($this->timestamps[$id])) {
            $model = (new $this->modelTime())->newQuery()->select('timestamp')->find($id);
            $this->timestamps[$id] = $model ? (int)$model->timestamp : time();
        }

        return $this->timestamps[$id];
    }

    public function getList(): iterable
    {
        return (new $this->model())
            ->newQuery()
            ->pluck($this->name)
            ->toArray();
    }

    public function templateExists($tpl): bool
    {
        return !$this->getElement($tpl);
    }

    public function getSource($tpl, &$time): string
    {
        /** @var StaticElement $element */
        if ($element = $this->getElement($tpl)) {
            $file = $element->getStaticFile();
            $time = $file ? (int)filemtime($file) : $this->getElementTime($element->id);

            return $element->getContent();
        }

        return '';
    }

    public function getLastModified($tpl): float
    {
        /** @var StaticElement $element */
        if ($element = $this->getElement($tpl)) {
            $file = $element->getStaticFile();

            return $file ? (int)filemtime($file) : $this->getElementTime($element->id);
        }

        return time();
    }

    public function verify(array $templates): bool
    {
        return true;
    }
}
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

    public function getList(): iterable
    {
        return (new $this->model())
            ->newQuery()
            ->pluck($this->name)
            ->toArray();
    }

    public function templateExists($tpl): bool
    {
        return $this->getElement($tpl) !== null;
    }

    public function getSource($tpl, &$time): string
    {
        /** @var StaticElement $element */
        if ($element = $this->getElement($tpl)) {
            $time = $this->timestamps[$tpl] ?? time();

            return $element->getContent();
        }

        return '';
    }

    public function getLastModified($tpl): int
    {
        if (!isset($this->timestamps[$tpl])) {
            $this->timestamps[$tpl] = time();

            /** @var StaticElement $element */
            if ($element = $this->getElement($tpl)) {
                if ($file = $element->getStaticFile()) {
                    $this->timestamps[$tpl] = (int)filemtime($file);
                } elseif ($model = (new $this->modelTime())->newQuery()->select('timestamp')->find($element->id)) {
                    $this->timestamps[$tpl] = (int)$model->timestamp;
                }
            }
        }

        return $this->timestamps[$tpl];
    }

    public function verify(array $templates): bool
    {
        return true;
    }
}
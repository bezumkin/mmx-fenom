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

    protected function getElement($tpl): ?Model
    {
        return (new $this->model())
            ->newQuery()
            ->where(is_numeric($tpl) ? ['id' => (int)$tpl] : [$this->name => (string)$tpl])
            ->first();
    }

    protected function getElementTime(int $id): ?Model
    {
        return (new $this->modelTime())->newQuery()->find($id);
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
            if ($file = $element->getStaticFile()) {
                $time = (int)filemtime($file);
            } elseif ($timestamp = $this->getElementTime($element->id)) {
                $time = (int)strtotime($timestamp->timestamp);
            } else {
                $time = time();
            }

            return $element->getContent();
        }

        return '';
    }

    public function getLastModified($tpl): float
    {
        /** @var StaticElement $element */
        if ($element = $this->getElement($tpl)) {
            if ($file = $element->getStaticFile()) {
                return (int)filemtime($file);
            }
            if ($timestamp = $this->getElementTime($element->id)) {
                return (int)strtotime($timestamp->timestamp);
            }
        }

        return 0;
    }

    public function verify(array $templates): bool
    {
        return true;
    }
}
<?php

namespace MMX\Fenom\Providers;

use MMX\Database\Models\Template;
use MMX\Fenom\Models\TemplateTime;

class TemplateProvider extends ElementProvider
{
    protected string $model = Template::class;
    protected string $name = 'templatename';
    protected string $modelTime = TemplateTime::class;
}
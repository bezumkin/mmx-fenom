<?php

namespace MMX\Fenom\Providers;

use MMX\Database\Models\Chunk;
use MMX\Fenom\Models\ChunkTime;

class ChunkProvider extends ElementProvider
{
    protected string $model = Chunk::class;
    protected string $name = 'name';
    protected string $modelTime = ChunkTime::class;
}
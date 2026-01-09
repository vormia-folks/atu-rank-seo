<?php

namespace Vormia\ATURankSEO\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeoCacheInvalidated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $cacheKey
    ) {}
}

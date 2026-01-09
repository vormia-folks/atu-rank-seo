<?php

namespace Vormia\ATURankSEO\Events;

use Vormia\ATURankSEO\Models\RankSeoMedia;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaIndexed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RankSeoMedia $mediaSeo
    ) {}
}

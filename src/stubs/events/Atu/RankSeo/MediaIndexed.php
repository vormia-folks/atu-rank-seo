<?php

namespace Atu\RankSeo\Events;

use Atu\RankSeo\Models\RankSeoMedia;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaIndexed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RankSeoMedia $mediaSeo
    ) {}
}

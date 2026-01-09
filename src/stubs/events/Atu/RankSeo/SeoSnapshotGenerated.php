<?php

namespace Atu\RankSeo\Events;

use Atu\RankSeo\Models\RankSeoMeta;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeoSnapshotGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RankSeoMeta $seoMeta
    ) {}
}

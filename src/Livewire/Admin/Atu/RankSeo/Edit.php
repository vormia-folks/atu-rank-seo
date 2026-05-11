<?php

namespace Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts;
use Vormia\ATURankSEO\Models\RankSeoMeta;
use Vormia\ATURankSEO\Services\SeoSnapshotService;

class Edit extends Component
{
    use WithRankSeoToasts;

    public int|string|null $seoId = null;

    #[Validate('nullable|string|max:255')]
    public ?string $title = null;

    #[Validate('nullable|string')]
    public ?string $description = null;

    #[Validate('nullable|string')]
    public ?string $keywords = null;

    #[Validate('nullable|url|max:255')]
    public ?string $canonicalUrl = null;

    #[Validate('nullable|string|max:255')]
    public ?string $robots = null;

    public bool $useGlobal = true;

    public bool $isActive = true;

    public function mount(int|string $id): void
    {
        $this->seoId = $id;
        $seoMeta = RankSeoMeta::findOrFail($id);

        $this->title = $seoMeta->title;
        $this->description = $seoMeta->description;
        $this->keywords = $seoMeta->keywords;
        $this->canonicalUrl = $seoMeta->canonical_url;
        $this->robots = $seoMeta->robots;
        $this->useGlobal = (bool) $seoMeta->use_global;
        $this->isActive = (bool) $seoMeta->is_active;
    }

    public function update(SeoSnapshotService $seoSnapshotService)
    {
        $this->validate();

        try {
            $seoSnapshotService->updateSeo((int) $this->seoId, [
                'title' => $this->title,
                'description' => $this->description,
                'keywords' => $this->keywords,
                'canonical_url' => $this->canonicalUrl,
                'robots' => $this->robots,
                'use_global' => $this->useGlobal,
                'is_active' => $this->isActive,
            ]);

            $this->notifySuccess(__('SEO entry updated successfully.'));

            return redirect()->route('admin.atu.rank-seo.index');
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to update SEO entry: ').$e->getMessage());
        }
    }

    public function cancel(): void
    {
        $this->notifyInfo(__('Update cancelled.'));
    }

    #[Computed]
    public function seoMeta(): RankSeoMeta
    {
        return RankSeoMeta::findOrFail($this->seoId);
    }

    public function render()
    {
        return view('aturankseo::livewire.admin.atu.rank-seo.edit');
    }
}

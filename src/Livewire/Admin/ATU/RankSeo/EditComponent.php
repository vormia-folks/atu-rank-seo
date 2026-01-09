<?php

namespace App\Livewire\Admin\ATU\RankSeo;

use Livewire\Component;
use Vormia\ATURankSEO\Models\RankSeoMeta;
use Vormia\ATURankSEO\Services\SeoSnapshotService;
use Illuminate\Support\Facades\App;

class EditComponent extends Component
{
    public $seoId;
    public $title;
    public $description;
    public $keywords;
    public $canonicalUrl;
    public $robots;
    public $useGlobal = true;
    public $isActive = true;

    public function mount($id)
    {
        $this->seoId = $id;
        $seoMeta = RankSeoMeta::findOrFail($id);
        
        $this->title = $seoMeta->title;
        $this->description = $seoMeta->description;
        $this->keywords = $seoMeta->keywords;
        $this->canonicalUrl = $seoMeta->canonical_url;
        $this->robots = $seoMeta->robots;
        $this->useGlobal = $seoMeta->use_global;
        $this->isActive = $seoMeta->is_active;
    }

    public function save()
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'canonicalUrl' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:255',
            'useGlobal' => 'boolean',
            'isActive' => 'boolean',
        ]);

        $seoSnapshotService = App::make(SeoSnapshotService::class);
        $seoSnapshotService->updateSeo($this->seoId, [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'canonical_url' => $this->canonicalUrl,
            'robots' => $this->robots,
            'use_global' => $this->useGlobal,
            'is_active' => $this->isActive,
        ]);

        session()->flash('message', 'SEO entry updated successfully.');
        
        return redirect()->route('admin.atu.rank-seo.index');
    }

    public function render()
    {
        $seoMeta = RankSeoMeta::findOrFail($this->seoId);
        
        return view('livewire.admin.atu.rank-seo.edit', [
            'seoMeta' => $seoMeta,
        ]);
    }
}

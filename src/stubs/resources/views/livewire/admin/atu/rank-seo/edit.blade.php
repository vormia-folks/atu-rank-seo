<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Vormia\ATURankSEO\Models\RankSeoMeta;
use Vormia\ATURankSEO\Services\SeoSnapshotService;
use Illuminate\Support\Facades\App;

new class extends Component {
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

    #[Computed]
    public function seoMeta()
    {
        return RankSeoMeta::findOrFail($this->seoId);
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit SEO Entry</h2>
        <a href="{{ route('admin.atu.rank-seo.index') }}" class="btn btn-secondary">
            Back to List
        </a>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="save">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Slug Registry ID</label>
                    <input type="text" class="form-control" value="{{ $this->seoMeta->slug_registry_id ?? 'N/A' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <input type="text" class="form-control" value="{{ $this->seoMeta->type }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" wire:model="title" placeholder="Page title">
                    <small class="text-muted">Supports placeholders like {make}, {model}, {year}, {site_name}</small>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Meta Description</label>
                    <textarea class="form-control" id="description" wire:model="description" rows="3" placeholder="Meta description"></textarea>
                </div>

                <div class="mb-3">
                    <label for="keywords" class="form-label">Meta Keywords</label>
                    <textarea class="form-control" id="keywords" wire:model="keywords" rows="2" placeholder="Comma-separated keywords"></textarea>
                </div>

                <div class="mb-3">
                    <label for="canonicalUrl" class="form-label">Canonical URL</label>
                    <input type="url" class="form-control" id="canonicalUrl" wire:model="canonicalUrl" placeholder="https://example.com/page">
                </div>

                <div class="mb-3">
                    <label for="robots" class="form-label">Robots Meta</label>
                    <input type="text" class="form-control" id="robots" wire:model="robots" placeholder="noindex, nofollow">
                    <small class="text-muted">e.g., noindex, nofollow, index, follow</small>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model="useGlobal" id="useGlobal">
                        <label class="form-check-label" for="useGlobal">
                            Use Global SEO (merge with global defaults)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model="isActive" id="isActive">
                        <label class="form-check-label" for="isActive">
                            Active
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.atu.rank-seo.index') }}" class="btn btn-secondary me-2">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

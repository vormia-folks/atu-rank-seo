<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Vormia\ATURankSEO\Models\RankSeoMedia;

new class extends Component {
    public $mediaId;
    public $title;
    public $altText;
    public $caption;
    public $isActive = true;

    public function mount($id)
    {
        $this->mediaId = $id;
        $mediaSeo = RankSeoMedia::findOrFail($id);
        
        $this->title = $mediaSeo->title;
        $this->altText = $mediaSeo->alt_text;
        $this->caption = $mediaSeo->caption;
        $this->isActive = $mediaSeo->is_active;
    }

    public function save()
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'altText' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'isActive' => 'boolean',
        ]);

        $mediaSeo = RankSeoMedia::findOrFail($this->mediaId);
        $mediaSeo->update([
            'title' => $this->title,
            'alt_text' => $this->altText,
            'caption' => $this->caption,
            'is_active' => $this->isActive,
        ]);

        session()->flash('message', 'Media SEO entry updated successfully.');
        
        return redirect()->route('admin.atu.rank-seo.media.index');
    }

    #[Computed]
    public function mediaSeo()
    {
        return RankSeoMedia::findOrFail($this->mediaId);
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Media SEO</h2>
        <a href="{{ route('admin.atu.rank-seo.media.index') }}" class="btn btn-secondary">
            Back to Media List
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
                    <label class="form-label">Media URL</label>
                    <input type="text" class="form-control" value="{{ $this->mediaSeo->media_url }}" disabled>
                    @if ($this->mediaSeo->media_type === 'image')
                        <div class="mt-2">
                            <img src="{{ asset($this->mediaSeo->media_url) }}" alt="{{ $this->mediaSeo->alt_text }}" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Media Type</label>
                    <input type="text" class="form-control" value="{{ $this->mediaSeo->media_type }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" wire:model="title" placeholder="Media title">
                </div>

                <div class="mb-3">
                    <label for="altText" class="form-label">Alt Text</label>
                    <input type="text" class="form-control" id="altText" wire:model="altText" placeholder="Alt text for images">
                    <small class="text-muted">Important for accessibility and SEO</small>
                </div>

                <div class="mb-3">
                    <label for="caption" class="form-label">Caption</label>
                    <textarea class="form-control" id="caption" wire:model="caption" rows="3" placeholder="Media caption"></textarea>
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
                    <a href="{{ route('admin.atu.rank-seo.media.index') }}" class="btn btn-secondary me-2">
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

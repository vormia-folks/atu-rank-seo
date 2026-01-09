<?php

namespace App\Livewire\Admin\ATU\RankSeo;

use Livewire\Component;
use Vormia\ATURankSEO\Models\RankSeoMedia;

class MediaEditComponent extends Component
{
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

    public function render()
    {
        $mediaSeo = RankSeoMedia::findOrFail($this->mediaId);
        
        return view('livewire.admin.atu.rank-seo.media-edit', [
            'mediaSeo' => $mediaSeo,
        ]);
    }
}

<?php

namespace App\Livewire\Admin\ATU\RankSeo;

use Livewire\Component;
use Livewire\WithPagination;
use Vormia\ATURankSEO\Models\RankSeoMedia;

class MediaIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $activeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'activeFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive($id)
    {
        $mediaSeo = RankSeoMedia::findOrFail($id);
        $mediaSeo->update(['is_active' => !$mediaSeo->is_active]);
        
        session()->flash('message', 'Media SEO entry updated successfully.');
    }

    public function delete($id)
    {
        RankSeoMedia::findOrFail($id)->delete();
        session()->flash('message', 'Media SEO entry deleted successfully.');
    }

    public function render()
    {
        $query = RankSeoMedia::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('media_url', 'like', '%' . $this->search . '%')
                  ->orWhere('title', 'like', '%' . $this->search . '%')
                  ->orWhere('alt_text', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('media_type', $this->typeFilter);
        }

        if ($this->activeFilter !== '') {
            $query->where('is_active', $this->activeFilter === '1');
        }

        $mediaEntries = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('livewire.admin.atu.rank-seo.media-index', [
            'mediaEntries' => $mediaEntries,
        ]);
    }
}

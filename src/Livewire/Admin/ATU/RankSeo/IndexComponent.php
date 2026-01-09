<?php

namespace App\Livewire\Admin\ATU\RankSeo;

use Livewire\Component;
use Livewire\WithPagination;
use Vormia\ATURankSEO\Models\RankSeoMeta;

class IndexComponent extends Component
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
        $seoMeta = RankSeoMeta::findOrFail($id);
        $seoMeta->update(['is_active' => !$seoMeta->is_active]);
        
        session()->flash('message', 'SEO entry updated successfully.');
    }

    public function delete($id)
    {
        RankSeoMeta::findOrFail($id)->delete();
        session()->flash('message', 'SEO entry deleted successfully.');
    }

    public function render()
    {
        $query = RankSeoMeta::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->activeFilter !== '') {
            $query->where('is_active', $this->activeFilter === '1');
        }

        $seoEntries = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('livewire.admin.atu.rank-seo.index', [
            'seoEntries' => $seoEntries,
        ]);
    }
}

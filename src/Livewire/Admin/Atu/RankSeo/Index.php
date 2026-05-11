<?php

namespace Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts;
use Vormia\ATURankSEO\Models\RankSeoMeta;

class Index extends Component
{
    use WithPagination;
    use WithRankSeoToasts;

    public string $search = '';

    public string $typeFilter = '';

    public string $activeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'activeFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedActiveFilter(): void
    {
        $this->resetPage();
    }

    public function activate(int|string $id): void
    {
        try {
            $seoMeta = RankSeoMeta::findOrFail($id);
            $seoMeta->update(['is_active' => true]);
            $this->notifySuccess(__('SEO entry was activated successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to activate SEO entry: ').$e->getMessage());
        }
    }

    public function deactivate(int|string $id): void
    {
        try {
            $seoMeta = RankSeoMeta::findOrFail($id);
            $seoMeta->update(['is_active' => false]);
            $this->notifySuccess(__('SEO entry was deactivated successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to deactivate SEO entry: ').$e->getMessage());
        }
    }

    public function delete(int|string $id): void
    {
        try {
            $seoMeta = RankSeoMeta::findOrFail($id);
            $seoMeta->delete();
            $this->notifySuccess(__('SEO entry was deleted successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to delete SEO entry: ').$e->getMessage());
        }
    }

    #[Computed]
    public function seoEntries()
    {
        $query = RankSeoMeta::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->activeFilter !== '') {
            $query->where('is_active', $this->activeFilter === '1');
        }

        return $query->orderBy('updated_at', 'desc')->paginate(15);
    }

    public function render()
    {
        return view('aturankseo::livewire.admin.atu.rank-seo.index');
    }
}

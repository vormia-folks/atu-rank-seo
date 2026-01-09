<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Vormia\ATURankSEO\Models\RankSeoMeta;

new class extends Component {
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

    #[Computed]
    public function seoEntries()
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

        return $query->orderBy('updated_at', 'desc')->paginate(15);
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>SEO Entries</h2>
        <a href="{{ route('admin.atu.rank-seo.settings') }}" class="btn btn-secondary">
            Global Settings
        </a>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search by title or description...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="page">Page</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="activeFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.atu.rank-seo.media.index') }}" class="btn btn-outline-primary w-100">
                        Media SEO
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SEO Entries Table -->
    <div class="card">
        <div class="card-body">
            @if ($this->seoEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Slug Registry ID</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->seoEntries as $entry)
                                <tr>
                                    <td>{{ $entry->slug_registry_id ?? 'N/A' }}</td>
                                    <td><span class="badge bg-info">{{ $entry->type }}</span></td>
                                    <td>{{ Str::limit($entry->title ?? 'N/A', 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $entry->is_active ? 'success' : 'secondary' }}">
                                            {{ $entry->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $entry->updated_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.atu.rank-seo.edit', $entry->id) }}" class="btn btn-primary">
                                                Edit
                                            </a>
                                            <button wire:click="toggleActive({{ $entry->id }})" class="btn btn-{{ $entry->is_active ? 'warning' : 'success' }}">
                                                {{ $entry->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button wire:click="delete({{ $entry->id }})" 
                                                    wire:confirm="Are you sure you want to delete this SEO entry?"
                                                    class="btn btn-danger">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $this->seoEntries->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">No SEO entries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

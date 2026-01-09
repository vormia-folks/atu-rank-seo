<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Vormia\ATURankSEO\Models\RankSeoMedia;

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
        $mediaSeo = RankSeoMedia::findOrFail($id);
        $mediaSeo->update(['is_active' => !$mediaSeo->is_active]);
        
        session()->flash('message', 'Media SEO entry updated successfully.');
    }

    public function delete($id)
    {
        RankSeoMedia::findOrFail($id)->delete();
        session()->flash('message', 'Media SEO entry deleted successfully.');
    }

    #[Computed]
    public function mediaEntries()
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

        return $query->orderBy('updated_at', 'desc')->paginate(15);
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Media SEO Manager</h2>
        <div>
            <a href="{{ route('admin.atu.rank-seo.index') }}" class="btn btn-secondary">
                SEO Entries
            </a>
        </div>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search by URL, title, or alt text...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="image">Image</option>
                        <option value="file">File</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="activeFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Entries Table -->
    <div class="card">
        <div class="card-body">
            @if ($this->mediaEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Media URL</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Alt Text</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->mediaEntries as $entry)
                                <tr>
                                    <td>
                                        <code class="small">{{ Str::limit($entry->media_url, 40) }}</code>
                                        @if ($entry->media_type === 'image')
                                            <br>
                                            <img src="{{ asset($entry->media_url) }}" alt="{{ $entry->alt_text }}" style="max-width: 50px; max-height: 50px;" class="mt-1">
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{ $entry->media_type === 'image' ? 'primary' : 'secondary' }}">{{ $entry->media_type }}</span></td>
                                    <td>{{ Str::limit($entry->title ?? 'N/A', 30) }}</td>
                                    <td>{{ Str::limit($entry->alt_text ?? 'N/A', 30) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $entry->is_active ? 'success' : 'secondary' }}">
                                            {{ $entry->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $entry->updated_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.atu.rank-seo.media.edit', $entry->id) }}" class="btn btn-primary">
                                                Edit
                                            </a>
                                            <button wire:click="toggleActive({{ $entry->id }})" class="btn btn-{{ $entry->is_active ? 'warning' : 'success' }}">
                                                {{ $entry->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button wire:click="delete({{ $entry->id }})" 
                                                    wire:confirm="Are you sure you want to delete this media SEO entry?"
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
                    {{ $this->mediaEntries->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">No media SEO entries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

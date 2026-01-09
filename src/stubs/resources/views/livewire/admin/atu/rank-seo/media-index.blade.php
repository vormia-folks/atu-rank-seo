<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Atu\RankSeo\Models\RankSeoMedia;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithPagination;
    use WithNotifications;

    public $search = '';
    public $typeFilter = '';
    public $activeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'activeFilter' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedActiveFilter()
    {
        $this->resetPage();
    }

    public function activate($id)
    {
        try {
            $mediaSeo = RankSeoMedia::findOrFail($id);
            $mediaSeo->update(['is_active' => true]);
            $this->notifySuccess(__('Media SEO entry was activated successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to activate media SEO entry: ' . $e->getMessage()));
        }
    }

    public function deactivate($id)
    {
        try {
            $mediaSeo = RankSeoMedia::findOrFail($id);
            $mediaSeo->update(['is_active' => false]);
            $this->notifySuccess(__('Media SEO entry was deactivated successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to deactivate media SEO entry: ' . $e->getMessage()));
        }
    }

    public function delete($id)
    {
        try {
            $mediaSeo = RankSeoMedia::findOrFail($id);
            $mediaSeo->delete();
            $this->notifySuccess(__('Media SEO entry was deleted successfully!'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to delete media SEO entry: ' . $e->getMessage()));
        }
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
    <x-admin-panel>
        <x-slot name="header">{{ __('Media SEO Manager') }}</x-slot>
        <x-slot name="desc">
            {{ __('Manage SEO metadata for media files.') }}
            {{ __('You can edit, enable/disable, or delete media SEO entries here.') }}
        </x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.atu.rank-seo.index') }}"
                class="bg-black dark:bg-gray-700 text-white hover:bg-gray-800 dark:hover:bg-gray-600 px-3 py-2 rounded-md float-right text-sm font-bold">
                SEO Entries
            </a>
        </x-slot>

        {{-- Search & Filter --}}
        <div class="my-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Search & Filter data</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                placeholder="Search by URL, title, or alt text..." />
                        </div>
                        <div>
                            <select wire:model.live="typeFilter"
                                class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                <option value="">All Types</option>
                                <option value="image">Image</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="activeFilter"
                                class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Display notifications --}}
        {!! $this->renderNotification() !!}

        {{-- Table --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg mt-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-3">Media URL</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Type</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Title</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Alt Text</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Last Updated</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        @if ($this->mediaEntries->isNotEmpty())
                            @foreach ($this->mediaEntries as $entry)
                                <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100 sm:pl-3">
                                        <code class="text-xs text-gray-600 dark:text-gray-400">{{ Str::limit($entry->media_url, 40) }}</code>
                                        @if ($entry->media_type === 'image')
                                            <div class="mt-1">
                                                <img src="{{ asset($entry->media_url) }}" alt="{{ $entry->alt_text }}" 
                                                    class="max-w-[50px] max-h-[50px] object-cover rounded-md border border-gray-300 dark:border-gray-600" />
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm {{ $entry->media_type === 'image' ? 'bg-blue-400' : 'bg-gray-400' }} text-white">
                                            {{ $entry->media_type }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ Str::limit($entry->title ?? 'N/A', 30) }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ Str::limit($entry->alt_text ?? 'N/A', 30) }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        @if ($entry->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-green-400 text-white">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-sm bg-red-400 text-white">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $entry->updated_at->format('Y-m-d H:i') }}</td>
                                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                        <div class="flex items-center justify-end gap-x-2">
                                            {{-- Edit Button --}}
                                            <a href="{{ route('admin.atu.rank-seo.media.edit', $entry->id) }}"
                                                class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                Edit
                                            </a>

                                            {{-- Activate Button --}}
                                            @if (!$entry->is_active)
                                                <button type="button" wire:click="activate({{ $entry->id }})"
                                                    class="inline-flex items-center gap-x-1.5 rounded-md bg-green-600 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-green-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                                        <path fill-rule="evenodd"
                                                            d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- Deactivate Button --}}
                                            @if ($entry->is_active)
                                                <button type="button" wire:click="deactivate({{ $entry->id }})"
                                                    class="inline-flex items-center gap-x-1.5 rounded-md bg-yellow-400 px-2.5 py-1 text-sm font-semibold text-white shadow-xs hover:bg-yellow-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                                        <path fill-rule="evenodd"
                                                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            @endif

                                            {{-- Delete Button --}}
                                            <button type="button" wire:click="$js.confirmDelete({{ $entry->id }})"
                                                class="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="even:bg-gray-50 dark:even:bg-gray-800/50">
                                <td colspan="7"
                                    class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100 sm:pl-3 text-center">
                                    <span class="text-gray-500 dark:text-gray-400 text-2xl font-bold">No results found</span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            @if ($this->mediaEntries->hasPages())
                <div class="p-2">
                    {{ $this->mediaEntries->links() }}
                </div>
            @endif
        </div>
    </x-admin-panel>

    @script
        <script>
            $js('confirmDelete', (id) => {
                Swal.fire({
                    title: 'Are you sure you want to delete?',
                    text: "This media SEO entry will be removed permanently.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.delete(id);
                    }
                });
            });
        </script>
    @endscript
</div>

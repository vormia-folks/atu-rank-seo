<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Atu\RankSeo\Models\RankSeoMeta;
use Atu\RankSeo\Services\SeoSnapshotService;
use Illuminate\Support\Facades\App;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;

    public $seoId;
    
    #[Validate('nullable|string|max:255')]
    public $title;
    
    #[Validate('nullable|string')]
    public $description;
    
    #[Validate('nullable|string')]
    public $keywords;
    
    #[Validate('nullable|url|max:255')]
    public $canonicalUrl;
    
    #[Validate('nullable|string|max:255')]
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

    public function update()
    {
        $this->validate();

        try {
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

            $this->notifySuccess(__('SEO entry updated successfully.'));
            
            return redirect()->route('admin.atu.rank-seo.index');
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to update SEO entry: ' . $e->getMessage()));
        }
    }

    public function cancel()
    {
        $this->notifyInfo(__('Update cancelled.'));
    }

    #[Computed]
    public function seoMeta()
    {
        return RankSeoMeta::findOrFail($this->seoId);
    }
}; ?>

<div>
    <x-admin-panel>
        <x-slot name="header">{{ __('Edit SEO Entry') }}</x-slot>
        <x-slot name="desc">
            {{ __('Update the SEO settings for this entry.') }}
        </x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.atu.rank-seo.index') }}"
                class="bg-black dark:bg-gray-700 text-white hover:bg-gray-800 dark:hover:bg-gray-600 px-3 py-2 rounded-md float-right text-sm font-bold">
                Go Back
            </a>
        </x-slot>

        {{-- Form Container --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
            {{-- Display notifications --}}
            {!! $this->renderNotification() !!}

            <form wire:submit="update">
                <div class="space-y-12">
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        {{-- Left Column: Field Descriptions --}}
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900 dark:text-gray-100">SEO Information</h2>
                            <p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-300">
                                Configure the SEO meta tags for this entry. You can use placeholders like {make}, {model}, {year}, {site_name} in the title and description fields.
                            </p>
                        </div>

                        {{-- Right Column: Form Fields --}}
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            {{-- Slug Registry ID (Read-only) --}}
                            <div class="col-span-full">
                                <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Slug Registry ID</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-gray-50 dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600">
                                        <input type="text" value="{{ $this->seoMeta->slug_registry_id ?? 'N/A' }}" disabled
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-500 dark:text-gray-400 sm:text-sm/6 bg-gray-50 dark:bg-gray-700" />
                                    </div>
                                </div>
                            </div>

                            {{-- Type (Read-only) --}}
                            <div class="col-span-full">
                                <label class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Type</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-gray-50 dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600">
                                        <input type="text" value="{{ $this->seoMeta->type }}" disabled
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-500 dark:text-gray-400 sm:text-sm/6 bg-gray-50 dark:bg-gray-700" />
                                    </div>
                                </div>
                            </div>

                            {{-- Title Field --}}
                            <div class="col-span-full">
                                <label for="title" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100 required">Title</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="text" id="title" wire:model="title" placeholder="Page title"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('title') }}</span>
                                </div>
                                <p class="mt-3 text-sm/6 text-gray-600 dark:text-gray-300">Supports placeholders like {make}, {model}, {year}, {site_name}</p>
                            </div>

                            {{-- Description Field --}}
                            <div class="col-span-full">
                                <label for="description" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Meta Description</label>
                                <div class="mt-2">
                                    <textarea id="description" wire:model="description" rows="3" placeholder="Meta description"
                                        class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('description') }}</span>
                                </div>
                            </div>

                            {{-- Keywords Field --}}
                            <div class="col-span-full">
                                <label for="keywords" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Meta Keywords</label>
                                <div class="mt-2">
                                    <textarea id="keywords" wire:model="keywords" rows="2" placeholder="Comma-separated keywords"
                                        class="block w-full rounded-md bg-white dark:bg-gray-700 px-3 py-1.5 text-base text-gray-900 dark:text-gray-100 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('keywords') }}</span>
                                </div>
                            </div>

                            {{-- Canonical URL Field --}}
                            <div class="col-span-full">
                                <label for="canonicalUrl" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Canonical URL</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="url" id="canonicalUrl" wire:model="canonicalUrl" placeholder="https://example.com/page"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('canonicalUrl') }}</span>
                                </div>
                            </div>

                            {{-- Robots Field --}}
                            <div class="col-span-full">
                                <label for="robots" class="block text-sm/6 font-medium text-gray-900 dark:text-gray-100">Robots Meta</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white dark:bg-gray-700 pl-3 outline-1 -outline-offset-1 outline-gray-300 dark:outline-gray-600 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="text" id="robots" wire:model="robots" placeholder="noindex, nofollow"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('robots') }}</span>
                                </div>
                                <p class="mt-3 text-sm/6 text-gray-600 dark:text-gray-300">e.g., noindex, nofollow, index, follow</p>
                            </div>

                            {{-- Use Global Checkbox --}}
                            <div class="col-span-full">
                                <label class="flex items-center">
                                    <input type="checkbox" id="useGlobal" wire:model="useGlobal"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">Use Global SEO (merge with global defaults)</span>
                                </label>
                            </div>

                            {{-- Is Active Checkbox --}}
                            <div class="col-span-full">
                                <label class="flex items-center">
                                    <input type="checkbox" id="isActive" wire:model="isActive"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">Active</span>
                                </label>
                            </div>

                            {{-- Form Actions --}}
                            <div class="col-span-full">
                                <div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 dark:border-gray-100/10 pt-4">
                                    <button type="button" wire:click="cancel"
                                        class="text-sm font-semibold text-gray-900 dark:text-gray-100">Cancel</button>

                                    <button type="submit" wire:loading.attr="disabled"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        <span wire:loading.remove>Update Changes</span>
                                        <span wire:loading>Updating...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </x-admin-panel>
</div>

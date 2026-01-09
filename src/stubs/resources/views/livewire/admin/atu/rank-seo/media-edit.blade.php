<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Vormia\ATURankSEO\Models\RankSeoMedia;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;

    public $mediaId;
    
    #[Validate('nullable|string|max:255')]
    public $title;
    
    #[Validate('nullable|string|max:255')]
    public $altText;
    
    #[Validate('nullable|string')]
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

    public function update()
    {
        $this->validate();

        try {
            $mediaSeo = RankSeoMedia::findOrFail($this->mediaId);
            $mediaSeo->update([
                'title' => $this->title,
                'alt_text' => $this->altText,
                'caption' => $this->caption,
                'is_active' => $this->isActive,
            ]);

            $this->notifySuccess(__('Media SEO entry updated successfully.'));
            
            return redirect()->route('admin.atu.rank-seo.media.index');
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to update media SEO entry: ' . $e->getMessage()));
        }
    }

    public function cancel()
    {
        $this->notifyInfo(__('Update cancelled.'));
    }

    #[Computed]
    public function mediaSeo()
    {
        return RankSeoMedia::findOrFail($this->mediaId);
    }
}; ?>

<div>
    <x-admin-panel>
        <x-slot name="header">{{ __('Edit Media SEO') }}</x-slot>
        <x-slot name="desc">
            {{ __('Update the SEO settings for this media file.') }}
        </x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.atu.rank-seo.media.index') }}"
                class="bg-black text-white hover:bg-gray-800 px-3 py-2 rounded-md float-right text-sm font-bold">
                Go Back
            </a>
        </x-slot>

        {{-- Form Container --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
            {{-- Display notifications --}}
            {!! $this->renderNotification() !!}

            <form wire:submit="update">
                <div class="space-y-12">
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        {{-- Left Column: Field Descriptions --}}
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Media Information</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">
                                Configure the SEO metadata for this media file. Alt text is important for accessibility and SEO.
                            </p>
                        </div>

                        {{-- Right Column: Form Fields --}}
                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            {{-- Media URL (Read-only) --}}
                            <div class="col-span-full">
                                <label class="block text-sm/6 font-medium text-gray-900">Media URL</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-gray-50 pl-3 outline-1 -outline-offset-1 outline-gray-300">
                                        <input type="text" value="{{ $this->mediaSeo->media_url }}" disabled
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-500 sm:text-sm/6 bg-gray-50" />
                                    </div>
                                </div>
                                @if ($this->mediaSeo->media_type === 'image')
                                    <div class="mt-2">
                                        <img src="{{ asset($this->mediaSeo->media_url) }}" alt="{{ $this->mediaSeo->alt_text }}" 
                                            class="max-w-[200px] max-h-[200px] object-cover rounded-md border border-gray-300" />
                                    </div>
                                @endif
                            </div>

                            {{-- Media Type (Read-only) --}}
                            <div class="col-span-full">
                                <label class="block text-sm/6 font-medium text-gray-900">Media Type</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-gray-50 pl-3 outline-1 -outline-offset-1 outline-gray-300">
                                        <input type="text" value="{{ $this->mediaSeo->media_type }}" disabled
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-500 sm:text-sm/6 bg-gray-50" />
                                    </div>
                                </div>
                            </div>

                            {{-- Title Field --}}
                            <div class="col-span-full">
                                <label for="title" class="block text-sm/6 font-medium text-gray-900">Title</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="text" id="title" wire:model="title" placeholder="Media title"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('title') }}</span>
                                </div>
                            </div>

                            {{-- Alt Text Field --}}
                            <div class="col-span-full">
                                <label for="altText" class="block text-sm/6 font-medium text-gray-900">Alt Text</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="text" id="altText" wire:model="altText" placeholder="Alt text for images"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('altText') }}</span>
                                </div>
                                <p class="mt-3 text-sm/6 text-gray-600">Important for accessibility and SEO</p>
                            </div>

                            {{-- Caption Field --}}
                            <div class="col-span-full">
                                <label for="caption" class="block text-sm/6 font-medium text-gray-900">Caption</label>
                                <div class="mt-2">
                                    <textarea id="caption" wire:model="caption" rows="3" placeholder="Media caption"
                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('caption') }}</span>
                                </div>
                            </div>

                            {{-- Is Active Checkbox --}}
                            <div class="col-span-full">
                                <div class="flex items-center">
                                    <input type="checkbox" id="isActive" wire:model="isActive"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                                    <label for="isActive" class="ml-3 block text-sm/6 font-medium text-gray-900">
                                        Active
                                    </label>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="col-span-full">
                                <div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 pt-4">
                                    <button type="button" wire:click="cancel"
                                        class="text-sm font-semibold text-gray-900">Cancel</button>

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

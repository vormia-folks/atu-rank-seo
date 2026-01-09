<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Vormia\ATURankSEO\Models\RankSeoSettings;
use App\Traits\Vrm\Livewire\WithNotifications;

new class extends Component {
    use WithNotifications;

    public $isEnabled;
    
    #[Validate('nullable|string|max:255')]
    public $globalTitle;
    
    #[Validate('nullable|string')]
    public $globalDescription;
    
    #[Validate('nullable|string')]
    public $globalKeywords;
    
    public $dynamicVariables = [];
    public $newVariableKey = '';
    public $newVariableValue = '';

    public function mount()
    {
        $settings = RankSeoSettings::getInstance();
        $this->isEnabled = $settings->is_enabled;
        $this->globalTitle = $settings->global_title;
        $this->globalDescription = $settings->global_description;
        $this->globalKeywords = $settings->global_keywords;
        $this->dynamicVariables = $settings->dynamic_variables ?? [];
    }

    public function save()
    {
        $this->validate([
            'isEnabled' => 'boolean',
            'globalTitle' => 'nullable|string|max:255',
            'globalDescription' => 'nullable|string',
            'globalKeywords' => 'nullable|string',
        ]);

        try {
            $settings = RankSeoSettings::getInstance();
            $settings->update([
                'is_enabled' => $this->isEnabled,
                'global_title' => $this->globalTitle,
                'global_description' => $this->globalDescription,
                'global_keywords' => $this->globalKeywords,
                'dynamic_variables' => $this->dynamicVariables,
            ]);

            $this->notifySuccess(__('Settings saved successfully.'));
        } catch (\Exception $e) {
            $this->notifyError(__('Failed to save settings: ' . $e->getMessage()));
        }
    }

    public function addVariable()
    {
        if ($this->newVariableKey && $this->newVariableValue) {
            $this->dynamicVariables[$this->newVariableKey] = $this->newVariableValue;
            $this->newVariableKey = '';
            $this->newVariableValue = '';
            $this->notifySuccess(__('Variable added successfully.'));
        } else {
            $this->notifyError(__('Please provide both variable name and value.'));
        }
    }

    public function removeVariable($key)
    {
        if (isset($this->dynamicVariables[$key])) {
            unset($this->dynamicVariables[$key]);
            $this->notifySuccess(__('Variable removed successfully.'));
        }
    }

    public function cancel()
    {
        $this->notifyInfo(__('Settings update cancelled.'));
    }
}; ?>

<div>
    <x-admin-panel>
        <x-slot name="header">{{ __('Global SEO Settings') }}</x-slot>
        <x-slot name="desc">
            {{ __('Configure global SEO defaults and dynamic variables for your website.') }}
        </x-slot>
        <x-slot name="button">
            <a href="{{ route('admin.atu.rank-seo.index') }}"
                class="bg-black text-white hover:bg-gray-800 px-3 py-2 rounded-md float-right text-sm font-bold">
                Back to SEO Entries
            </a>
        </x-slot>

        {{-- Form Container --}}
        <div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg px-4 py-5 mb-5 sm:p-6">
            {{-- Display notifications --}}
            {!! $this->renderNotification() !!}

            <form wire:submit="save">
                <div class="space-y-12">
                    {{-- Master Settings Section --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Master Settings</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">
                                Enable or disable SEO functionality globally. When disabled, all SEO meta outputs are ignored.
                            </p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full">
                                <div class="flex items-center">
                                    <input type="checkbox" id="isEnabled" wire:model="isEnabled"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" />
                                    <label for="isEnabled" class="ml-3 block text-sm/6 font-medium text-gray-900">
                                        Enable SEO Functionality
                                    </label>
                                </div>
                                <p class="mt-2 text-sm/6 text-gray-600">When disabled, all SEO meta outputs are ignored.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Global SEO Defaults Section --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Global SEO Defaults</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">
                                These values are used when page-specific SEO is not set or when "Use Global" is enabled on individual entries.
                            </p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full">
                                <label for="globalTitle" class="block text-sm/6 font-medium text-gray-900">Global Title</label>
                                <div class="mt-2">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                        <input type="text" id="globalTitle" wire:model="globalTitle" placeholder="Default page title"
                                            class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                    </div>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('globalTitle') }}</span>
                                </div>
                                <p class="mt-3 text-sm/6 text-gray-600">Used when page-specific SEO is not set or when "Use Global" is enabled.</p>
                            </div>

                            <div class="col-span-full">
                                <label for="globalDescription" class="block text-sm/6 font-medium text-gray-900">Global Description</label>
                                <div class="mt-2">
                                    <textarea id="globalDescription" wire:model="globalDescription" rows="3" placeholder="Default meta description"
                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('globalDescription') }}</span>
                                </div>
                            </div>

                            <div class="col-span-full">
                                <label for="globalKeywords" class="block text-sm/6 font-medium text-gray-900">Global Keywords</label>
                                <div class="mt-2">
                                    <textarea id="globalKeywords" wire:model="globalKeywords" rows="2" placeholder="Default meta keywords (comma-separated)"
                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"></textarea>
                                    <span class="text-red-500 text-sm italic"> {{ $errors->first('globalKeywords') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Variables Section --}}
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Dynamic Variables</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">
                                These variables can be used in SEO placeholders like {site_name}, {current_year}, etc.
                            </p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            @if (count($dynamicVariables) > 0)
                                <div class="col-span-full">
                                    <div class="overflow-hidden shadow-sm ring-1 ring-black/5 sm:rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">Variable Name</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Value</th>
                                                    <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">
                                                        <span class="sr-only">Actions</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                @foreach ($dynamicVariables as $key => $value)
                                                    <tr class="even:bg-gray-50">
                                                        <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                                            <code class="text-xs text-gray-600">{ {{ $key }} }</code>
                                                        </td>
                                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $value }}</td>
                                                        <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                                            <button type="button" wire:click="removeVariable('{{ $key }}')"
                                                                class="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs hover:bg-red-500">
                                                                Remove
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <div class="col-span-full">
                                <label class="block text-sm/6 font-medium text-gray-900">Add New Variable</label>
                                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-12">
                                    <div class="sm:col-span-4">
                                        <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input type="text" wire:model="newVariableKey" placeholder="Variable name (e.g., site_name)"
                                                class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                    <div class="sm:col-span-6">
                                        <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                                            <input type="text" wire:model="newVariableValue" placeholder="Variable value"
                                                class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <button type="button" wire:click="addVariable"
                                            class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="col-span-full">
                                <div class="flex items-center justify-end gap-x-3 border-t border-gray-900/10 pt-4">
                                    <button type="button" wire:click="cancel"
                                        class="text-sm font-semibold text-gray-900">Cancel</button>

                                    <button type="submit" wire:loading.attr="disabled"
                                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        <span wire:loading.remove>Save Settings</span>
                                        <span wire:loading>Saving...</span>
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

<?php

use Livewire\Volt\Component;
use Vormia\ATURankSEO\Models\RankSeoSettings;

new class extends Component {
    public $isEnabled;
    public $globalTitle;
    public $globalDescription;
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

        $settings = RankSeoSettings::getInstance();
        $settings->update([
            'is_enabled' => $this->isEnabled,
            'global_title' => $this->globalTitle,
            'global_description' => $this->globalDescription,
            'global_keywords' => $this->globalKeywords,
            'dynamic_variables' => $this->dynamicVariables,
        ]);

        session()->flash('message', 'Settings saved successfully.');
    }

    public function addVariable()
    {
        if ($this->newVariableKey && $this->newVariableValue) {
            $this->dynamicVariables[$this->newVariableKey] = $this->newVariableValue;
            $this->newVariableKey = '';
            $this->newVariableValue = '';
        }
    }

    public function removeVariable($key)
    {
        unset($this->dynamicVariables[$key]);
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Global SEO Settings</h2>
        <a href="{{ route('admin.atu.rank-seo.index') }}" class="btn btn-secondary">
            Back to SEO Entries
        </a>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="save">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Master Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model="isEnabled" id="isEnabled">
                        <label class="form-check-label" for="isEnabled">
                            Enable SEO Functionality
                        </label>
                    </div>
                    <small class="text-muted">When disabled, all SEO meta outputs are ignored.</small>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Global SEO Defaults</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="globalTitle" class="form-label">Global Title</label>
                    <input type="text" class="form-control" id="globalTitle" wire:model="globalTitle" placeholder="Default page title">
                    <small class="text-muted">Used when page-specific SEO is not set or when "Use Global" is enabled.</small>
                </div>

                <div class="mb-3">
                    <label for="globalDescription" class="form-label">Global Description</label>
                    <textarea class="form-control" id="globalDescription" wire:model="globalDescription" rows="3" placeholder="Default meta description"></textarea>
                </div>

                <div class="mb-3">
                    <label for="globalKeywords" class="form-label">Global Keywords</label>
                    <textarea class="form-control" id="globalKeywords" wire:model="globalKeywords" rows="2" placeholder="Default meta keywords (comma-separated)"></textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Dynamic Variables</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">These variables can be used in SEO placeholders like {site_name}, {current_year}, etc.</p>

                @if (count($dynamicVariables) > 0)
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Variable Name</th>
                                    <th>Value</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dynamicVariables as $key => $value)
                                    <tr>
                                        <td><code>{ {{ $key }} }</code></td>
                                        <td>{{ $value }}</td>
                                        <td>
                                            <button type="button" wire:click="removeVariable('{{ $key }}')" class="btn btn-sm btn-danger">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" wire:model="newVariableKey" placeholder="Variable name (e.g., site_name)">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" wire:model="newVariableValue" placeholder="Variable value">
                    </div>
                    <div class="col-md-2">
                        <button type="button" wire:click="addVariable" class="btn btn-primary w-100">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                Save Settings
            </button>
        </div>
    </form>
</div>

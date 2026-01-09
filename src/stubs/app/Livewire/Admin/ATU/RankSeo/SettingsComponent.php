<?php

namespace App\Livewire\Admin\ATU\RankSeo;

use Livewire\Component;
use Vormia\ATURankSEO\Models\RankSeoSettings;

class SettingsComponent extends Component
{
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

    public function render()
    {
        return view('livewire.admin.atu.rank-seo.settings');
    }
}

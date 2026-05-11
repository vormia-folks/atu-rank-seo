<?php

namespace Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts;
use Vormia\ATURankSEO\Models\RankSeoSettings;

class Settings extends Component
{
    use WithRankSeoToasts;

    public bool $isEnabled = true;

    #[Validate('nullable|string|max:255')]
    public ?string $globalTitle = null;

    #[Validate('nullable|string')]
    public ?string $globalDescription = null;

    #[Validate('nullable|string')]
    public ?string $globalKeywords = null;

    /** @var array<string, string> */
    public array $dynamicVariables = [];

    public string $newVariableKey = '';

    public string $newVariableValue = '';

    public function mount(): void
    {
        $settings = RankSeoSettings::getInstance();
        $this->isEnabled = (bool) $settings->is_enabled;
        $this->globalTitle = $settings->global_title;
        $this->globalDescription = $settings->global_description;
        $this->globalKeywords = $settings->global_keywords;
        $this->dynamicVariables = $settings->dynamic_variables ?? [];
    }

    public function save(): void
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
            $this->notifyError(__('Failed to save settings: ').$e->getMessage());
        }
    }

    public function addVariable(): void
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

    public function removeVariable(string $key): void
    {
        if (isset($this->dynamicVariables[$key])) {
            unset($this->dynamicVariables[$key]);
            $this->notifySuccess(__('Variable removed successfully.'));
        }
    }

    public function cancel(): void
    {
        $this->notifyInfo(__('Settings update cancelled.'));
    }

    public function render()
    {
        return view('aturankseo::livewire.admin.atu.rank-seo.settings');
    }
}

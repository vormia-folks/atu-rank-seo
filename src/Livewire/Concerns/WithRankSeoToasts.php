<?php

namespace Vormia\ATURankSEO\Livewire\Concerns;

trait WithRankSeoToasts
{
    public ?string $rankSeoToastHtml = null;

    protected function notifySuccess(string $message): void
    {
        $this->rankSeoToastHtml = $this->rankSeoToastMarkup('success', $message);
    }

    protected function notifyError(string $message): void
    {
        $this->rankSeoToastHtml = $this->rankSeoToastMarkup('error', $message);
    }

    protected function notifyInfo(string $message): void
    {
        $this->rankSeoToastHtml = $this->rankSeoToastMarkup('info', $message);
    }

    public function renderNotification(): string
    {
        return $this->rankSeoToastHtml ?? '';
    }

    private function rankSeoToastMarkup(string $type, string $message): string
    {
        $classes = match ($type) {
            'success' => 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200',
            'error' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200',
            default => 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200',
        };

        $safe = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="mb-4 rounded-md border p-4 '.$classes.'" role="alert">'.$safe.'</div>';
    }
}

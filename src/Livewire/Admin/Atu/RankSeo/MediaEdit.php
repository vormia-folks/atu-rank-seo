<?php

namespace Vormia\ATURankSEO\Livewire\Admin\Atu\RankSeo;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Vormia\ATURankSEO\Livewire\Concerns\WithRankSeoToasts;
use Vormia\ATURankSEO\Models\RankSeoMedia;

class MediaEdit extends Component
{
    use WithRankSeoToasts;

    public int|string|null $mediaId = null;

    #[Validate('nullable|string|max:255')]
    public ?string $title = null;

    #[Validate('nullable|string|max:255')]
    public ?string $altText = null;

    #[Validate('nullable|string')]
    public ?string $caption = null;

    public bool $isActive = true;

    public function mount(int|string $id): void
    {
        $this->mediaId = $id;
        $mediaSeo = RankSeoMedia::findOrFail($id);

        $this->title = $mediaSeo->title;
        $this->altText = $mediaSeo->alt_text;
        $this->caption = $mediaSeo->caption;
        $this->isActive = (bool) $mediaSeo->is_active;
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
            $this->notifyError(__('Failed to update media SEO entry: ').$e->getMessage());
        }
    }

    public function cancel(): void
    {
        $this->notifyInfo(__('Update cancelled.'));
    }

    #[Computed]
    public function mediaSeo(): RankSeoMedia
    {
        return RankSeoMedia::findOrFail($this->mediaId);
    }

    public function render()
    {
        return view('aturankseo::livewire.admin.atu.rank-seo.media-edit');
    }
}

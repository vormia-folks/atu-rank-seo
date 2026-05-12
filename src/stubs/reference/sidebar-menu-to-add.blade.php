{{-- >>> ATU Rank SEO Sidebar START --}}
@if (auth()->user()?->isAdminOrSuperAdmin())
	<hr />

	{{-- SEO Entries --}}
	<flux:navlist.item icon="magnifying-glass" :href="route('admin.atu.rank-seo.index')"
		:current="request()->routeIs('admin.atu.rank-seo.index') || request()->routeIs('admin.atu.rank-seo.edit')" wire:navigate>
		{{ __('SEO Entries') }}
	</flux:navlist.item>

	{{-- Media SEO --}}
	<flux:navlist.item icon="photo" :href="route('admin.atu.rank-seo.media.index')"
		:current="request()->routeIs('admin.atu.rank-seo.media.*')" wire:navigate>
		{{ __('Media SEO') }}
	</flux:navlist.item>

	{{-- SEO Settings --}}
	<flux:navlist.item icon="cog-6-tooth" :href="route('admin.atu.rank-seo.settings')"
		:current="request()->routeIs('admin.atu.rank-seo.settings')" wire:navigate>
		{{ __('SEO Settings') }}
	</flux:navlist.item>
@endif
{{-- >>> ATU Rank SEO Sidebar END --}}

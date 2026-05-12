{{--
    REFERENCE ONLY — not copied or loaded by the installer.
    Paste into a Flux-based admin sidebar (e.g. flux:navlist) when you want
    Rank SEO links next to your other admin items.

    Route names match the package defaults (admin.atu.rank-seo.*).
    Adjust auth() checks to match your app (below uses isAdminOrSuperAdmin()).

    For non-Flux layouts, see sidebar-menu-to-add.blade.php in this folder.
--}}

@if (auth()->user()?->isAdminOrSuperAdmin())
    <hr />

    <flux:navlist.item icon="magnifying-glass" :href="route('admin.atu.rank-seo.index')"
        :current="request()->routeIs('admin.atu.rank-seo.index') || request()->routeIs('admin.atu.rank-seo.edit')" wire:navigate>
        {{ __('SEO Entries') }}
    </flux:navlist.item>

    <flux:navlist.item icon="photo" :href="route('admin.atu.rank-seo.media.index')"
        :current="request()->routeIs('admin.atu.rank-seo.media.*')" wire:navigate>
        {{ __('Media SEO') }}
    </flux:navlist.item>

    <flux:navlist.item icon="cog-6-tooth" :href="route('admin.atu.rank-seo.settings')"
        :current="request()->routeIs('admin.atu.rank-seo.settings')" wire:navigate>
        {{ __('SEO Settings') }}
    </flux:navlist.item>
@endif

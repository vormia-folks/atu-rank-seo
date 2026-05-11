{{--
    REFERENCE ONLY — copy into your admin sidebar / navigation as needed.

    Route names match the package defaults (admin.atu.rank-seo.*).
    Ensure Flux or your layout includes Livewire styles/scripts.
--}}

{{-- Single link example --}}
<li>
    <a href="{{ route('admin.atu.rank-seo.index') }}" class="nav-link">
        <i class="fas fa-search"></i>
        <span>SEO Management</span>
    </a>
</li>

{{-- Submenu example --}}
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="fas fa-search"></i>
        <span>SEO</span>
    </a>
    <ul class="nav-submenu">
        <li>
            <a href="{{ route('admin.atu.rank-seo.index') }}">SEO Entries</a>
        </li>
        <li>
            <a href="{{ route('admin.atu.rank-seo.settings') }}">Global Settings</a>
        </li>
        <li>
            <a href="{{ route('admin.atu.rank-seo.media.index') }}">Media SEO</a>
        </li>
    </ul>
</li>

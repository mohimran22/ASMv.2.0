{{--
    Adapter menu AHA (format Tablar: text, icon, url, role, can, submenu)
    ke struktur sidebar gaya Ruang Kembali (.nav-item.has-dropdown, .submenu, .submenu-arrow).
    Simpan di: resources/views/partials/aha_menu_item.blade.php
--}}
@php
    $authUser = auth()->user();

    $canSee = function ($item) use ($authUser) {
        if (!$authUser) return false;
        if (isset($item['can']) && !$authUser->can($item['can'])) return false;
        if (isset($item['role']) && !$authUser->hasRole($item['role'])) return false;
        return true;
    };

    $resolveUrl = function ($item) {
        $raw = $item['url'] ?? '#';
        if ($raw === '#' || $raw === '' || $raw === null) return 'javascript:void(0)';
        if (!empty($item['href'])) return $item['href'];
        return url($raw);
    };

    $currentPath = trim(request()->path(), '/');

    $childIsActive = function ($child) use ($currentPath) {
        $path = trim(parse_url($child['url'] ?? '#', PHP_URL_PATH) ?? '', '/');
        if ($path === '' || $path === '#') return false;
        return $currentPath === $path
            || (bool) preg_match('#^' . preg_quote($path, '#') . '/(create|\d+(/edit)?)$#', $currentPath);
    };
@endphp

@foreach($menus as $menu)
    @php
        if (!is_array($menu) || !$canSee($menu)) continue;

        $children = array_values(array_filter($menu['submenu'] ?? [], fn ($c) => is_array($c) && $canSee($c)));
        $hasChildren = count($children) > 0;

        $isActive = false;
        if ($hasChildren) {
            foreach ($children as $c) {
                if ($childIsActive($c)) { $isActive = true; break; }
            }
        } else {
            $path = trim(parse_url($menu['url'] ?? '#', PHP_URL_PATH) ?? '', '/');
            if ($path !== '' && $path !== '#') {
                $isActive = $currentPath === $path || str_starts_with($currentPath, $path . '/');
            }
        }

        $submenuId = 'submenu-' . md5(($menu['text'] ?? '') . $loop->index);
    @endphp

    <li class="nav-item {{ $hasChildren ? 'has-dropdown' : '' }}" data-title="{{ $menu['text'] }}">
        <a class="nav-link {{ $isActive ? 'active' : '' }} {{ $hasChildren && $isActive ? 'open' : '' }}"
           data-title="{{ $menu['text'] }}"
           @if($hasChildren) data-submenu="{{ $submenuId }}" @endif
           href="{{ $hasChildren ? 'javascript:void(0)' : $resolveUrl($menu) }}">
            <span class="nav-link-icon">
                <i class="{{ $menu['icon'] ?? 'ti ti-circle' }}"></i>
            </span>
            <span class="nav-link-title">{{ $menu['text'] }}</span>
            @if($hasChildren)
                <span class="submenu-arrow"></span>
            @endif
        </a>

        @if($hasChildren)
            <div class="submenu {{ $isActive ? 'show' : '' }}" id="{{ $submenuId }}">
                <ul class="nav nav-sm flex-column ms-4">
                    @foreach($children as $child)
                        <li class="nav-item">
                            <a href="{{ $resolveUrl($child) }}"
                               class="nav-link {{ $childIsActive($child) ? 'active' : '' }}"
                               data-title="{{ $child['text'] }}">
                                <i class="{{ $child['icon'] ?? 'ti ti-point' }} me-2"></i>
                                {{ $child['text'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </li>
@endforeach
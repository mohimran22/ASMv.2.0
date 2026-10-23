<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Membangun menu sidebar dari tabel `menus`, dalam format yang dipahami
 * adapter sidebar AHA (text, icon, url, href, submenu).
 * Penyaringan hak akses dilakukan DI SINI, jadi adapter tidak perlu menyaring lagi.
 */
class SidebarMenu
{
    public const VERSION_KEY = 'sidebar_menus_version';

    public static function forUser($user): array
    {
        if (!$user) {
            return [];
        }

        $version = Cache::get(self::VERSION_KEY, 1);
        $key     = "sidebar_menus.v{$version}.user.{$user->id}";

        // TTL 10 menit: menjaga perubahan role user tetap ikut terbaca.
        return Cache::remember($key, now()->addMinutes(10), fn () => self::build($user));
    }

    /** Dipanggil otomatis saat menu disimpan atau dihapus (lihat model Menu). */
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 1) + 1);
    }

    private static function build($user): array
    {
        $isSuperAdmin = $user->hasRole('Super-Admin');

        $roots = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('order')])
            ->get();

        $result = [];

        foreach ($roots as $root) {
            if (!self::visible($root, $user, $isSuperAdmin)) {
                continue;
            }

            $children = [];
            foreach ($root->children as $child) {
                if (self::visible($child, $user, $isSuperAdmin)) {
                    $children[] = self::toItem($child);
                }
            }

            $item = self::toItem($root);

            // Induk yang semua anaknya tersembunyi dan tidak punya link sendiri: jangan ditampilkan.
            if ($root->children->isNotEmpty() && empty($children) && $item['href'] === null) {
                continue;
            }

            if (!empty($children)) {
                $item['submenu'] = $children;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Aturan akses:
     *  - Super-Admin melihat semua menu.
     *  - Kolom `permission_name` terisi -> user harus punya salah satu permission itu
     *    (beberapa permission dipisah "|", sama seperti Ruang Kembali).
     *  - Kolom `permission_name` kosong -> semua user yang login boleh melihat.
     */
    private static function visible(Menu $menu, $user, bool $isSuperAdmin): bool
    {
        if ($isSuperAdmin) {
            return true;
        }

        $perms = self::split($menu->permission_name);
        if ($perms) {
            foreach ($perms as $perm) {
                if ($user->can($perm)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    private static function toItem(Menu $menu): array
    {
        $url  = trim((string) $menu->url);
        $href = null;
        $path = '#';

        if ($url !== '' && $url !== '#') {
            try {
                if ($menu->type === 'route') {
                    if (Route::has($url)) {
                        $href = route($url);
                        $path = route($url, [], false);
                    }
                } else {
                    $href = url($url);
                    $path = $url;
                }
            } catch (\Throwable $e) {
                // route butuh parameter / tidak valid: tampilkan tanpa link
                $href = null;
                $path = '#';
            }
        }

        return [
            'text' => $menu->text,
            'icon' => $menu->icon ?: null,
            'url'  => $path,   // dipakai adapter untuk mendeteksi menu aktif
            'href' => $href,   // dipakai adapter sebagai link
        ];
    }

    private static function split(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode('|', (string) $value))));
    }
}
<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
// Pakai model milik aplikasi (bukan bawaan Spatie): id tabel roles/permissions bertipe UUID
// dan hanya model aplikasi yang mengisinya.
use App\Models\Permission;
use App\Models\Role;

/**
 * Memindahkan menu dari config/tablar.php ke tabel `menus`, dengan hak akses berbasis permission.
 *
 * Di config, akses menu diatur lewat 'role'. Di tabel, akses diatur lewat `permission_name`.
 * Supaya hak akses yang berlaku sekarang tidak berubah, untuk setiap menu yang dibatasi role
 * seeder ini:
 *   1. membuat permission "lihat menu <nama menu>",
 *   2. memberikannya ke role-role yang tercantum di config,
 *   3. mengisi `permission_name` menu tersebut dengan permission itu.
 * Menu yang di config memakai 'can' langsung memakai permission tersebut.
 * Menu tanpa 'role' dan 'can' tidak dibatasi (semua user yang login).
 *
 * Setelah itu, akses per menu bisa diubah dari halaman Manajemen Menu / Manajemen Role.
 *
 * Aman dijalankan ulang: dilewati kalau tabel menus sudah berisi data.
 * Jalankan: php artisan db:seed --class=SidebarMenuSeeder
 */
class SidebarMenuSeeder extends Seeder
{
    private const GUARD = 'web';

    /** Isi kolom `modules` untuk permission menu yang dibuat seeder (pengelompokan di halaman Permission/Role). */
    private const MODULE = 'Menu Sidebar';

    public function run(): void
    {
        if (Menu::count() > 0) {
            $this->command?->warn('Tabel menus sudah berisi data, seeder dilewati.');
            return;
        }

        $items = config('tablar.menu', []);

        // Menu untuk halaman pengelola menu (hanya Super-Admin)
        $items[] = [
            'text' => 'Manajemen Menu',
            'icon' => 'ti ti-menu-2',
            'url'  => '/menus',
            'role' => 'Super-Admin',
        ];

        // Satu transaksi: kalau ada yang gagal, tidak ada menu/permission yang tersisa setengah jadi.
        DB::transaction(fn () => $this->seed($items));

        $this->command?->info('Menu dari config/tablar.php sudah dipindahkan ke tabel menus.');
    }

    private function seed(array $items): void
    {
        $order = 1;

        foreach ($items as $item) {
            if (!is_array($item) || empty($item['text'])) {
                continue;
            }

            $parent = Menu::create($this->payload($item, null, $order++));

            $childOrder = 1;
            foreach ($item['submenu'] ?? [] as $child) {
                if (!is_array($child) || empty($child['text'])) {
                    continue;
                }
                Menu::create($this->payload($child, $parent->id, $childOrder++));
            }
        }
    }

    private function payload(array $item, ?int $parentId, int $order): array
    {
        $url = $item['url'] ?? null;

        return [
            'parent_id'       => $parentId,
            'text'            => $item['text'],
            'icon'            => $item['icon'] ?? null,
            'url'             => ($url === '#' || $url === '') ? null : $url,
            'type'            => 'url',
            'order'           => $order,
            'is_active'       => true,
            'permission_name' => $this->permissionFor($item),
        ];
    }

    private function permissionFor(array $item): ?string
    {
        // Permission yang sudah ada di AHA (mis. akun-akuntansi.lihat): pakai apa adanya.
        if (!empty($item['can'])) {
            return $item['can'];
        }

        $roles = array_values(array_filter(Arr::wrap($item['role'] ?? [])));
        if (!$roles) {
            return null; // tidak dibatasi
        }

        $name = 'lihat menu ' . Str::lower($item['text']);

        // 'id' diisi eksplisit seperti di PermissionController, jadi tidak bergantung pada trait HasUuid.
        $permission = Permission::firstOrCreate(
            ['name' => $name, 'guard_name' => self::GUARD],
            ['id' => (string) Str::uuid(), 'modules' => self::MODULE]
        );

        // Permission yang sudah ada tapi `modules`-nya kosong ikut dilengkapi.
        if (blank($permission->modules)) {
            $permission->update(['modules' => self::MODULE]);
        }

        $found = Role::whereIn('name', $roles)->where('guard_name', self::GUARD)->get();
        foreach ($found as $role) {
            $role->givePermissionTo($permission);
        }

        $missing = array_diff($roles, $found->pluck('name')->all());
        if ($missing) {
            $this->command?->warn("Role tidak ditemukan untuk menu '{$item['text']}': " . implode(', ', $missing));
        }

        return $name;
    }
}
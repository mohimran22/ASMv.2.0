<?php

namespace App\Models;

use App\Services\SidebarMenu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kalau project AHA sudah punya App\Models\Menu, JANGAN ditimpa:
 * cukup pastikan 'roles' ada di $fillable, ada relasi children(), dan hook booted() di bawah.
 */
class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'parent_id', 'text', 'icon', 'url', 'type',
        'order', 'is_active', 'permission_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    protected static function booted(): void
    {
        // Setiap menu diubah/dihapus, cache sidebar semua user dibuang.
        static::saved(fn () => SidebarMenu::flush());
        static::deleted(fn () => SidebarMenu::flush());
    }
}
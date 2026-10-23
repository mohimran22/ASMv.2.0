<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('menus', 'roles')) {
            Schema::table('menus', function (Blueprint $table) {
                // Daftar role dipisah "|", contoh: Super-Admin|Pemilik Lisensi|Direktur. Kosong = tidak dibatasi role.
                $table->string('roles')->nullable()->after('permission_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menus', 'roles')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->dropColumn('roles');
            });
        }
    }
};
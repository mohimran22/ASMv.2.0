<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('permissions', 'modules')) {
            Schema::table('permissions', function (Blueprint $table) {
                // Pengelompokan permission (dipakai PermissionController). Boleh kosong.
                $table->string('modules')->nullable()->after('guard_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('permissions', 'modules')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('modules');
            });
        }
    }
};
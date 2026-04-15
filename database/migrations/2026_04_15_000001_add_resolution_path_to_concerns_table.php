<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('concerns', 'resolution_path')) {
                $table->string('resolution_path')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'resolution_path')) {
                $table->dropColumn('resolution_path');
            }
        });
    }
};

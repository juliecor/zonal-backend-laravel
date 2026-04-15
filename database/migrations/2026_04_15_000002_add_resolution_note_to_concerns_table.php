<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('concerns', 'resolution_note')) {
                $table->string('resolution_note', 500)->nullable()->after('resolution_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'resolution_note')) {
                $table->dropColumn('resolution_note');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (!Schema::hasColumn('concerns', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('concerns', function (Blueprint $table) {
            if (Schema::hasColumn('concerns', 'attachment_path')) {
                $table->dropColumn('attachment_path');
            }
        });
    }
};

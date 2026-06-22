<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Durable cache of the city/barangay/classification dropdown lists per domain.
        // Lets the first (slow) SpreadSimple fetch happen ONCE globally instead of on
        // every cold serverless instance — subsequent loads come straight from MySQL.
        Schema::create('facet_cache', function (Blueprint $table) {
            $table->id();
            // e.g. "cities|pampanga.zonalvalue.com" or "barangays|cebu.zonalvalue.com|cebu city"
            $table->string('cache_key', 191)->unique();
            $table->longText('payload'); // JSON array of strings (the dropdown list)
            $table->timestamp('refreshed_at')->nullable(); // for stale-while-revalidate
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facet_cache');
    }
};

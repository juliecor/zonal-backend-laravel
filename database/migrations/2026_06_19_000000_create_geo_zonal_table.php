<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_zonal', function (Blueprint $table) {
            $table->id();
            // Normalized "street|barangay|city|province" key (unique cache key).
            $table->string('address_key', 191)->unique();

            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->string('label', 512)->nullable();

            // Optional zonal info registered to this coordinate.
            $table->decimal('value_per_sqm', 15, 2)->nullable();
            $table->string('classification_code', 32)->nullable();
            $table->string('province', 191)->nullable();
            $table->string('city_municipality', 191)->nullable();
            $table->string('barangay', 191)->nullable();
            $table->string('street_location', 255)->nullable();

            $table->string('source', 32)->nullable(); // google | osm
            $table->timestamp('geocoded_at')->nullable(); // for the 30-day refresh rule
            $table->timestamps();

            // Fast nearest-point lookups ("scan a coordinate → zonal value").
            $table->index(['lat', 'lon'], 'idx_geo_latlon');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_zonal');
    }
};

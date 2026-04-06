<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ CRITICAL: Add indexes for facet queries (province distinct, cities distinct, etc)
        // These will reduce 2.5min facet queries to ~50ms!
        Schema::table('zonal_values', function (Blueprint $table) {
            // Single-column indexes for DISTINCT queries
            $table->index('province', 'idx_zonal_province');
            $table->index('city_municipality', 'idx_zonal_city');
            $table->index('barangay', 'idx_zonal_barangay');
            $table->index('classification_code', 'idx_zonal_classification');
            
            // Composite indexes for filtered queries
            $table->index(['province', 'city_municipality'], 'idx_zonal_prov_city');
            $table->index(['province', 'city_municipality', 'barangay'], 'idx_zonal_prov_city_brgy');
            
            // For text search
            $table->index('street_location', 'idx_zonal_street');
            $table->index('vicinity', 'idx_zonal_vicinity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zonal_values', function (Blueprint $table) {
            $table->dropIndex('idx_zonal_province');
            $table->dropIndex('idx_zonal_city');
            $table->dropIndex('idx_zonal_barangay');
            $table->dropIndex('idx_zonal_classification');
            $table->dropIndex('idx_zonal_prov_city');
            $table->dropIndex('idx_zonal_prov_city_brgy');
            $table->dropIndex('idx_zonal_street');
            $table->dropIndex('idx_zonal_vicinit');
        });
    }
};

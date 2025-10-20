<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update NULL values to 0 in services table
        DB::table('services')
            ->whereNull('total_views')
            ->update(['total_views' => 0]);

        // Update NULL values to 0 in post_job_requests table
        DB::table('post_job_requests')
            ->whereNull('total_views')
            ->update(['total_views' => 0]);

        // Make sure the column has a default value
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('total_views')->default(0)->change();
        });

        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('total_views')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - keeping data as is
    }
};

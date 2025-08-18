<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('post_job_requests', 'total_budget')) {
                $table->double('total_budget')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            if (Schema::hasColumn('post_job_requests', 'total_budget')) {
                $table->dropColumn('total_budget');
            }
        });
    }
};
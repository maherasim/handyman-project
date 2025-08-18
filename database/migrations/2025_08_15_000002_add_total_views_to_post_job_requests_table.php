<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('total_views')->default(0)->after('job_price');
        });
    }

    public function down(): void
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->dropColumn('total_views');
        });
    }
};
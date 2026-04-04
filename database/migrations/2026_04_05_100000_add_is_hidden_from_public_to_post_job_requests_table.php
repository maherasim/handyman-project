<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('post_job_requests', 'is_hidden_from_public')) {
                $table->boolean('is_hidden_from_public')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_job_requests', function (Blueprint $table) {
            $table->dropColumn('is_hidden_from_public');
        });
    }
};

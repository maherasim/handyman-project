<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_job_bids', function (Blueprint $table) {
            if (!Schema::hasColumn('post_job_bids', 'hold_reason')) {
                $table->text('hold_reason')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_job_bids', function (Blueprint $table) {
            if (Schema::hasColumn('post_job_bids', 'hold_reason')) {
                $table->dropColumn('hold_reason');
            }
        });
    }
};
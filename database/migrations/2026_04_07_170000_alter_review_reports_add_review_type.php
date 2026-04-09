<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('review_reports')) {
            return;
        }

        Schema::table('review_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('review_reports', 'review_type')) {
                $table->string('review_type', 40)->default('booking_rating')->after('reporter_id');
            }
        });

        // For existing DBs where initial migration used FK to booking_ratings.
        try {
            DB::statement('ALTER TABLE review_reports DROP FOREIGN KEY review_reports_review_id_foreign');
        } catch (\Throwable $e) {
            // ignore if key name differs or already dropped
        }

        try {
            DB::statement('ALTER TABLE review_reports MODIFY review_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            // ignore if platform doesn't support this SQL
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('review_reports')) {
            return;
        }

        Schema::table('review_reports', function (Blueprint $table) {
            if (Schema::hasColumn('review_reports', 'review_type')) {
                $table->dropColumn('review_type');
            }
        });
    }
};

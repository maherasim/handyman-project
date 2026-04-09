<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * review_id is polymorphic (booking_ratings, customer_ratings, post_job_bid_ratings, …).
     * A single FK to booking_ratings breaks post-job and other report types.
     */
    public function up(): void
    {
        if (! Schema::hasTable('review_reports')) {
            return;
        }

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME AS name FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            ['review_reports', 'FOREIGN KEY']
        );

        foreach ($constraints as $row) {
            $name = $row->name ?? null;
            if (! is_string($name) || $name === '') {
                continue;
            }
            // Drop only FKs that involve review_id (not reporter_id, etc.)
            $usesReviewId = DB::selectOne(
                'SELECT 1 AS ok FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND CONSTRAINT_NAME = ?
                   AND COLUMN_NAME = ?
                 LIMIT 1',
                ['review_reports', $name, 'review_id']
            );
            if ($usesReviewId) {
                try {
                    DB::statement('ALTER TABLE `review_reports` DROP FOREIGN KEY `'.$name.'`');
                } catch (\Throwable $e) {
                    // ignore if already dropped or name mismatch
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: re-adding FK would break polymorphic review_id again.
    }
};

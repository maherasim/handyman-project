<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_conversations', 'booking_id')) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('post_job_bid_id');
                $table->index(['booking_id']);
            }
        });

        // Make post_job_bid_id nullable to allow booking-only conversations
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('post_job_bid_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('chat_conversations', 'booking_id')) {
                $table->dropIndex(['booking_id']);
                $table->dropColumn('booking_id');
            }
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('post_job_bid_id')->nullable(false)->change();
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('review_type', 40)->default('booking_rating');
            $table->unsignedBigInteger('review_id');
            $table->foreignId('review_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 120);
            $table->text('details')->nullable();
            $table->string('status', 40)->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['review_type', 'review_id', 'status']);
            $table->index(['reporter_id', 'review_type', 'review_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reports');
    }
};

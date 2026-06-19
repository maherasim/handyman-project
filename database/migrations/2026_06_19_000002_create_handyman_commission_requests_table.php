<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handyman_commission_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handyman_id');
            $table->unsignedBigInteger('provider_id');
            $table->decimal('current_commission', 5, 2);
            $table->decimal('requested_commission', 5, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('helpdesk_id')->nullable();
            $table->boolean('provider_agreed')->default(false);
            $table->boolean('handyman_agreed')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('handyman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('helpdesk_id')->references('id')->on('help_desk')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handyman_commission_requests');
    }
};

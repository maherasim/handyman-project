<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_job_extra_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_job_bid_id');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->foreign('post_job_bid_id')
                ->references('id')->on('post_job_bids')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_job_extra_charges');
    }
};


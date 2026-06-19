<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_job_extra_charges', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('post_job_extra_charges', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
        });
    }
};

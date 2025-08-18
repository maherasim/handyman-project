<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('post_job_requests', function (Blueprint $table) {
			if (!Schema::hasColumn('post_job_requests', 'state_id')) {
				$table->unsignedBigInteger('state_id')->nullable()->after('country_id');
			}
			if (!Schema::hasColumn('post_job_requests', 'city_id')) {
				$table->unsignedBigInteger('city_id')->nullable()->after('state_id');
			}
			if (!Schema::hasColumn('post_job_requests', 'category_id')) {
				$table->unsignedBigInteger('category_id')->nullable()->after('city_id');
			}
			if (!Schema::hasColumn('post_job_requests', 'subcategory_id')) {
				$table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
			}
			if (!Schema::hasColumn('post_job_requests', 'start_date')) {
				$table->date('start_date')->nullable()->after('date');
			}
			if (!Schema::hasColumn('post_job_requests', 'end_date')) {
				$table->date('end_date')->nullable()->after('start_date');
			}
			if (!Schema::hasColumn('post_job_requests', 'total_hours')) {
				$table->unsignedInteger('total_hours')->nullable()->after('end_date');
			}
			if (!Schema::hasColumn('post_job_requests', 'total_days')) {
				$table->unsignedInteger('total_days')->nullable()->after('total_hours');
			}
			if (!Schema::hasColumn('post_job_requests', 'requirement')) {
				$table->text('requirement')->nullable()->after('description');
			}
			if (!Schema::hasColumn('post_job_requests', 'price_type')) {
				$table->string('price_type')->nullable()->after('job_price');
			}
			if (!Schema::hasColumn('post_job_requests', 'job_schedule')) {
				$table->string('job_schedule')->nullable()->after('price_type');
			}
			if (!Schema::hasColumn('post_job_requests', 'remote_work_level')) {
				$table->string('remote_work_level')->nullable()->after('job_schedule');
			}
			if (!Schema::hasColumn('post_job_requests', 'career_level')) {
				$table->string('career_level')->nullable()->after('remote_work_level');
			}
			if (!Schema::hasColumn('post_job_requests', 'travel_required')) {
				$table->boolean('travel_required')->default(false)->after('career_level');
			}
			if (!Schema::hasColumn('post_job_requests', 'education_level')) {
				$table->string('education_level')->nullable()->after('travel_required');
			}
			if (!Schema::hasColumn('post_job_requests', 'duties')) {
				$table->text('duties')->nullable()->after('education_level');
			}
			if (!Schema::hasColumn('post_job_requests', 'benefits')) {
				$table->text('benefits')->nullable()->after('duties');
			}
		});
	}

	public function down(): void
	{
		Schema::table('post_job_requests', function (Blueprint $table) {
			foreach (['benefits','duties','education_level','travel_required','career_level','remote_work_level','job_schedule','price_type','requirement','total_days','total_hours','end_date','start_date','subcategory_id','category_id','city_id','state_id'] as $column) {
				if (Schema::hasColumn('post_job_requests', $column)) {
					$table->dropColumn($column);
				}
			}
		});
	}
};
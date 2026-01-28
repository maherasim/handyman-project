<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Set in-app notification_message for provider_send_bid so job creator sees a short message when a provider places a bid.
     */
    public function up(): void
    {
        $template = DB::table('notification_templates')->where('type', 'provider_send_bid')->first();
        if ($template) {
            DB::table('notification_template_content_mapping')
                ->where('template_id', $template->id)
                ->update([
                    'notification_message' => 'You have received a new bid of [[ bid_amount ]] from [[ provider_name ]] on your job request #[[ job_id ]].',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $template = DB::table('notification_templates')->where('type', 'provider_send_bid')->first();
        if ($template) {
            DB::table('notification_template_content_mapping')
                ->where('template_id', $template->id)
                ->update(['notification_message' => '']);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDoneByProviderBookingStatus extends Migration
{
    public function up()
    {
        $exists = DB::table('booking_statuses')->where('value', 'done_by_provider')->exists();
        if (!$exists) {
            DB::table('booking_statuses')->insert([
                'label'      => 'Done By Provider',
                'value'      => 'done_by_provider',
                'sequence'   => 10,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('booking_statuses')->where('value', 'done_by_provider')->delete();
    }
}

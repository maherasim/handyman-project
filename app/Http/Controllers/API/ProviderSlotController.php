<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProviderSlotMapping;
 

class ProviderSlotController extends Controller
{
public function getProviderSlot(Request $request)
{
    $provider_id = $request->provider_id ?? auth()->user()->id;

    // Set timezone
    date_default_timezone_set('Asia/Dubai');

    $startDate = \Carbon\Carbon::now();
    $endDate = \Carbon\Carbon::now()->addDays(30); // Next 30 days

    $calendarSlots = [];

    for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
        $dayCode = strtolower($date->format('D')); // mon, tue, etc.
        $formattedDate = $date->format('Y-m-d');

        $slots = ProviderSlotMapping::where('provider_id', $provider_id)
            ->where('days', $dayCode)
            ->orderBy('start_at', 'asc')
            ->pluck('start_at')
            ->toArray();

        $calendarSlots[] = [
            'date' => $formattedDate,
            'day' => $dayCode,
            'slots' => $slots,
        ];
    }

    return comman_custom_response($calendarSlots);
}


public function store(Request $request)
{
    $request->validate([
        'slots' => 'required|array',
        'slots.*.day' => 'required|string|in:sun,mon,tue,wed,thu,fri,sat',
        'slots.*.time' => 'required|array',
        'slots.*.time.*' => 'required|date_format:H:i',
    ]);

    $slotdata = $request->all();
    $provider_id = $request->provider_id ?? auth()->user()->id;

    // Delete old slots for this provider
    ProviderSlotMapping::where('provider_id', $provider_id)->delete();

    $isCreated = false;

    foreach ($slotdata['slots'] as $value) {
        if (!empty($value['time'])) {
            foreach ($value['time'] as $time) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $time);
                $end = $start->copy()->addMinutes(60); // or 30, your choice

                ProviderSlotMapping::create([
                    'provider_id' => $provider_id,
                    'days' => $value['day'],
                    'start_at' => $start->format('H:i'),
                    'end_at' => $end->format('H:i')
                ]);

                $isCreated = true;
            }
        }
    }

    $message = $isCreated
        ? __('messages.save_form', ['form' => __('messages.providerslot')])
        : __('messages.update_form', ['form' => __('messages.providerslot')]);

    return comman_message_response($message);
}

}

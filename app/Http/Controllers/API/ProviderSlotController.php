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
        'slots' => 'nullable|array',
        'slots.*.date' => 'required|date|date_format:Y-m-d',
        'slots.*.time' => 'nullable|array',
    ]);

    $provider_id = $request->provider_id ?? auth()->user()->id;

    // Delete existing slots for the specific dates being updated
    $datesToUpdate = [];
    foreach ($request->slots as $slot) {
        if (isset($slot['date']) && !empty($slot['date'])) {
            $datesToUpdate[] = $slot['date'];
        }
    }

    if (!empty($datesToUpdate)) {
        ProviderSlotMapping::where('provider_id', $provider_id)
            ->whereIn('date', $datesToUpdate)
            ->delete();
    }

    $isCreated = false;

    foreach ($request->slots as $slot) {
        $date = $slot['date'] ?? null;
        $times = $slot['time'] ?? [];

        if (!$date) {
            continue;
        }

        if (empty($times)) {
            continue;
        }

        foreach ($times as $time) {
            if ($time === '24:00:00') {
                continue;
            }

            try {
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $time);
                $end = $start->copy()->addMinutes(60);

                ProviderSlotMapping::create([
                    'provider_id' => $provider_id,
                    'date' => $date,
                    'start_at' => $start->format('H:i'),
                    'end_at' => $end->format('H:i'),
                ]);

                $isCreated = true;
            } catch (\Exception $e) {
                \Log::error('Error creating slot: ' . $e->getMessage());
                continue;
            }
        }
    }

    $message = $isCreated
        ? __('messages.save_form', ['form' => __('messages.providerslot')])
        : __('messages.update_form', ['form' => __('messages.providerslot')]);

    return comman_message_response($message);
}
}

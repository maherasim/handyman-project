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
        'slots.*.date' => 'required|date|date_format:Y-m-d',
        'slots.*.time' => 'required|array',
    ]);

    $provider_id = $request->provider_id ?? auth()->user()->id;

    // Log incoming request for debugging
    \Log::info('Time slot update request', [
        'provider_id' => $provider_id,
        'slots_count' => count($request->slots),
        'slots' => $request->slots
    ]);

    // Delete existing slots for the specific dates being updated
    $datesToUpdate = [];
    foreach ($request->slots as $slot) {
        if (isset($slot['date']) && !empty($slot['date'])) {
            $datesToUpdate[] = $slot['date'];
        }
    }

    if (!empty($datesToUpdate)) {
        \Log::info('Deleting slots for dates', ['dates' => $datesToUpdate]);
        ProviderSlotMapping::where('provider_id', $provider_id)
            ->whereIn('date', $datesToUpdate)
            ->delete();
    }

    $isCreated = false;

    foreach ($request->slots as $slot) {
        $date = $slot['date'] ?? null;
        $times = $slot['time'] ?? [];

        if (!$date) {
            \Log::warning('Slot skipped: no date provided', ['slot' => $slot]);
            continue;
        }

        if (empty($times)) {
            \Log::warning('Slot skipped: no times provided', ['date' => $date]);
            continue;
        }

        foreach ($times as $time) {
            if ($time === '24:00:00') {
                continue;
            }

            try {
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $time);
                $end = $start->copy()->addMinutes(60);

                $slotData = [
                    'provider_id' => $provider_id,
                    'date' => $date, // Make sure this is set
                    'start_at' => $start->format('H:i'),
                    'end_at' => $end->format('H:i'),
                ];

                \Log::info('Creating slot', ['slotData' => $slotData]);

                ProviderSlotMapping::create($slotData);

                $isCreated = true;
            } catch (\Exception $e) {
                \Log::error('Error creating slot', [
                    'time' => $time,
                    'date' => $date,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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

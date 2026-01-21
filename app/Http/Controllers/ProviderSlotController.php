<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderSlotMapping;
class ProviderSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
                // Handle both H:i and H:i:s formats
                $timeFormat = strlen($time) === 5 ? 'H:i' : 'H:i:s';
                $start = \Carbon\Carbon::createFromFormat($timeFormat, $time);
                $end = $start->copy()->addMinutes(60);

                ProviderSlotMapping::create([
                    'provider_id' => $provider_id,
                    'date' => $date,
                    'start_at' => $start->format('H:i'),
                    'end_at' => $end->format('H:i'),
                    'days' => null, // Explicitly set to null since we're using date-based slots
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


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

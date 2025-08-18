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

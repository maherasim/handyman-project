@extends('landing-page.layouts.default')

@section('content')

<div class="section-padding">
    <div class="container">
    <booking-wizard  
    :service='@json($service)' 
    :coupons='@json($coupons)' 
    :taxes='@json($taxes)' 
    :user_id="{{ $user_id }}" 
    :availableserviceslot='@json($availableserviceslot)' 
    :serviceaddon='@json(isset($serviceaddon) ? $serviceaddon : null)'  
    :googlemapkey="'{{ $googlemapkey }}'" 
    :wallet_amount="{{ $wallet_amount }}"
></booking-wizard>

    </div>
</div>

@endsection


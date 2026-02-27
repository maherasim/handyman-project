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
    :googlemapkey="'AIzaSyAWSFmXUbdkkWCXfMB0VgG0-DEVFK_GT4o'"
    :wallet_amount="{{ $wallet_amount }}"
    :payment_type='@json($payment_type)'
    :booking_id="{{ $booking_id }}"
    :total_booking_amount="{{ $total_booking_amount }}"
    :total_advance_paid_amount="{{ $total_advance_paid_amount }}"
></booking-wizard>

    </div>
</div>

@endsection


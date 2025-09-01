@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Bid Details</h2>

    <div class="card p-3">
        <h4>{{ $bid->title }}</h4>
        <p><strong>Price:</strong> {{ $bid->price }}</p>
        <p><strong>Duration:</strong> {{ $bid->duration }}</p>
        <p><strong>Status:</strong> {{ ucfirst($bid->status) }}</p>
        <p><strong>Advance %:</strong> {{ $bid->advance_percent }}</p>
        <p><strong>Remaining %:</strong> {{ $bid->remaining_percent }}</p>

        <hr>
        <h5>Request Information</h5>
        <p><strong>Request Title:</strong> {{ $bid->request->title ?? '-' }}</p>
        <p><strong>Customer:</strong> {{ $bid->request->customer->name ?? '-' }}</p>
    </div>
</div>
@endsection

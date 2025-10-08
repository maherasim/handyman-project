

@extends('landing-page.layouts.default')

@section('content')
<style>
    /* Ensure SweetAlert2 and Bootstrap modals sit above any card ribbons/badges */
    .swal2-container { z-index: 200000 !important; }
    .swal2-container .swal2-popup { z-index: 200001 !important; }
    .modal-backdrop { z-index: 200010 !important; }
    .modal { z-index: 200020 !important; }
</style>
<div class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <!-- Tab panes -->
                <service-page link="{{ route('service.data', ['id' => $id, 'type' => $type, 'latitude' => $latitude, 'longitude' => $longitude]) }}"></service-page>


            </div>
        </div>
    </div>
</div>

@endsection

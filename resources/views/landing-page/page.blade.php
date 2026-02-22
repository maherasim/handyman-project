@extends('landing-page.layouts.default')

@section('content')
<div class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="mb-4 text-capitalize fw-bold">{{ $page->title }}</h1>
                <div class="page-content prose">
                    {!! $page->content ?? '' !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

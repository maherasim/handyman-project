<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="fw-bold">{{ $pageTitle ?? __('messages.edit') }}</h5>
                        <a href="{{ route('page.index') }}" class="btn btn-sm btn-outline-primary">{{ __('messages.back') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    {{ html()->form('POST', route('page.update'))->attribute('data-toggle', 'validator')->open() }}
                    {{ html()->hidden('id', $page->id) }}
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ html()->label(__('messages.title'), 'title')->class('form-control-label') }}
                            {{ html()->text('title', $page->title)->class('form-control')->placeholder(__('messages.title')) }}
                        </div>
                        <div class="form-group col-md-6">
                            {{ html()->label(__('messages.slug'), 'slug')->class('form-control-label') }}
                            {{ html()->text('slug', $page->slug)->class('form-control')->attribute('readonly')->attribute('disabled') }}
                            <small class="text-muted">{{ __('Slug cannot be changed after creation.') }}</small>
                        </div>
                        <div class="form-group col-md-12 mt-3">
                            {{ html()->label(__('messages.content'), 'content')->class('form-control-label') }}
                            {{ html()->textarea('content', $page->content)->class('form-control tinymce-page-content')->placeholder(__('messages.content')) }}
                        </div>
                        <div class="form-group col-md-6 mt-3">
                            <div class="form-check form-switch">
                                {{ html()->checkbox('is_active', 1, $page->is_active)->class('form-check-input')->id('is_active') }}
                                {{ html()->label(__('messages.active'), 'is_active')->class('form-check-label') }}
                            </div>
                        </div>
                        <div class="form-group col-md-6 mt-3">
                            {{ html()->label(__('messages.sort_order'), 'sort_order')->class('form-control-label') }}
                            {{ html()->number('sort_order', $page->sort_order)->class('form-control')->attribute('min', 0) }}
                        </div>
                    </div>
                    {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-end mt-3') }}
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@section('bottom_script')
    <script>
        (function($) {
            $(document).ready(function(){
                tinymceEditor('.tinymce-page-content',' ',function (ed) {}, 450);
            });
        })(jQuery);
    </script>
@endsection
</x-master-layout>

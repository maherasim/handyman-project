<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                        </div>
                    </div>
                </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    {{ html()->form('POST', route('term-condition-save'))->attribute('data-toggle', 'validator')->open() }}
                    {{ html()->hidden('id',$setting_data->id ?? null) }}
                    <div class="row">
                        <div class="form-group col-md-12">
                            {{ html()->label(__('messages.terms_condition'), 'terms_condition')->class('form-control-label') }}
                            {{ html()->textarea('value', $setting_data->value)->class('form-control tinymce-terms_condition')->placeholder(__('messages.terms_condition')) }}
                    </div>
                    </div>   
                    {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-end') }}
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
                tinymceEditor('.tinymce-terms_condition',' ',function (ed) {

                }, 450)
            
            });

        })(jQuery);
    </script>
@endsection
</x-master-layout>
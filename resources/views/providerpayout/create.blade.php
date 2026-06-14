<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="fw-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                        <a href="{{ route('earning') }}" class=" float-end btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert" style="animation: slideDown 0.3s ease-out; margin-bottom: 20px; display: block !important;">
                            <i class="fa fa-check-circle me-2"></i><strong>{{ session('success') }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <script>
                            // Show success message immediately - run before DOMContentLoaded
                            (function() {
                                const successMessage = '{{ addslashes(session('success')) }}';
                                // Show Snackbar immediately if available
                                if (typeof Snackbar !== 'undefined') {
                                    Snackbar.show({
                                        text: successMessage,
                                        pos: 'bottom-center',
                                        duration: 5000,
                                        actionText: 'OK',
                                        actionTextColor: '#fff'
                                    });
                                } else {
                                    // If Snackbar not loaded yet, wait a bit and try again
                                    setTimeout(function() {
                                        if (typeof Snackbar !== 'undefined') {
                                            Snackbar.show({
                                                text: successMessage,
                                                pos: 'bottom-center',
                                                duration: 5000,
                                                actionText: 'OK',
                                                actionTextColor: '#fff'
                                            });
                                        }
                                    }, 100);
                                }
                            })();
                        </script>
                    @endif
                    {{ html()->form('POST', route('providerpayout.store'))->attributes(['enctype' => 'multipart/form-data', 'data-toggle' => 'validator', 'id' => 'providerpayout'])->open() }}       
                    {{ html()->hidden('provider_id',$payoutdata->provider_id ?? null) }}
                    {{ html()->hidden('redirect_type', $redirect_type) }}
                    {{ html()->hidden('payment_method', 'bank')->attributes(['id' => 'payment_method_hidden']) }}
                
                    <div class="row">
                        <div class="form-group col-md-4" id="payment_method_id">
                            {{ html()->label(trans('messages.method') . ' <span class="text-danger">*</span>', 'method')->class('form-control-label') }}
                            {{ html()->select('payment_method', ['bank' => __('messages.bank'),'cash' => __('messages.cash'),'wallet' => __('messages.wallet'),], old('payment_method', 'bank'))->attributes(['id' => 'method', 'class' => 'form-control select2js', 'required']) }}
                        </div>
                
                        <div class="form-group col-md-4" id="select_bank">
                            {{ html()->label(__('messages.select_bank', ['select' => __('messages.select_bank')]) . ' <span class="text-danger">*</span>', 'bank')->class('form-control-label') }}
                            <a href="{{ route('bank.create', ['user_id' => $payoutdata->provider_id]) }}" class="me-1 btn-link btn-link-hover"><i class="fa fa-plus-circle"></i> {{ trans('messages.add_form_title', ['form' => trans('messages.bank')]) }}</a>
                            <br />
                            {{ html()->select('bank', [])
                                ->attributes(['class' => 'select2js form-group col-md-12 bank', 'required', 'data-placeholder' => __('messages.select_bank', ['select' => __('messages.')])]) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ html()->label(__('messages.amount') . ' <span class="text-danger">*</span>', 'amount')->class('form-control-label') }}
                            
                            {{-- Editable amount input field --}}
                            {{ html()->text('amount', old('amount') ?? $payoutdata->amount)->attributes([
                                'class' => 'form-control',
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '5',
                                'required' => true,
                                'placeholder' => __('messages.amount'),
                                'id' => 'amount_input',
                            ]) }}
                            @if(isset($walletBalance) && (auth()->user()->user_type == 'handyman' || auth()->user()->user_type == 'provider'))
                                <small class="form-text text-muted mt-1">
                                    <strong>{{ __('messages.available_balance') }}:</strong> 
                                    <span class="text-success">{{ getPriceFormat($walletBalance ?? 0) }}</span>
                                </small>
                                <small class="form-text text-danger mt-1 d-none" id="min_amount_error">
                                    <i class="fa fa-exclamation-circle"></i> {{ __('messages.minimum_withdrawal_amount') ?? 'Minimum withdrawal amount is 5' }}
                                </small>
                            @endif
                        </div>
                              {{-- Payment Gateway section hidden --}}
                              {{-- <div class="form-group col-md-12"  id='payment_gateway'>
                                  <label class="form-control-label">{{__('messages.payment_gateway',['gateway'=>__('messages.payment_gateway')])}}</label><br/>
                            <div class="form-check-inline">
                                      <label class="form-check-label">
                                          <input type="radio" class="form-check-input is_test" value="razorpayx" name="payment_gateway" data-type="razorpayx" {{ old('payment_gateway') == 'razorpayx' || !old('payment_gateway') ? 'checked' : '' }}>{{__('messages.razorx')}}
                                      </label>
                            </div>
                            <div class="form-check-inline">
                                      <label class="form-check-label">
                                          <input type="radio" class="form-check-input is_test" value="stripe" name="payment_gateway" data-type="stripe" {{ old('payment_gateway') == 'stripe' ? 'checked' : '' }} >{{__('messages.stripe')}}
                                      </label>
                            </div>
                
                            <small class="help-block with-errors text-danger"></small>
                        </div> --}}

                          <!-- 
                        <div class="form-group col-md-12" id="payment_gateway">
                            {{ html()->label(trans('messages.payment_gateway') . ' <span class="text-danger">*</span>','payment_gateway')->class('form-control-label') }}
                            {{ html()->select('payment_gateway', ['razorpayx' => __('messages.razorx'),'stripe' => __('messages.stripe')], old('payment_gateway'))->id('payment_gateway')->class('form-control select2js')->required() }}
                            </div> -->
                          
                            {{-- Description field hidden for providers --}}
                            {{-- <div class="form-group col-md-12">
                                {{ html()->label(__('messages.description'), 'description')->class('form-control-label') }}
                                {{ html()->textarea('description', null)->attributes(['class' => 'form-control textarea', 'rows' => 3, 'placeholder' => __('messages.description')]) }}
                            </div> --}}
                            
                        </div>
                    {{ html()->submit(trans('messages.save'))->attributes(['class' => 'btn btn-md btn-primary float-end', 'id' => 'saveButton']) }}
                    {{ html()->form()->close() }}
                </div>                
            </div>
        </div>
    </div>
</div>
@section('bottom_script')
<script type="text/javascript">
            (function($) {
                "use strict";
                $(document).ready(function(){
                    var payment_method =  "{{ isset($provider_payouts->payment_method) ? $provider_payouts->payment_method : 'bank' }}";
                       
                    var provider_id = $('input[name="provider_id"]').val();
       
                    // Set default to bank and show bank fields
                    $('#method').val('bank').trigger('change');
                    // Disable the method dropdown when bank is selected
                    $('#method').prop('disabled', true);
                    // Hide payment gateway section
                    $('#payment_gateway').addClass("d-none");
                    bankdetails(provider_id , bank);

                    $(document).on('change' , '#method' , function (){
                        var payment_method = $(this).val();

                        if(payment_method=='bank'){

                          $("#select_bank").removeClass("d-none");
                          // Payment gateway is hidden, so don't show it
                          // $("#payment_gateway").removeClass("d-none");
                          // Disable method dropdown when bank is selected
                          $(this).prop('disabled', true);
                            bankdetails(provider_id,bank);

                        }else{

                           $('#select_bank').addClass("d-none");

                           $('#payment_gateway').addClass("d-none");
                           // Enable method dropdown for other options (though bank should always be selected)
                           $(this).prop('disabled', false);
                           
                        }   
                       
                    })
                   
                })
                function bankdetails(provider_id , bank ="" ){
                    var bank_route = "{{ route('ajax-list', [ 'type' => 'bank','provider_id' =>'']) }}"+provider_id;
                    bank_route = bank_route.replace('amp;','');

                    $.ajax({
                        url: bank_route,
                        success: function(result){
                          
                            $('#bank').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.bank_name',['select' => trans('messages.bank_name')]) }}",
                                data: result.results
                            });
                            if(bank != null){
                                $("#bank_details").val(bank).trigger('change');
                            }
                        }
                    });
                }
        
               
            })(jQuery);


            window.onload = function() {
    if (window.history && window.history.pushState) {
        window.history.pushState('', null, '');
        window.onpopstate = function() {
            window.history.pushState('', null, '');
        };
    }
};

    $(document).ready(function() {
        // Minimum withdrawal amount validation
        const minAmount = 5;
        const amountInput = $('#amount_input');
        const minAmountError = $('#min_amount_error');
        
        // Real-time validation on input
        amountInput.on('input blur', function() {
            const amount = parseFloat($(this).val()) || 0;
            if (amount > 0 && amount < minAmount) {
                $(this).addClass('is-invalid');
                minAmountError.removeClass('d-none');
            } else {
                $(this).removeClass('is-invalid');
                minAmountError.addClass('d-none');
            }
        });
        
        $('#providerpayout').on('submit', function(e) {
            // Validate minimum amount before submission
            const amount = parseFloat(amountInput.val()) || 0;
            if (amount < minAmount) {
                e.preventDefault();
                amountInput.addClass('is-invalid');
                minAmountError.removeClass('d-none');
                amountInput.focus();
                
                // Show alert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('messages.validation_error') }}',
                        text: '{{ __('messages.minimum_withdrawal_amount') ?? 'Minimum withdrawal amount is 5' }}',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('{{ __('messages.minimum_withdrawal_amount') ?? 'Minimum withdrawal amount is 5' }}');
                }
                return false;
            }
            
            // Ensure payment_method is always set to 'bank' before submission
            $('#payment_method_hidden').val('bank');
            // Also set it in the disabled dropdown (though it won't submit, hidden field will)
            $('#method').val('bank');
            
            $('#saveButton').attr('disabled', true); 
        });
        
        // Scroll to success alert if it exists
        @if(session('success'))
            const alert = document.getElementById('success-alert');
            if (alert) {
                setTimeout(function() {
                    alert.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 200);
            }
        @endif
    });
</script>
@endsection
</x-master-layout>

<style>
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .alert-success {
        border-left: 4px solid #198754;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.2);
        background-color: #d1e7dd;
    }
    #success-alert {
        position: relative;
        z-index: 1000;
    }
</style>

<x-master-layout>
    <head>
        <title>Laravel - Stripe Payment Gateway Integration Example</title>
        <!-- Updated Bootstrap 4 CDN -->
         <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>

    <div class="container">
        <h1>Proceed payment with stripe</h1>
<br>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card credit-card-box">
                    <div class="card-header">
                        <h3 class="card-title">Payment Details</h3>
                    </div>
                    <div class="card-body">
                        @if (Session::has('success'))
                            <div class="alert alert-success text-center">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
                                <p>{{ Session::get('success') }}</p>
                            </div>
                        @endif

                        <form role="form" action="{{ route('stripe.post') }}" method="post" class="require-validation" data-cc-on-file="false" data-stripe-publishable-key="{{ env('STRIPE_KEY') }}" id="payment-form">
                            @csrf
                            <input type="hidden" name="plan_id" id="plan_id" value="{{ request()->get('plan_id') }}">
                            <input type="hidden" name="plan_type" id="plan_type" value="{{ request()->get('plan_type') }}">
                            <input type="hidden" name="plan_amount" id="plan_amount" value="{{ request()->get('plan_amount') }}">

                            <div class='form-row row'>
                                <div class='col-xs-6 form-group required'>
                                    <label class='control-label'>Name</label>
                                    <input class='form-control' size='20' type='text'>
                                </div>
                                <div class='col-xs-6 form-group card required'>
                                    <label class='control-form'>Card Number</label>
                                    <input autocomplete='off' class='form-control card-number' size='20' type='text'>
                                </div>
                            </div>

                          

                            <div class='form-row row'>
                                <div class='col-xs-12 col-md-4 form-group cvc required'>
                                    <label class='control-label'>CVC</label>
                                    <input autocomplete='off' class='form-control card-cvc' placeholder='ex. 311' size='4' type='text'>
                                </div>
                                <div class='col-xs-12 col-md-4 form-group expiration required'>
                                    <label class='control-label'>Expiration Month</label>
                                    <input class='form-control card-expiry-month' placeholder='MM' size='2' type='text'>
                                </div>
                                <div class='col-xs-12 col-md-4 form-group expiration required'>
                                    <label class='control-label'>Expiration Year</label>
                                    <input class='form-control card-expiry-year' placeholder='YYYY' size='4' type='text'>
                                </div>
                            </div>

                            <div class='form-row row'>
                                <div class='col-md-12 error form-group d-none'>
                                    <div class='alert alert-danger'>Please correct the errors and try again.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12">
                                    <button class="btn btn-primary btn-lg btn-block" type="submit">
                                        Pay Now ($<span id="payment-amount-display">{{ request()->get('plan_amount') }}</span>)
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            // Update the displayed payment amount based on the plan details
            document.addEventListener('DOMContentLoaded', function() {
                const amountDisplay = document.getElementById('payment-amount-display');
                const planAmount = document.getElementById('plan_amount').value;

                if (planAmount && amountDisplay) {
                    amountDisplay.textContent = parseFloat(planAmount).toFixed(2);
                }
            });
        </script>

    </div>

    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

    <script type="text/javascript">
        $(function() {
            var $form = $(".require-validation");

            $('form.require-validation').bind('submit', function(e) {
                var $form = $(".require-validation"),
                    inputSelector = ['input[type=email]', 'input[type=password]', 'input[type=text]', 'input[type=file]', 'textarea'].join(', '),
                    $inputs = $form.find('.required').find(inputSelector),
                    $errorMessage = $form.find('div.error'),
                    valid = true;

                $errorMessage.addClass('d-none');
                $('.has-error').removeClass('has-error');
                
                $inputs.each(function(i, el) {
                    var $input = $(el);
                    if ($input.val() === '') {
                        $input.parent().addClass('has-error');
                        $errorMessage.removeClass('d-none');
                        e.preventDefault();
                    }
                });

                if (!$form.data('cc-on-file')) {
                    e.preventDefault();
                    Stripe.setPublishableKey($form.data('stripe-publishable-key'));
                    Stripe.createToken({
                        number: $('.card-number').val(),
                        cvc: $('.card-cvc').val(),
                        exp_month: $('.card-expiry-month').val(),
                        exp_year: $('.card-expiry-year').val()
                    }, stripeResponseHandler);
                }
            });

            function stripeResponseHandler(status, response) {
                if (response.error) {
                    $('.error').removeClass('d-none').find('.alert').text(response.error.message);
                } else {
                    var token = response['id'];
                    $form.find('input[type=text]').empty();
                    $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
                    $form.get(0).submit();
                }
            }
        });
    </script>
</x-master-layout>

<x-master-layout>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="fw-bold">{{ $pageTitle }}</h5>
                        <a href="{{ route('wallet.show', auth()->id()) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">

                    {{-- Current balance --}}
                    <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
                        <span>{{ __('messages.wallet_balance') }}</span>
                        <strong class="fs-5">{{ getPriceFormat($walletBalance) }}</strong>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Payment method tabs --}}
                    <ul class="nav nav-pills mb-4 gap-2" id="topupTabs" role="tablist">
                        @php $hasStripe = $paymentGateways->contains('type', 'stripe'); @endphp

                        @if($hasStripe)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="stripe-tab" data-bs-toggle="pill" data-bs-target="#stripe-pane" type="button" role="tab">
                                <i class="fab fa-stripe-s me-1"></i> {{ __('messages.stripe') }}
                            </button>
                        </li>
                        @endif
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$hasStripe ? 'active' : '' }}" id="bank-tab" data-bs-toggle="pill" data-bs-target="#bank-pane" type="button" role="tab">
                                <i class="fas fa-university me-1"></i> {{ __('messages.bank_transfer') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="topupTabContent">

                        {{-- Stripe --}}
                        @if($hasStripe)
                        <div class="tab-pane fade show active" id="stripe-pane" role="tabpanel">
                            <form action="{{ route('wallet.topup.stripe') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="stripe_amount" class="form-label fw-semibold">
                                        {{ __('messages.topup_amount') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" name="amount" id="stripe_amount" class="form-control form-control-lg"
                                            placeholder="{{ __('messages.topup_amount_placeholder') }}"
                                            min="1" step="0.01" required>
                                    </div>
                                    <small class="text-muted">{{ __('messages.minimum') }}: €1.00</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-credit-card me-2"></i> {{ __('messages.proceed') }} (Stripe)
                                </button>
                            </form>
                        </div>
                        @endif

                        {{-- Bank Transfer --}}
                        <div class="tab-pane fade {{ !$hasStripe ? 'show active' : '' }}" id="bank-pane" role="tabpanel">
                            @php $bankConfig = json_decode(optional(\App\Models\Setting::where('key','bank_transfer_config')->first())->value ?? '{}', true); @endphp
                            <div class="p-3 bg-light rounded border mb-3">
                                <h6 class="text-primary mb-3"><i class="fas fa-university me-2"></i>{{ __('messages.bank_information') }}</h6>
                                <ul class="list-unstyled mb-0">
                                    @if(!empty($bankConfig['recipient']))
                                    <li class="mb-1"><strong>{{ __('messages.pjr_bank_recipient') }}</strong> {{ $bankConfig['recipient'] }}</li>
                                    @endif
                                    @if(!empty($bankConfig['iban']))
                                    <li class="mb-1"><strong>{{ __('messages.pjr_bank_iban') }}</strong> {{ $bankConfig['iban'] }}</li>
                                    @endif
                                    @if(!empty($bankConfig['bic']))
                                    <li class="mb-1"><strong>{{ __('messages.pjr_bank_bic') }}</strong> {{ $bankConfig['bic'] }}</li>
                                    @endif
                                    @if(!empty($bankConfig['bank_name']))
                                    <li class="mb-1"><strong>{{ __('messages.pjr_bank_name_address') }}</strong> {{ $bankConfig['bank_name'] }}, {{ $bankConfig['bank_address'] ?? '' }}</li>
                                    @endif
                                </ul>
                            </div>
                            <p class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('messages.send_proof_of_payment') }}
                                @if(!empty($bankConfig['email']))
                                    <a href="mailto:{{ $bankConfig['email'] }}">{{ $bankConfig['email'] }}</a>
                                @endif
                            </p>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-1"></i>
                                {{ __('messages.bank_transfer_email_proof') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-master-layout>

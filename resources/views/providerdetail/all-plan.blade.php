@php
    $plans = \App\Models\Plans::where('status', 'active')->get();
    $planLang = [
        'upgradeTo' => __('messages.provider_sub_btn_upgrade_to'),
        'switchTo' => __('messages.provider_sub_btn_switch_to'),
        'noOtherPlans' => __('messages.provider_sub_no_other_plans'),
        'freeModalTitle' => __('messages.provider_sub_free_modal_title'),
        'freeModalLine' => __('messages.provider_sub_free_modal_line'),
        'freeModalNoPayment' => __('messages.provider_sub_free_modal_no_payment'),
        'btnYesUpgrade' => __('messages.provider_sub_btn_yes_upgrade'),
        'cancel' => __('messages.cancel'),
        'processing' => __('messages.provider_sub_processing'),
        'upgradingPlan' => __('messages.provider_sub_upgrading_plan'),
        'successTitle' => __('messages.provider_sub_success'),
        'successUpgraded' => __('messages.provider_sub_success_upgraded'),
        'errorTitle' => __('messages.provider_sub_error'),
        'errorUpgrade' => __('messages.provider_sub_error_upgrade'),
        'unknownError' => __('messages.provider_sub_unknown_error'),
        'errorTryAgain' => __('messages.provider_sub_error_try_again'),
        'walletSwalTitle' => __('messages.provider_sub_wallet_swal_title'),
        'walletSwalLine' => __('messages.provider_sub_wallet_swal_line'),
        'walletSwalNote' => __('messages.provider_sub_wallet_swal_note'),
        'payWithWallet' => __('messages.provider_sub_pay_with_wallet'),
        'processingPayment' => __('messages.provider_sub_processing_payment'),
        'walletPleaseWait' => __('messages.provider_sub_wallet_please_wait'),
        'paymentSuccessTitle' => __('messages.provider_sub_payment_success'),
        'subscriptionUpgraded' => __('messages.provider_sub_subscription_upgraded'),
        'great' => __('messages.provider_sub_great'),
        'paymentFailed' => __('messages.provider_sub_payment_failed'),
        'paymentNotProcessed' => __('messages.provider_sub_payment_not_processed'),
        'paymentErrorGeneric' => __('messages.provider_sub_payment_error_generic'),
        'stripeSwalTitle' => __('messages.provider_sub_stripe_swal_title'),
        'stripeSwalLine' => __('messages.provider_sub_stripe_swal_line'),
        'stripeRedirectNote' => __('messages.provider_sub_stripe_redirect_note'),
        'continueStripe' => __('messages.provider_sub_continue_stripe'),
        'redirecting' => __('messages.provider_sub_redirecting'),
        'redirectStripe' => __('messages.provider_sub_redirect_stripe'),
        'stripeSessionError' => __('messages.provider_sub_stripe_session_error'),
        'stripeProcessError' => __('messages.provider_sub_stripe_process_error'),
        'paypalSwalTitle' => __('messages.provider_sub_paypal_swal_title'),
        'paypalSwalLine' => __('messages.provider_sub_paypal_swal_line'),
        'paypalRedirectNote' => __('messages.provider_sub_paypal_redirect_note'),
        'continuePaypal' => __('messages.provider_sub_continue_paypal'),
        'redirectPaypal' => __('messages.provider_sub_redirect_paypal'),
        'paypalSessionError' => __('messages.provider_sub_paypal_session_error'),
        'paypalProcessError' => __('messages.provider_sub_paypal_process_error'),
        'bankSwalTitle' => __('messages.provider_sub_bank_swal_title'),
        'bankSwalIntro' => __('messages.provider_sub_bank_swal_intro'),
        'bankSubscriptionDetails' => __('messages.provider_sub_bank_subscription_details'),
        'bankLabelPlan' => __('messages.provider_sub_bank_label_plan'),
        'bankLabelAmount' => __('messages.provider_sub_bank_label_amount'),
        'bankForTransfers' => __('messages.provider_sub_bank_for_transfers'),
        'bankRecipient' => __('messages.pjr_bank_recipient'),
        'bankIban' => __('messages.pjr_bank_iban'),
        'bankBic' => __('messages.pjr_bank_bic'),
        'bankNameAddress' => __('messages.pjr_bank_name_address'),
        'bankBicSender' => __('messages.pjr_bank_bic_sender'),
        'bankInstructionsHeading' => __('messages.provider_sub_bank_instructions_heading'),
        'bankStep1' => __('messages.provider_sub_bank_step1'),
        'bankStep2' => __('messages.provider_sub_bank_step2'),
        'bankStep3' => __('messages.provider_sub_bank_step3'),
        'bankStep4' => __('messages.provider_sub_bank_step4'),
        'bankNote' => __('messages.provider_sub_bank_note'),
        'bankConfirmBtn' => __('messages.provider_sub_bank_confirm_btn'),
        'bankInitiatedTitle' => __('messages.provider_sub_bank_initiated_title'),
        'bankInitiatedBody' => __('messages.provider_sub_bank_initiated_body'),
        'bankNextSteps' => __('messages.provider_sub_bank_next_steps'),
        'bankNext1' => __('messages.provider_sub_bank_next_1'),
        'bankNext2' => __('messages.provider_sub_bank_next_2'),
        'bankNext3' => __('messages.provider_sub_bank_next_3'),
        'bankEmailFollowup' => __('messages.provider_sub_bank_email_followup'),
        'gotIt' => __('messages.provider_sub_got_it'),
        'bankRecordFailed' => __('messages.provider_sub_bank_record_failed'),
        'bankRecordError' => __('messages.provider_sub_bank_record_error'),
        'ok' => __('messages.provider_sub_ok'),
        'planWord' => __('messages.plan'),
        'bankInfoHeading' => __('messages.pjr_bank_info_heading'),
        'noteLabel' => __('messages.provider_sub_note_label'),
        'bankNote' => __('messages.provider_sub_bank_note'),
    ];
    $bankConfig = getBankTransferDisplayConfig();
@endphp

<div class="subscription-management-container">
    <!-- Header Section -->
    <div class="subscription-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 text-primary">
                    <i class="fas fa-crown me-2"></i>{{ __('messages.plan') }}
                </h4>
                <p class="text-muted mb-0">{{ __('messages.provider_sub_header_subtitle') }}</p>
            </div>
            <div class="subscription-search">
                <div class="input-group input-group-search">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control dt-search border-start-0" 
                           placeholder="{{ __('messages.provider_sub_search_ph') }}" aria-label="{{ __('messages.provider_sub_search_aria') }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Current Plan Status Card -->
    <div class="current-plan-card mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="plan-icon me-3">
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-dark">{{ __('messages.provider_sub_current_plan') }}</h6>
                                <p class="mb-0 text-muted">{{ __('messages.provider_sub_current_plan_desc') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-success fs-6">{{ __('messages.provider_sub_badge_active') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Table -->
    <div class="plans-table-container">
        <div class="table-responsive">
            <table class="table data-table mb-0 table-hover">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" class="border-0">
                            <i class="fas fa-tag me-1"></i>{{ __('messages.name') }}
                        </th>
                        <th scope="col" class="border-0">
                            <i class="fas fa-layer-group me-1"></i>{{ __('messages.type') }}
                        </th>
                        <th scope="col" class="border-0">
                            <i class="fas fa-euro-sign me-1"></i>{{ __('messages.amount') }}
                        </th>
                        <th scope="col" class="border-0">
                            <i class="fas fa-calendar-alt me-1"></i>{{ __('messages.start_at') }}
                        </th>
                        <th scope="col" class="border-0">
                            <i class="fas fa-calendar-check me-1"></i>{{ __('messages.end_at') }}
                        </th>
                        <th scope="col" class="border-0">
                            <i class="fas fa-info-circle me-1"></i>{{ __('messages.status') }}
                        </th>
                        <th scope="col" class="border-0 text-center">
                            <i class="fas fa-rocket me-1"></i>{{ __('messages.provider_sub_col_actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Enhanced Upgrade Confirmation Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1" role="dialog" aria-labelledby="upgradeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="upgrade-icon me-3">
                        <i class="fas fa-rocket fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="upgradeModalLabel">{{ __('messages.provider_sub_modal_upgrade_title') }}</h5>
                        <small class="opacity-75">{{ __('messages.provider_sub_modal_upgrade_subtitle') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Plan Summary Card -->
                <div class="plan-summary-card mb-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="plan-image-container me-3">
                                            <img id="plan_image" src="{{ asset('images/icon/freepng.png') }}" 
                                                 alt="{{ __('messages.provider_sub_plan_image_alt') }}" class="plan-image">
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark" id="plan_name">{{ __('messages.provider_sub_plan_placeholder_name') }}</h6>
                                            <p class="mb-0 text-muted">{{ __('messages.provider_sub_plan_placeholder_tagline') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="plan-price">
                                        <span class="price-amount text-primary fs-3 fw-bold" id="plan_amount">€0.00</span>
                                        <small class="text-muted d-block">{{ __('messages.provider_sub_per_month') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="plan-benefits mb-4">
                    <h6 class="text-dark mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>{{ __('messages.provider_sub_benefits_title') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('messages.provider_sub_benefit_1') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('messages.provider_sub_benefit_2') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('messages.provider_sub_benefit_3') }}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('messages.provider_sub_benefit_4') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('messages.provider_sub_benefit_5') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('messages.provider_sub_benefit_6') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Message -->
                <div class="confirmation-message">
                    <div class="alert alert-info border-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 text-info"></i>
                            <div>
                                <strong>{{ __('messages.provider_sub_confirm_ready') }}</strong><br>
                                <small>{{ __('messages.provider_sub_confirm_click') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <form id="upgradeForm" class="w-100">
                    <input type="hidden" id="plan_id" name="plan_id">
                    <input type="hidden" id="plan_type" name="plan_type">
                    <input type="hidden" id="plan_amount_value" name="plan_amount_value">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i>{{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i>{{ __('messages.provider_sub_btn_proceed_payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Payment Method Selection Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1" role="dialog" aria-labelledby="paymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-success text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="payment-icon me-3">
                        <i class="fas fa-credit-card fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="paymentMethodModalLabel">{{ __('messages.provider_sub_pay_choose_title') }}</h5>
                        <small class="opacity-75">{{ __('messages.provider_sub_pay_choose_subtitle') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Payment Summary -->
                <div class="payment-summary mb-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">{{ __('messages.provider_sub_payment_summary') }}</h6>
                            <div class="payment-amount">
                                <span class="amount text-primary fs-2 fw-bold" id="payment_amount">€0.00</span>
                                <small class="text-muted d-block">{{ __('messages.provider_sub_one_time_payment') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods Grid -->
                <div class="payment-methods-grid">
                    <h6 class="text-dark mb-3">
                        <i class="fas fa-wallet me-2"></i>{{ __('messages.provider_sub_pay_methods_heading') }}
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-method-card" id="payWithWallet">
                                <div class="card h-100 border-0 shadow-sm payment-card">
                                    <div class="card-body text-center p-4">
                                        <div class="payment-icon-large mb-3">
                                            <i class="fas fa-wallet text-primary fa-2x"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">{{ __('messages.provider_sub_wallet_title') }}</h6>
                                        <p class="card-text text-muted small mb-3">{{ __('messages.provider_sub_wallet_desc') }}</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-success">{{ __('messages.provider_sub_badge_instant') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" id="payWithStripe">
                                <div class="card h-100 border-0 shadow-sm payment-card">
                                    <div class="card-body text-center p-4">
                                        <div class="payment-icon-large mb-3">
                                            <i class="fab fa-cc-stripe text-primary fa-2x"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">{{ __('messages.provider_sub_card_title') }}</h6>
                                        <p class="card-text text-muted small mb-3">{{ __('messages.provider_sub_card_desc') }}</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-primary">{{ __('messages.provider_sub_badge_secure') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" id="payWithPaypal">
                                <div class="card h-100 border-0 shadow-sm payment-card">
                                    <div class="card-body text-center p-4">
                                        <div class="payment-icon-large mb-3">
                                            <i class="fab fa-paypal text-primary fa-2x"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">PayPal</h6>
                                        <p class="card-text text-muted small mb-3">{{ __('messages.provider_sub_paypal_desc') }}</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-info">{{ __('messages.provider_sub_badge_popular') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-method-card" id="payWithBank">
                                <div class="card h-100 border-0 shadow-sm payment-card">
                                    <div class="card-body text-center p-4">
                                        <div class="payment-icon-large mb-3">
                                            <i
                                             class="fas fa-university text-primary fa-2x"></i>
                      
                                            </div>
                                        <h6 class="card-title text-dark mb-2">{{ __('messages.provider_sub_bank_title') }}</h6>
                                        <p class="card-text text-muted small mb-3">{{ __('messages.provider_sub_bank_desc') }}</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-warning">{{ __('messages.provider_sub_badge_manual') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="security-notice mt-4">
                    <div class="alert alert-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success me-3"></i>
                            <div>
                                <strong>{{ __('messages.provider_sub_secure_pay_title') }}</strong><br>
                                <small class="text-muted">{{ __('messages.provider_sub_secure_pay_text') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.subscription-management-container {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    margin: 1rem 0;
}

.subscription-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin: -2rem -2rem 2rem -2rem;
}

.subscription-search .input-group-text {
    border-color: #e9ecef;
}

.subscription-search .form-control {
    border-color: #e9ecef;
}

.subscription-search .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.current-plan-card .card {
    border-radius: 12px;
    transition: transform 0.2s ease;
}

.current-plan-card .card:hover {
    transform: translateY(-2px);
}

.plan-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #ffc107, #ff8c00);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.plans-table-container .table {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.plans-table-container .table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    padding: 1rem;
}

.plans-table-container .table tbody tr {
    transition: background-color 0.2s ease;
}

.plans-table-container .table tbody tr:hover {
    background-color: #f8f9fa;
}

.upgrade-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.plan-summary-card .card {
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.plan-image-container {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #e9ecef;
    transition: all 0.3s ease;
}

.plan-image-container:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.plan-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.plan-benefits ul li {
    padding: 0.25rem 0;
}

.payment-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.payment-summary .card {
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Modal sizing: keep professional medium height with scroll */
.modal .modal-body{
    max-height: 65vh;
    overflow-y: auto;
}

/* SweetAlert bank transfer popup sizing */
.bank-transfer-popup{
    max-width: 640px !important;
}
.bank-transfer-popup .swal2-html-container{
    max-height: 65vh;
    overflow-y: auto;
    text-align: left;
}

.payment-methods-grid .payment-card {
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.payment-methods-grid .payment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border: 2px solid #667eea;
}

.payment-icon-large {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin: 0 auto;
}

.payment-badge .badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
    border-radius: 8px;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .subscription-management-container {
        padding: 1rem;
    }
    
    .subscription-header {
        margin: -1rem -1rem 1rem -1rem;
        padding: 1rem;
    }
    
    .modal-dialog { max-width: 95%; }
    
    .payment-methods-grid .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    var planLang = @json($planLang);
    var bankTransferConfig = @json($bankConfig);
    $(document).ready(function() {
        var table;
        var loadurl =
            '{{ route('provider_detail_pages') }}?tabpage=all-plan&type=tbl&providerid={{ request()->providerid }}';
        var plans = [];

        function formatCurrency(amount){
            var num = parseFloat(amount);
            if (isNaN(num)) { num = 0; }
            return '€' + num.toFixed(2);
        }

        // Fetch available plans
        $.ajax({
            url: '{{ route('get.plans') }}',
            type: 'GET',
            success: function(data) {
                plans = data;
                console.log("Available plans:", plans); // Log to ensure plans are loaded
                initializeDataTable(); // Initialize DataTable after fetching plans
            },
            error: function(error) {
                console.log("Error fetching plans:", error);
            }
        });

        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('.data-table')) {
                $('.data-table').DataTable().destroy();
            }

            table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                ajax: {
                    url: loadurl,
                    type: 'GET',
                    data: function(d) {
                        d.search = {
                            value: $('.dt-search').val()
                        };
                    }
                },
                columns: [{
                        data: 'plan_type',
                        name: 'title'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        render: function(data){
                            return formatCurrency(data);
                        }
                    },
                    {
                        data: 'start_at',
                        name: 'start_at'
                    },
                    {
                        data: 'end_at',
                        name: 'end_at'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            return data.charAt(0).toUpperCase() + data.slice(1);
                        }
                    },
                    {
                        data: 'plan_type',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let currentPlan = data;
                            let upgradeOptions = [];

                            // Debug logging
                            console.log('Current plan:', currentPlan);

                            // If current plan is Free Plan, show Silver and Gold (upgrades)
                            if (currentPlan.toLowerCase().includes('free')) {
                                upgradeOptions = plans.filter(plan => 
                                    plan.title.toLowerCase().includes('silver') || 
                                    plan.title.toLowerCase().includes('gold')
                                );
                                console.log('Free plan detected - showing Silver and Gold upgrades:', upgradeOptions);
                            } else if (currentPlan.toLowerCase().includes('silver')) {
                                // If Silver plan, show Free and Gold (downgrade to Free, upgrade to Gold)
                                upgradeOptions = plans.filter(plan => 
                                    plan.title.toLowerCase().includes('free') || 
                                    plan.title.toLowerCase().includes('gold')
                                );
                                console.log('Silver plan detected - showing Free and Gold options:', upgradeOptions);
                            } else if (currentPlan.toLowerCase().includes('gold')) {
                                // If Gold plan, show Free and Silver (downgrades)
                                upgradeOptions = plans.filter(plan => 
                                    plan.title.toLowerCase().includes('free') || 
                                    plan.title.toLowerCase().includes('silver')
                                );
                                console.log('Gold plan detected - showing Free and Silver downgrades:', upgradeOptions);
                            } else {
                                // For other plans, show all available plans except current
                                upgradeOptions = plans.filter(plan => plan.title !== currentPlan);
                                console.log('Other plan - showing all plans except current:', upgradeOptions);
                            }

                            let buttons = '';
                            if (upgradeOptions.length > 0) {
                                upgradeOptions.forEach(plan => {
                                    // Determine if it's an upgrade or downgrade based on amount
                                    let currentAmount = parseFloat($('#plan_amount').text()) || 0;
                                    let planAmount = parseFloat(plan.amount) || 0;
                                    let buttonText = planAmount > currentAmount ? planLang.upgradeTo : planLang.switchTo;
                                    
                                    buttons +=
                                        `<button class="btn btn-warning upgrade-btn" data-plan="${plan.title}" data-id="${row.id}" data-amount="${plan.amount}">${buttonText} ${plan.title}</button> `;
                                });
                            } else {
                                buttons = '<span>' + planLang.noOtherPlans + '</span>';
                            }
                            return buttons;
                        }
                    }
                ],
                language: {
                    processing: "{{ __('messages.processing') }}"
                }
            });
        }

        // Trigger search
        $('.dt-search').on('keyup', function() {
            table.draw();
        });

        // Handle upgrade button click with enhanced UX
        $(document).on('click', '.upgrade-btn', function() {
            var planType = $(this).data('plan');
            var planId = $(this).data('id');
            var planAmount = $(this).data('amount');
            var buttonText = $(this).text();

            // Debug logging
            console.log('Plan Type:', planType);
            console.log('Plan Amount:', planAmount);
            console.log('Plan ID:', planId);

            // Update modal content
            $('#plan_id').val(planId);
            $('#plan_type').val(planType);
            $('#plan_name').text(planType);
            var amountNumber = parseFloat(planAmount);
            var formattedAmount = formatCurrency(amountNumber);
            $('#plan_amount').text(formattedAmount);
            $('#payment_amount').text(formattedAmount);
            $('#plan_amount_value').val(amountNumber.toFixed(2));
            
            // Update plan image based on plan type
            var planImageSrc = '';
            var planImageAlt = planType + ' ' + planLang.planWord;
            
            if (planType.toLowerCase().includes('silver')) {
                planImageSrc = '{{ asset("images/icon/silverpng.png") }}';
            } else if (planType.toLowerCase().includes('gold')) {
                planImageSrc = '{{ asset("images/icon/goldpng.png") }}';
            } else if (planType.toLowerCase().includes('free') || planType.toLowerCase().includes('basic')) {
                planImageSrc = '{{ asset("images/icon/freepng.png") }}';
            } else {
                // Default fallback
                planImageSrc = '{{ asset("images/icon/freepng.png") }}';
            }
            
            // Update the plan image in the modal
            $('#plan_image').attr('src', planImageSrc).attr('alt', planImageAlt);

            // Check if it's a free plan (either by name or amount)
            if (planType.toLowerCase().includes('free') || planAmount == 0 || planAmount == '0') {
                // Show confirmation for free plan
                Swal.fire({
                    title: '🎉 ' + planLang.freeModalTitle,
                    html: `
                        <div class="text-center">
                            <div class="mb-3">
                                <img src="${planImageSrc}" alt="${planImageAlt}" style="width: 80px; height: 80px; object-fit: contain; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            </div>
                            <p class="mb-3">${planLang.freeModalLine.replace(/:plan/g, planType)}</p>
                            <p class="text-muted">${planLang.freeModalNoPayment}</p>
                        </div>
                    `,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: planLang.btnYesUpgrade,
                    cancelButtonText: planLang.cancel,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: planLang.processing,
                            text: planLang.upgradingPlan,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Automatically upgrade without payment
                        $.ajax({
                            url: '{{ route('upgrade.free.plan') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                plan_id: planId,
                                plan_type: planType
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        title: '✅ ' + planLang.successTitle,
                                        text: planLang.successUpgraded.replace(':plan', planType),
                                        icon: 'success',
                                        confirmButtonText: planLang.great,
                                        confirmButtonColor: '#28a745'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: '❌ ' + planLang.errorTitle,
                                        text: planLang.errorUpgrade.replace(':plan', planType).replace(':message', response.message || planLang.unknownError),
                                        icon: 'error',
                                        confirmButtonText: planLang.ok
                                    });
                                }
                            },
                            error: function(error) {
                                console.error('Free plan upgrade error:', error);
                                Swal.fire({
                                    title: '❌ ' + planLang.errorTitle,
                                    text: planLang.errorTryAgain.replace(':plan', planType),
                                    icon: 'error',
                                    confirmButtonText: planLang.ok
                                });
                            }
                        });
                    }
                });
            } else {
                // Show payment modal for paid plans
                $('#upgradeModal').modal('show');
            }
        });

        // Handle form submission
        $('#upgradeForm').on('submit', function(e) {
            e.preventDefault();

            $('#upgradeModal').modal('hide');
            $('#paymentMethodModal').modal('show');
        });

        // Handle Wallet payment selection with enhanced UX
        $('#payWithWallet').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount_value').val();
            var planAmountDisplay = formatCurrency(planAmount);

            // Show confirmation
            Swal.fire({
                title: '💳 ' + planLang.walletSwalTitle,
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-wallet text-primary fa-3x"></i>
                        </div>
                        <p class="mb-3">${planLang.walletSwalLine.replace(':amount', planAmountDisplay)}</p>
                        <p class="text-muted">${planLang.walletSwalNote}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: planLang.payWithWallet,
                cancelButtonText: planLang.cancel,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: planLang.processingPayment,
                        text: planLang.walletPleaseWait,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route('subscription.wallet.payment') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            plan_id: planId,
                            plan_type: planType,
                            plan_amount: planAmount
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: '✅ ' + planLang.paymentSuccessTitle,
                                    text: planLang.subscriptionUpgraded,
                                    icon: 'success',
                                    confirmButtonText: planLang.great,
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: '❌ ' + planLang.paymentFailed,
                                    text: response.message || planLang.paymentNotProcessed,
                                    icon: 'error',
                                    confirmButtonText: planLang.ok
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                title: '❌ ' + planLang.errorTitle,
                                text: planLang.paymentErrorGeneric,
                                icon: 'error',
                                confirmButtonText: planLang.ok
                            });
                        }
                    });
                }
            });
        });

        // Handle Stripe payment selection with enhanced UX
        $('#payWithStripe').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount_value').val();
            var planAmountDisplay = formatCurrency(planAmount);

            // Show confirmation
            Swal.fire({
                title: '💳 ' + planLang.stripeSwalTitle,
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fab fa-cc-stripe text-primary fa-3x"></i>
                        </div>
                        <p class="mb-3">${planLang.stripeSwalLine.replace(':amount', planAmountDisplay)}</p>
                        <p class="text-muted">${planLang.stripeRedirectNote}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: planLang.continueStripe,
                cancelButtonText: planLang.cancel,
                confirmButtonColor: '#635bff',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: planLang.redirecting,
                        text: planLang.redirectStripe,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route('subscription.stripe.create') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            plan_id: planId,
                            plan_type: planType,
                            plan_amount: planAmount
                        },
                        success: function(response) {
                            if (response.status && response.url) {
                                window.location.href = response.url;
                            } else {
                                Swal.fire({
                                    title: '❌ ' + planLang.errorTitle,
                                    text: response.message || planLang.stripeSessionError,
                                    icon: 'error',
                                    confirmButtonText: planLang.ok
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                title: '❌ ' + planLang.errorTitle,
                                text: planLang.stripeProcessError,
                                icon: 'error',
                                confirmButtonText: planLang.ok
                            });
                        }
                    });
                }
            });
        });

        // Handle PayPal payment selection with enhanced UX
        $('#payWithPaypal').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount_value').val();
            var planAmountDisplay = formatCurrency(planAmount);

            // Show confirmation
            Swal.fire({
                title: '💳 ' + planLang.paypalSwalTitle,
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fab fa-paypal text-primary fa-3x"></i>
                        </div>
                        <p class="mb-3">${planLang.paypalSwalLine.replace(':amount', planAmountDisplay)}</p>
                        <p class="text-muted">${planLang.paypalRedirectNote}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: planLang.continuePaypal,
                cancelButtonText: planLang.cancel,
                confirmButtonColor: '#0070ba',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: planLang.redirecting,
                        text: planLang.redirectPaypal,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route('subscription.paypal.create') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            plan_id: planId,
                            plan_type: planType,
                            plan_amount: planAmount
                        },
                        success: function(response) {
                            if (response.status && response.url) {
                                window.location.href = response.url;
                            } else {
                                Swal.fire({
                                    title: '❌ ' + planLang.errorTitle,
                                    text: response.message || planLang.paypalSessionError,
                                    icon: 'error',
                                    confirmButtonText: planLang.ok
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                title: '❌ ' + planLang.errorTitle,
                                text: planLang.paypalProcessError,
                                icon: 'error',
                                confirmButtonText: planLang.ok
                            });
                        }
                    });
                }
            });
        });

        // Handle Bank Transfer payment selection
        $('#payWithBank').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount_value').val();
            var planAmountDisplay = formatCurrency(planAmount);

            const bankInfoHtml = `
                <div class="bank-transfer-modal">
                    <div class="bank-header mb-4">
                        <div class="bank-icon">🏦</div>
                        <h4 class="mb-2">${planLang.bankSwalTitle}</h4>
                        <p class="text-muted">${planLang.bankSwalIntro}</p>
                    </div>
                    
                    <div class="subscription-info mb-4">
                        <h6 class="text-primary mb-2">📋 ${planLang.bankSubscriptionDetails}</h6>
                        <div class="row">
                            <div class="col-6">${planLang.bankLabelPlan} <strong>${planType}</strong></div>
                            <div class="col-6">${planLang.bankLabelAmount} <strong>${planAmountDisplay}</strong></div>
                        </div>
                    </div>

                    <div class="bank-details mb-4">
                        <h6 class="text-success mb-3">🏦 ${planLang.bankInfoHeading}</h6>
                        <div class="mb-2"><strong>${planLang.bankForTransfers}</strong></div>
                        <div class="bank-info-card">
                            <div class="bank-row">
                                <span class="bank-label">${planLang.bankRecipient}</span>
                                <span class="bank-value">${bankTransferConfig.recipient}</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">${planLang.bankIban}</span>
                                <span class="bank-value">${bankTransferConfig.iban}</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">${planLang.bankBic}</span>
                                <span class="bank-value">${bankTransferConfig.bic}</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">${planLang.bankNameAddress}</span>
                                <span class="bank-value">${bankTransferConfig.bank_name},<br>${bankTransferConfig.bank_address.replace(/\n/g,'<br>')}</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">${planLang.bankBicSender}</span>
                                <span class="bank-value">${bankTransferConfig.sender_bic}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="instructions mb-4">
                        <h6 class="text-warning mb-3">📝 ${planLang.bankInstructionsHeading}</h6>
                        <div class="instruction-steps">
                            <div class="step">
                                <span class="step-number">1</span>
                                <span class="step-text">${planLang.bankStep1.replace(':amount', planAmountDisplay)}</span>
                            </div>
                            <div class="step">
                                <span class="step-number">2</span>
                                <span class="step-text">${planLang.bankStep2.replace(':plan', planType)}</span>
                            </div>
                            <div class="step">
                                <span class="step-number">3</span>
                                <span class="step-text">${planLang.bankStep3} <a href="mailto:${bankTransferConfig.email}" class="email-link">${bankTransferConfig.email}</a></span>
                            </div>
                            <div class="step">
                                <span class="step-number">4</span>
                                <span class="step-text">${planLang.bankStep4}</span>
                            </div>
                        </div>
                    </div>

                    <div class="note-box">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <strong>${planLang.noteLabel}</strong> ${planLang.bankNote}
                        </small>
                    </div>
                </div>
                
                <style>
                    .bank-transfer-modal {
                        text-align: left;
                        max-width: 100%;
                    }
                    .bank-header {
                        text-align: center;
                        padding: 20px;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border-radius: 10px;
                        margin: -20px -20px 20px -20px;
                    }
                    .bank-icon {
                        font-size: 2rem;
                        margin-bottom: 10px;
                    }
                    .subscription-info {
                        background-color: #e3f2fd;
                        padding: 15px;
                        border-radius: 8px;
                        border-left: 4px solid #2196f3;
                    }
                    .bank-details {
                        background-color: #f8f9fa;
                        padding: 15px;
                        border-radius: 8px;
                        border-left: 4px solid #28a745;
                    }
                    .bank-info-card {
                        background-color: white;
                        padding: 15px;
                        border-radius: 6px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .bank-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 8px 0;
                        border-bottom: 1px solid #e9ecef;
                    }
                    .bank-row:last-child {
                        border-bottom: none;
                    }
                    .bank-label {
                        font-weight: 600;
                        color: #495057;
                    }
                    .bank-value {
                        color: #6c757d;
                        font-family: monospace;
                    }
                    .instructions {
                        background-color: #fff3cd;
                        padding: 15px;
                        border-radius: 8px;
                        border-left: 4px solid #ffc107;
                    }
                    .instruction-steps {
                        margin-top: 10px;
                    }
                    .step {
                        display: flex;
                        align-items: flex-start;
                        margin-bottom: 10px;
                    }
                    .step-number {
                        background-color: #ffc107;
                        color: #212529;
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 12px;
                        margin-right: 10px;
                        flex-shrink: 0;
                    }
                    .step-text {
                        flex: 1;
                        line-height: 1.4;
                    }
                    .email-link {
                        color: #007bff;
                        text-decoration: none;
                        font-weight: 600;
                    }
                    .email-link:hover {
                        text-decoration: underline;
                    }
                    .note-box {
                        background-color: #d1ecf1;
                        padding: 12px;
                        border-radius: 6px;
                        border-left: 4px solid #17a2b8;
                    }
                </style>
            `;

            Swal.fire({
                title: '',
                html: bankInfoHtml,
                showCancelButton: true,
                confirmButtonText: planLang.bankConfirmBtn,
                cancelButtonText: planLang.cancel,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                width: '600px',
                customClass: {
                    popup: 'bank-transfer-popup'
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                
                $.ajax({
                    url: '{{ route('subscription.bank.transfer') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        plan_id: planId,
                        plan_type: planType,
                        plan_amount: planAmount
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: '✅ ' + planLang.bankInitiatedTitle,
                                html: `
                                    <div class="text-center">
                                        <p>${planLang.bankInitiatedBody}</p>
                                        <p><strong>${planLang.bankNextSteps}</strong></p>
                                        <ol class="text-left">
                                            <li>${planLang.bankNext1}</li>
                                            <li>${planLang.bankNext2} <a href="mailto:${bankTransferConfig.email}">${bankTransferConfig.email}</a></li>
                                            <li>${planLang.bankNext3}</li>
                                        </ol>
                                        <p class="text-muted mt-3">${planLang.bankEmailFollowup}</p>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonText: planLang.gotIt,
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(planLang.bankRecordFailed + ' ' + (response.message || ''));
                        }
                    },
                    error: function(error) {
                        alert(planLang.bankRecordError);
                    }
                });
            });
        });
    });
</script>

@php
    $plans = \App\Models\Plans::where('status', 'active')->get();
@endphp

<div class="subscription-management-container">
    <!-- Header Section -->
    <div class="subscription-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 text-primary">
                    <i class="fas fa-crown me-2"></i>{{ __('messages.plan') }}
                </h4>
                <p class="text-muted mb-0">Manage your subscription plans and upgrades</p>
            </div>
            <div class="subscription-search">
                <div class="input-group input-group-search">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control dt-search border-start-0" 
                           placeholder="Search plans..." aria-label="Search">
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
                                <h6 class="mb-1 text-dark">Current Plan</h6>
                                <p class="mb-0 text-muted">Your active subscription details</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-success fs-6">Active</span>
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
                            <i class="fas fa-rocket me-1"></i>Actions
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
                        <h5 class="modal-title mb-0" id="upgradeModalLabel">Upgrade Your Plan</h5>
                        <small class="opacity-75">Choose your preferred payment method</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
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
                                                 alt="Plan Image" class="plan-image">
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark" id="plan_name">Premium Plan</h6>
                                            <p class="mb-0 text-muted">Unlock all premium features</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="plan-price">
                                        <span class="price-amount text-primary fs-3 fw-bold" id="plan_amount">€0.00</span>
                                        <small class="text-muted d-block">per month</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="plan-benefits mb-4">
                    <h6 class="text-dark mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>What you'll get:
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Unlimited service listings
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Priority customer support
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Advanced analytics
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Featured in search results
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Custom branding options
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    API access
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
                                <strong>Ready to upgrade?</strong><br>
                                <small>Click "Proceed to Payment" to choose your payment method and complete the upgrade.</small>
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
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
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
                        <h5 class="modal-title mb-0" id="paymentMethodModalLabel">Choose Payment Method</h5>
                        <small class="opacity-75">Select how you'd like to pay</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Payment Summary -->
                <div class="payment-summary mb-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Payment Summary</h6>
                            <div class="payment-amount">
                                <span class="amount text-primary fs-2 fw-bold" id="payment_amount">€0.00</span>
                                <small class="text-muted d-block">One-time payment</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods Grid -->
                <div class="payment-methods-grid">
                    <h6 class="text-dark mb-3">
                        <i class="fas fa-wallet me-2"></i>Available Payment Methods:
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-method-card" id="payWithWallet">
                                <div class="card h-100 border-0 shadow-sm payment-card">
                                    <div class="card-body text-center p-4">
                                        <div class="payment-icon-large mb-3">
                                            <i class="fas fa-wallet text-primary fa-2x"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">Wallet Payment</h6>
                                        <p class="card-text text-muted small mb-3">Pay using your wallet balance</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-success">Instant</span>
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
                                        <h6 class="card-title text-dark mb-2">Credit/Debit Card</h6>
                                        <p class="card-text text-muted small mb-3">Pay with Stripe secure payment</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-primary">Secure</span>
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
                                        <p class="card-text text-muted small mb-3">Pay with your PayPal account</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-info">Popular</span>
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
                                        <h6 class="card-title text-dark mb-2">Bank Transfer</h6>
                                        <p class="card-text text-muted small mb-3">Traditional bank transfer</p>
                                        <div class="payment-badge">
                                            <span class="badge bg-warning">Manual</span>
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
                                <strong>Secure Payment</strong><br>
                                <small class="text-muted">All payments are processed securely with industry-standard encryption.</small>
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
                                    let buttonText = planAmount > currentAmount ? 'Upgrade to' : 'Switch to';
                                    
                                    buttons +=
                                        `<button class="btn btn-warning upgrade-btn" data-plan="${plan.title}" data-id="${row.id}" data-amount="${plan.amount}">${buttonText} ${plan.title}</button> `;
                                });
                            } else {
                                buttons = '<span>No other plans available</span>';
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
            var planImageAlt = planType + ' Plan';
            
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
                    title: '🎉 Free Plan Upgrade',
                    html: `
                        <div class="text-center">
                            <div class="mb-3">
                                <img src="${planImageSrc}" alt="${planImageAlt}" style="width: 80px; height: 80px; object-fit: contain; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            </div>
                            <p class="mb-3">You're upgrading to the <strong>${planType}</strong> plan!</p>
                            <p class="text-muted">This is a free plan, so no payment is required.</p>
                        </div>
                    `,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Upgrade Now',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Upgrading your plan',
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
                                        title: '✅ Success!',
                                        text: `You have been successfully upgraded to ${planType}!`,
                                        icon: 'success',
                                        confirmButtonText: 'Great!',
                                        confirmButtonColor: '#28a745'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: '❌ Error',
                                        text: `Failed to upgrade to ${planType}: ${response.message || 'Unknown error'}`,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(error) {
                                console.error('Free plan upgrade error:', error);
                                Swal.fire({
                                    title: '❌ Error',
                                    text: `Failed to upgrade to ${planType}. Please try again.`,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
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
                title: '💳 Wallet Payment',
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-wallet text-primary fa-3x"></i>
                        </div>
                        <p class="mb-3">Pay <strong>${planAmountDisplay}</strong> using your wallet balance</p>
                        <p class="text-muted">This payment will be processed instantly.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Pay with Wallet',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing Payment...',
                        text: 'Please wait while we process your wallet payment',
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
                                    title: '✅ Payment Successful!',
                                    text: 'Your subscription has been upgraded successfully!',
                                    icon: 'success',
                                    confirmButtonText: 'Great!',
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: '❌ Payment Failed',
                                    text: response.message || 'Payment could not be processed',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                title: '❌ Error',
                                text: 'Payment failed. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
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
                title: '💳 Credit/Debit Card Payment',
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fab fa-cc-stripe text-primary fa-3x"></i>
                        </div>
                        <p class="mb-3">Pay <strong>${planAmountDisplay}</strong> using your credit/debit card</p>
                        <p class="text-muted">You will be redirected to Stripe's secure payment page.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Continue to Stripe',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#635bff',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Redirecting...',
                        text: 'Taking you to Stripe payment page',
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
                                    title: '❌ Error',
                                    text: response.message || 'Could not create Stripe payment session',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                title: '❌ Error',
                                text: 'Could not process Stripe payment. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
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
                title: '💳 PayPal Payment',
                html: `
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fab fa-paypal text-primary fa-3x"></i>
                        </div>
                        <p class="mb-3">Pay <strong>${planAmountDisplay}</strong> using your PayPal account</p>
                        <p class="text-muted">You will be redirected to PayPal's secure payment page.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Continue to PayPal',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0070ba',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Redirecting...',
                        text: 'Taking you to PayPal payment page',
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
                                    title: '❌ Error',
                                    text: response.message || 'Could not create PayPal payment session',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(error) {
                            Swal.fire({
                                title: '❌ Error',
                                text: 'Could not process PayPal payment. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK'
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
                        <h4 class="mb-2">Bank Transfer Payment</h4>
                        <p class="text-muted">Complete your subscription upgrade via bank transfer</p>
                    </div>
                    
                    <div class="subscription-info mb-4">
                        <h6 class="text-primary mb-2">📋 Subscription Details</h6>
                        <div class="row">
                            <div class="col-6"><strong>Plan:</strong> ${planType}</div>
                            <div class="col-6"><strong>Amount:</strong> ${planAmountDisplay}</div>
                        </div>
                    </div>

                    <div class="bank-details mb-4">
                        <h6 class="text-success mb-3">🏦 Bank Information</h6>
                        <div class="bank-info-card">
                            <div class="bank-row">
                                <span class="bank-label">Bank Name:</span>
                                <span class="bank-value">Norisbank</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">Country:</span>
                                <span class="bank-value">Germany</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">Account Number:</span>
                                <span class="bank-value">4776167</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">IBAN:</span>
                                <span class="bank-value">DE57760260000477616700</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-label">BIC/Swift:</span>
                                <span class="bank-value">NORDSDE71XXX</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="instructions mb-4">
                        <h6 class="text-warning mb-3">📝 Important Instructions</h6>
                        <div class="instruction-steps">
                            <div class="step">
                                <span class="step-number">1</span>
                                <span class="step-text">Transfer the exact amount (${planAmountDisplay}) to the bank account above</span>
                            </div>
                            <div class="step">
                                <span class="step-number">2</span>
                                <span class="step-text">Include your name and "Subscription ${planType}" in the transfer reference</span>
                            </div>
                            <div class="step">
                                <span class="step-number">3</span>
                                <span class="step-text">Send proof of payment (screenshot or PDF) to: <a href="mailto:billing@frobster.com" class="email-link">billing@frobster.com</a></span>
                            </div>
                            <div class="step">
                                <span class="step-number">4</span>
                                <span class="step-text">Your subscription will be activated within 24 hours after payment verification</span>
                            </div>
                        </div>
                    </div>

                    <div class="note-box">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Bank transfers may take varying business days to process. 
                            Your subscription will be activated once payment is verified.
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
                confirmButtonText: 'I Understand, Proceed',
                cancelButtonText: 'Cancel',
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
                                title: '✅ Bank Transfer Initiated',
                                html: `
                                    <div class="text-center">
                                        <p>Your subscription upgrade has been recorded.</p>
                                        <p><strong>Next Steps:</strong></p>
                                        <ol class="text-left">
                                            <li>Complete the bank transfer using the provided details</li>
                                            <li>Send proof of payment to <a href="mailto:billing@frobster.com">billing@frobster.com</a></li>
                                            <li>Your subscription will be activated within 24 hours</li>
                                        </ol>
                                        <p class="text-muted mt-3">You will receive a confirmation email shortly.</p>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonText: 'Got It!',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert('Failed to record bank transfer: ' + response.message);
                        }
                    },
                    error: function(error) {
                        alert('Failed to record bank transfer. Please try again.');
                    }
                });
            });
        });
    });
</script>

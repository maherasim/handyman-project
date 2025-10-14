@php
    $plans = \App\Models\Plans::where('status', 'active')->get();
@endphp

<h5 class="mb-2">{{ __('messages.plan') }}</h5>

<div class="row justify-content-end">
    <div class="col-md-3">
        <div class="d-flex justify-content-end">
            <div class="input-group input-group-search ml-auto">
                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search"
                    aria-describedby="addon-wrapping">
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table data-table mb-0">
        <thead class="table-color-heading">
            <tr class="text-secondary">
                <th scope="col">{{ __('messages.name') }}</th>
                <th scope="col">{{ __('messages.type') }}</th>
                <th scope="col">{{ __('messages.amount') }}</th>
                <th scope="col">{{ __('messages.start_at') }}</th>
                <th scope="col">{{ __('messages.end_at') }}</th>
                <th scope="col">{{ __('messages.status') }}</th>
                <th scope="col">Upgrade Your Plan</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Upgrade Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1" role="dialog" aria-labelledby="upgradeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="upgradeModalLabel">Upgrade Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to upgrade to <strong id="plan_name"></strong>?</p>
                <p>Amount: <strong>$<span id="plan_amount"></span></strong></p>
            </div>
            <div class="modal-footer">
                <form id="upgradeForm">
                    <input type="hidden" id="plan_id" name="plan_id">
                    <input type="hidden" id="plan_type" name="plan_type">
                    <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1" role="dialog" aria-labelledby="paymentMethodModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentMethodModalLabel">Choose Payment Method</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Please select your preferred payment method:</p>
                <div class="d-grid gap-2">
                    <button id="payWithWallet" class="btn btn-outline-primary">
                        <i class="fas fa-wallet me-1"></i> Wallet
                    </button>
                    <button id="payWithStripe" class="btn btn-outline-dark">
                        <i class="fab fa-cc-stripe me-1"></i> Stripe
                    </button>
                    <button id="payWithPaypal" class="btn btn-outline-primary">
                        <i class="fab fa-paypal me-1"></i> PayPal
                    </button>
                    <button id="payWithBank" class="btn btn-outline-secondary">
                        <i class="la la-university me-1"></i> Bank Transfer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var table;
        var loadurl =
            '{{ route('provider_detail_pages') }}?tabpage=all-plan&type=tbl&providerid={{ request()->providerid }}';
        var plans = [];

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
                        name: 'amount'
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

        // Handle upgrade button click
        // Handle upgrade button click
        $(document).on('click', '.upgrade-btn', function() {
            var planType = $(this).data('plan');
            var planId = $(this).data('id');
            var planAmount = $(this).data('amount');

            // Debug logging
            console.log('Plan Type:', planType);
            console.log('Plan Amount:', planAmount);
            console.log('Plan ID:', planId);

            $('#plan_id').val(planId);
            $('#plan_type').val(planType);
            $('#plan_name').text(planType + " Plan");
            $('#plan_amount').text(planAmount);

            // Check if it's a free plan (either by name or amount)
            if (planType.toLowerCase().includes('free') || planAmount == 0 || planAmount == '0') {
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
                            alert('You have been successfully upgraded to ' + planType + '.');
                            location.reload();
                        } else {
                            alert('Failed to upgrade to ' + planType + ': ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(error) {
                        console.error('Free plan upgrade error:', error);
                        alert('Failed to upgrade to ' + planType + '.');
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

        // Handle Wallet payment selection
        $('#payWithWallet').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount').text();

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
                        alert('Subscription upgraded successfully using wallet payment.');
                        location.reload();
                    } else {
                        alert('Payment failed: ' + response.message);
                    }
                },
                error: function(error) {
                    alert('Payment failed. Please try again.');
                }
            });
        });

        // Handle Stripe payment selection
        $('#payWithStripe').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount').text();

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
                        alert('Stripe payment failed: ' + response.message);
                    }
                },
                error: function(error) {
                    alert('Stripe payment failed. Please try again.');
                }
            });
        });

        // Handle PayPal payment selection
        $('#payWithPaypal').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount').text();

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
                        alert('PayPal payment failed: ' + response.message);
                    }
                },
                error: function(error) {
                    alert('PayPal payment failed. Please try again.');
                }
            });
        });

        // Handle Bank Transfer payment selection
        $('#payWithBank').on('click', function() {
            var planId = $('#plan_id').val();
            var planType = $('#plan_type').val();
            var planAmount = $('#plan_amount').text();

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
                            <div class="col-6"><strong>Amount:</strong> €${planAmount}</div>
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
                                <span class="step-text">Transfer the exact amount (€${planAmount}) to the bank account above</span>
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
                                <span class="step-text">Your subscription will be activated within 24 hours after verification</span>
                            </div>
                        </div>
                    </div>

                    <div class="note-box">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Bank transfers may take 1-3 business days to process. 
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

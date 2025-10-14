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
                            let upgradeOptions = plans.filter(plan => plan.title !==
                                currentPlan);

                            let buttons = '';
                            if (upgradeOptions.length > 0) {
                                upgradeOptions.forEach(plan => {
                                    buttons +=
                                        `<button class="btn btn-warning upgrade-btn" data-plan="${plan.title}" data-id="${row.id}" data-amount="${plan.amount}">Upgrade to ${plan.title}</button> `;
                                });
                            } else {
                                buttons = '<span>No upgrades available</span>';
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

            $('#plan_id').val(planId);
            $('#plan_type').val(planType);
            $('#plan_name').text(planType + " Plan");
            $('#plan_amount').text(planAmount);

            if (planType === "Free plan") {
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
                        alert('You have been successfully upgraded to Free Plan.');
                        location.reload();
                    },
                    error: function(error) {
                        alert('Failed to upgrade to Free Plan.');
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
                <div class="text-start">
                    <h6 class="mb-2">Bank Information</h6>
                    <div><strong>Bank Name:</strong> Norisbank</div>
                    <div><strong>Country:</strong> Germany</div>
                    <div><strong>Account Number:</strong> 4776167</div>
                    <div><strong>IBAN:</strong> DE57760260000477616700</div>
                    <div><strong>BIC/Swift:</strong> NORDSDE71XXX</div>
                    
                    <h6 class="mt-3">Instructions</h6>
                    <div class="small mt-1">
                        Send Proof of Payment (screenshot or PDF Document) to: 
                        <a href="mailto:billing@frobster.com">billing@frobster.com</a>
                    </div>
                </div>
            `;

            Swal.fire({
                title: 'Bank Transfer',
                html: bankInfoHtml,
                showCancelButton: true,
                confirmButtonText: 'Proceed',
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
                            alert('Bank transfer recorded. Please send proof of payment.');
                            location.reload();
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

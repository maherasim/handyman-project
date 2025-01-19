{{ html()->hidden('id', null)->attribute('placeholder', 'id')->class('form-control') }}
{{ html()->hidden('type', 'hjg')->attribute('placeholder', 'id')->class('form-control') }}

@php
    $plans = \App\Models\Plans::where('status', 'active')->get();
@endphp
<h5 class="mb-2">{{__('messages.plan')}}</h5>

<div class="row justify-content-end">
    <div class="col-md-3">
        <div class="d-flex justify-content-end">
            <div class="input-group input-group-search ml-auto">
                <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control dt-search" placeholder="Search..." aria-label="Search" aria-describedby="addon-wrapping">
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table data-table mb-0">
        <thead class="table-color-heading">
            <tr class="text-secondary">
                <th scope="col">{{__('messages.name')}}</th>
                <th scope="col">{{__('messages.type')}}</th>
                <th scope="col">{{__('messages.amount')}}</th>
                <th scope="col">{{__('messages.start_at')}}</th>
                <th scope="col">{{__('messages.end_at')}}</th>
                <th scope="col">{{__('messages.status')}}</th>
                <th scope="col">Upgrade Your Plan</th>
                {{-- <th scope="col">{{__('messages.plan_type')}}</th> --}}
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<!-- Existing Upgrade Modal -->
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

<!-- New Payment Method Selection Modal -->
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
                <button id="payWithStripe" class="btn btn-primary">Pay with Stripe</button>
                <button id="payWithPaypal" class="btn btn-secondary">Pay with PayPal</button>
            </div>
        </div>
    </div>
</div>
{{ html()->form()->close() }}
<script type="text/javascript">

    var loadurl = '{{route('provider_detail_pages')}}?tabpage=all-plan&type=tbl&providerid={{request()->providerid}}';

    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        responsive: true,
        dom: '<"row align-items-center"><"table-responsive my-3" rt><"row align-items-center" <"col-md-6" l><"col-md-6" p><"col-md-6" i>><"clear">',
        ajax: {
            url: loadurl,
            type: 'GET',
            data: function(d) {
                // Add custom search parameter
                d.search = {
                    value: $('.dt-search').val()
                };
            },
        },
        columns: [
            {
                data: 'title',
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
                render: function(data, type, row) {
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
                    let upgradeOptions = plans.filter(plan => plan.title !== currentPlan);

                    let buttons = '';
                    upgradeOptions.forEach(plan => {
                        buttons +=
                            `<button class="btn btn-warning upgrade-btn" data-plan="${plan.title}" data-id="${row.id}" data-amount="${plan.amount}">Upgrade to ${plan.title}</button> `;
                    });
                    return buttons;
                }
            }
        ],
        language: {
            processing: "{{ __('messages.processing') }}" // Set your custom processing text
        }
    });

    // Trigger search when user types in the input field
    $('.dt-search').on('keyup', function () {
        table.draw();
    });
</script>
<script type="text/javascript">
    var loadurl =
        '{{ route('provider_detail_pages') }}?tabpage=all-plan&type=tbl&providerid={{ request()->providerid }}';

    let plans = []; // Array to store available plans and their details

    // Fetch available plans dynamically from the server
    $.ajax({
        url: '{{ route('get.plans') }}',
        type: 'GET',
        success: function(data) {
            plans = data;
        },
        error: function(error) {
            console.log("Error fetching plans:", error);
        }
    });

    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: loadurl,
            type: 'GET'
        },
        columns: [{
                data: 'title',
                name: 'title'
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'plan_type',
                name: 'plan_type'
            },
            {
                data: null,
                name: 'duration',
                render: function(data, type, row) {
                    return `From ${row.start_at} , To ${row.end_at}`;
                }
            },
            {
                data: 'plan_type',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let currentPlan = data;
                    let upgradeOptions = plans.filter(plan => plan.title !== currentPlan);

                    let buttons = '';
                    upgradeOptions.forEach(plan => {
                        buttons +=
                            `<button class="btn btn-warning upgrade-btn" data-plan="${plan.title}" data-id="${row.id}" data-amount="${plan.amount}">Upgrade to ${plan.title}</button> `;
                    });
                    return buttons;
                }
            }
        ]
    });

    $(document).on('click', '.upgrade-btn', function() {
        var planType = $(this).data('plan');
        var planId = $(this).data('id');
        var planAmount = $(this).data('amount');

        // Populate the modal form fields
        $('#plan_id').val(planId);
        $('#plan_type').val(planType);
        $('#plan_name').text(planType + " Plan");
        $('#plan_amount').text(planAmount);

        // Show the modal
        $('#upgradeModal').modal('show');
    });

    // Submit the form and show payment method options
    $('#upgradeForm').on('submit', function(e) {
        e.preventDefault();

        $('#upgradeModal').modal('hide'); // Close the upgrade modal
        $('#paymentMethodModal').modal('show'); // Show payment method selection modal
    });

    // Handle Stripe payment selection
    $('#payWithStripe').on('click', function() {
        var planId = $('#plan_id').val();
        var planType = $('#plan_type').val();
        var planAmount = $('#plan_amount').text();

        // Redirect to Stripe payment route
        window.location.href =
            `{{ route('stripe') }}?plan_id=${planId}&plan_type=${planType}&plan_amount=${planAmount}`;
    });

    // Handle PayPal payment selection
    // Corrected PayPal payment selection handler
    $('#payWithPaypal').on('click', function() {
        var planId = $('#plan_id').val();
        var planType = $('#plan_type').val();
        var planAmount = $('#plan_amount').text();

        window.location.href =
            `{{ route('paypal.payment') }}?plan_id=${planId}&plan_type=${planType}&plan_amount=${planAmount}`;
    });
</script>

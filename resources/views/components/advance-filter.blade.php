<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel"
    data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="offcanvas-header border-bottom">
        @if (isset($title))
            {{ $title }}
        @endif
        <div>
            <button type="button" id="refresh-filter" class="btn-icon btn-refresh me-2" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button type="button" data-bs-dismiss="offcanvas" aria-label="Close" class="btn-icon btn-close-offcanvas">
                <i class="far fa-window-close"></i>
            </button>
        </div>
    </div>
    <div class="offcanvas-body">
        {{ $slot }}
    </div>
    
    <div class="offcanvas-footer p-3">
        <div class="text-end">
            <button type="reset" class="btn btn-danger" data-bs-dismiss="offcanvas"
            id="reset-filter">{{ __('messages.reset_all_button') }}</button>
        </div>        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // var resetButton = document.getElementById('reset-filter');
        // resetButton.addEventListener('click', function(event) {
        //     var offcanvas = bootstrap.Offcanvas.getInstance('#offcanvasExample');
        //     if (offcanvas) {
        //         offcanvas.hide();
        //     }

        // });
        document.getElementById('reset-filter').addEventListener('click', function() {
                // Clear selected filters object
                selectedFilters = {
                    booking_status: [],
                    payment_status: [],
                    payment_type: [],
                    advance_paid: [],
                    date_range: ''
                };

                // Reset all filter buttons
                document.querySelectorAll('.filter-button').forEach(button => {
                    button.classList.remove('active');
                    button.classList.add('inactive');
                });

                // Reset dropdowns
                document.querySelectorAll('.select2').forEach(select => {
                    select.value = '';
                    $(select).trigger('change'); // Trigger Select2 update if using Select2
                });

                // Reset Date Range Picker
                document.getElementById('datepicker1').value = '';

                // Reload the DataTable
                $('#datatable').DataTable().ajax.reload();
            });
        var refreshButton = document.getElementById('refresh-filter');
        refreshButton.addEventListener('click', function(event) {
            // Reset all inputs within the offcanvas
            const offcanvas = document.querySelector('#offcanvasExample');
            
            // Reset regular inputs
            offcanvas.querySelectorAll('input').forEach(input => {
                if (input.type === 'text' || input.type === 'number' || input.type === 'email' || input.type === 'date') {
                    input.value = '';
                } else if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                }
            });

            // Reset textareas
            offcanvas.querySelectorAll('textarea').forEach(textarea => {
                textarea.value = '';
            });

            // Reset Select2 elements
            offcanvas.querySelectorAll('.select2-hidden-accessible').forEach(select => {
                $(select).val(null).trigger('change');
            });

            // Reset regular selects
            offcanvas.querySelectorAll('select:not(.select2-hidden-accessible)').forEach(select => {
                select.selectedIndex = 0;
            });

            // Log to confirm the function is running
            console.log('Form refresh attempted');

            // Optional: Refresh DataTable if it exists
            if (typeof table !== 'undefined') {
                table.ajax.reload();
            }
        });
    });
    const offcanvasElem = document.querySelector('#offcanvasExample')
    offcanvasElem.addEventListener('shown.bs.offcanvas', function() {
        $('.datatable-filter .select2').select2({
            dropdownParent: $('#offcanvasExample')
        });
    })
</script>

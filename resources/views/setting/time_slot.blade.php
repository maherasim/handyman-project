<div class="col-md-12">
    <div class="row">
        <div class="col-md-6">
            <div class="user-sidebar">
                <div class="user-body user-profile text-center">
                    <div class="sideuser-info">
                        <h4 class="mb-2">{{ __('messages.update') }} {{$pageTitle}}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar Implementation -->
    <div class="row">
        <div class="col-md-12">
            <div id="calendar"></div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            {{ html()->form('POST', route('providerslot.store'))->attributes(['data-toggle' => 'validator', 'id' => 'provider-form'])->open() }}
            <input type="hidden" name="id" id="provider-id" value="{{ $provider_id }}">
            {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end mt-15')->id('submit') }}
            {{ html()->form()->close() }}
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var selectedSlots = [];

        // Initialize FullCalendar
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'timeGrid', 'interaction'],
            themeSystem: 'bootstrap',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 600,
            selectable: true,
            editable: false,
            eventLimit: false,

            // Load existing slots
            events: [
                @foreach ($slotsArray['days'] as $day)
                    @if (isset($activeSlots[$day]))
                        @foreach ($activeSlots[$day] as $slot)
                            {
                                title: "{{ ucfirst($day) }} Slot",
                                start: "{{ now()->format('Y-m-d') }}T{{ $slot }}",
                                color: 'rgb(19, 193, 240)',
                                textColor: '#fff'
                            },
                        @endforeach
                    @endif
                @endforeach
            ],

            // Handle slot selection
            select: function (info) {
                var selectedTime = info.startStr.substring(11, 16);
                var selectedDate = info.startStr.substring(0, 10);
                
                var existingSlotIndex = selectedSlots.findIndex(slot => slot.date === selectedDate && slot.time === selectedTime);
                if (existingSlotIndex === -1) {
                    selectedSlots.push({ date: selectedDate, time: selectedTime });
                    calendar.addEvent({
                        title: "Selected Slot",
                        start: info.startStr,
                        color: "green"
                    });
                } else {
                    selectedSlots.splice(existingSlotIndex, 1);
                    var eventToRemove = calendar.getEvents().find(e => e.startStr === info.startStr);
                    if (eventToRemove) eventToRemove.remove();
                }
            }
        });

        calendar.render();

        // Handle form submission
        $('#provider-form').on('submit', function (e) {
            e.preventDefault();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            $.ajax({
                type: 'POST',
                url: '{{ route("providerslot.store") }}',
                data: { provider_id: $('#provider-id').val(), slots: selectedSlots },
                success: function (response) {
                    alert(response.message);
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });
    });
</script>

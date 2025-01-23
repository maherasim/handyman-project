<x-master-layout>
    <div class="container-fluid">
        @include('partials._provider')
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">
                                {{$providerdata->first_name .' '. $providerdata->last_name}} {{$pageTitle}}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', '#')->attribute('data-toggle', 'validator')->id('provider')->open() }}
                        <div class="row">
                            <div class="col-md-12">
                                {{ html()->hidden('id', $provider_id)->class('form-control')->attribute('placeholder', 'id') }}

                                <div class="form-group">
                                    <h5 class="form-control-label">{{ __('messages.select_time_slot') }}</h5>
                                    <div id="calendar"></div>
                                </div>

                            </div>
                        </div>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'interaction'],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            height: 600,
            selectable: true,
            events: [
                // Populate events dynamically from your $slotsArray
                @foreach ($slotsArray as $slotDay)
                    @if (isset($slotDay['slot']))
                        @foreach ($slotDay['slot'] as $slot)
                            {
                                title: '{{ ucfirst($slotDay['day']) }} Slot',
                                start: '{{ date('Y-m-d', strtotime($slotDay['day'])) }}T{{ sprintf('%02d:00:00', $slot) }}',
                                allDay: false
                            },
                        @endforeach
                    @endif
                @endforeach
            ],
            select: function (info) {
                alert('Selected: ' + info.startStr);
                // Handle slot selection here
            },
            eventClick: function (info) {
                alert('Event: ' + info.event.title);
                // Handle slot click here
            }
        });
        calendar.render();
    });
</script>

<script>
    $(document).ready(function () {
        setActiveDay('mon');
        $('.day-link').on('click', function (e) {
            e.preventDefault();
            var selectedDay = $(this).data('day');
            setActiveDay(selectedDay);
            showActiveDaySlots();
        });
       
        function setActiveDay(day) {
            $('.day-slot').removeClass('active');
            $('.day-link').removeClass('active');
            $('.day-link[data-day="' + day + '"]').addClass('active');
            $('.day-slot#' + day).addClass('active');
            activeDay = day;
        }

        function showActiveDaySlots() {
            $('.day-slot').hide();
            $('.day-slot.active').show();
        }
    });
</script>

<x-master-layout>
    <div class="container-fluid">
        @include('partials._provider')
@php
    $activeSlots = [
    'mon' => ['09:00', '10:00'],
    'wed' => ['14:00'],
    // etc.
];
@endphp
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $providerdata->first_name . ' ' . $providerdata->last_name }} {{ $pageTitle }}</h5>
                            <a href="{{ route('provider.time-slot', ['id' => $provider_id]) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form id="provider-form">
                            @csrf
                            <input type="hidden" name="id" id="provider-id" value="{{ $provider_id }}">
                            <div id="calendar"></div>
                            <button type="submit" class="btn btn-primary mt-3">{{ __('messages.submit') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>

<!-- FullCalendar JS/CSS dependencies (include only once in your layout) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales-all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Inject slot data safely -->
<script>
    const previouslySavedSlots = {!! json_encode($activeSlots ?? []) !!};
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.initializeCalendar = function () {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            const selectedSlots = [];

            const dayMap = {
                sun: 0,
                mon: 1,
                tue: 2,
                wed: 3,
                thu: 4,
                fri: 5,
                sat: 6
            };

            const events = [];

            if (previouslySavedSlots && typeof previouslySavedSlots === 'object') {
                Object.entries(previouslySavedSlots).forEach(([day, times]) => {
                    times.forEach(time => {
                        const [hour, minute] = time.split(':');
                        events.push({
                            title: 'Available',
                            daysOfWeek: [dayMap[day.toLowerCase()]],
                            startTime: `${hour}:${minute}`,
                            endTime: `${String(parseInt(hour) + 1).padStart(2, '0')}:${minute}`,
                            rendering: 'background',
                            backgroundColor: '#198754',
                            borderColor: '#198754',
                            classNames: ['preselected-slot']
                        });
                        selectedSlots.push({ day, time });
                    });
                });
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['timeGrid', 'interaction'],
                initialView: 'timeGridWeek',
                themeSystem: 'bootstrap',
                headerToolbar: {
                    left: '',
                    center: 'title',
                    right: ''
                },
                allDaySlot: false,
                slotDuration: '01:00:00',
                selectable: true,
                height: 600,
                events: events,
                select: function (info) {
                    const dayIndex = info.start.getDay();
                    const dayName = Object.keys(dayMap).find(key => dayMap[key] === dayIndex);

                    if (!dayName) return;

                    const hour = String(info.start.getHours()).padStart(2, '0') + ':00';
                    const exists = selectedSlots.find(slot => slot.day === dayName && slot.time === hour);

                    if (exists) {
                        const index = selectedSlots.indexOf(exists);
                        if (index > -1) selectedSlots.splice(index, 1);

                        calendar.getEvents().forEach(ev => {
                            if (
                                !ev.classNames.includes('preselected-slot') &&
                                ev.start.getDay() === dayIndex &&
                                ev.start.getHours() === info.start.getHours()
                            ) {
                                ev.remove();
                            }
                        });
                    } else {
                        selectedSlots.push({ day: dayName, time: hour });

                        calendar.addEvent({
                            title: 'Selected',
                            start: info.start,
                            end: info.end,
                            backgroundColor: '#198754',
                            borderColor: '#198754',
                            classNames: ['user-slot']
                        });
                    }
                }
            });

            calendar.render();

            $('#provider-form').on('submit', function (e) {
                e.preventDefault();

                const formattedSlots = [];
                const groupedSlots = {};

                selectedSlots.forEach(slot => {
                    if (!slot.day) return;
                    const day = slot.day.toLowerCase();
                    if (!groupedSlots[day]) groupedSlots[day] = [];
                    groupedSlots[day].push(slot.time);
                });

                for (const day in groupedSlots) {
                    formattedSlots.push({ day, time: groupedSlots[day] });
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route("providerslot.store") }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        provider_id: $('#provider-id').val(),
                        slots: formattedSlots
                    },
                    success: function (res) {
                        alert(res.message || 'Availability updated.');
                    },
                    error: function (err) {
                        alert('Error updating availability');
                        console.error(err);
                    }
                });
            });
        };

        window.initializeCalendar();
    });
</script>

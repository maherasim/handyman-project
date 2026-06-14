
<div class="col-md-12">
    <div class="row ">
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
    <div class="row ">
        <div class="col-md-12">
            <form id="provider-form">
                @csrf
                <input type="hidden" name="id" id="provider-id" value="{{ $provider_id }}">
                <div id="calendar"></div>
                <button type="submit" class="btn btn-primary mt-3">{{ __('messages.submit') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
    window.initializeCalendar = function () {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        const selectedSlots = [];

        const previouslySavedSlots = @json($activeSlots); // e.g. { mon: ['09:00', '10:00'], ... }
        const dayMap = {
            sun: 0,
            mon: 1,
            tue: 2,
            wed: 3,
            thu: 4,
            fri: 5,
            sat: 6
        };

        // Convert stored slots to FullCalendar event objects
        const events = [];
        Object.entries(previouslySavedSlots).forEach(([day, times]) => {
            times.forEach(time => {
                const [hour, minute] = time.split(':');
                events.push({
                    title: '{{ __('messages.available') }}',
                    daysOfWeek: [dayMap[day.toLowerCase()]],
                    startTime: `${hour}:${minute}`,
                    endTime: `${String(parseInt(hour) + 1).padStart(2, '0')}:${minute}`, // 1 hour slot
                    rendering: 'background',
                    backgroundColor: '#198754',
                    borderColor: '#198754',
                    classNames: ['preselected-slot']
                });
                selectedSlots.push({ day, time });
            });
        });

        const calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['timeGrid', 'interaction', 'bootstrap'],
            themeSystem: 'bootstrap',
            initialView: 'timeGridWeek',
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

                if (!dayName) return; // Avoid adding undefined day

                const hour = String(info.start.getHours()).padStart(2, '0') + ':00';

                const exists = selectedSlots.find(slot => slot.day === dayName && slot.time === hour);

                if (exists) {
                    selectedSlots.splice(selectedSlots.indexOf(exists), 1);
                    calendar.getEvents().forEach(ev => {
                        if (
                            ev.classNames.includes('preselected-slot') === false &&
                            ev.start.getDay() === dayIndex &&
                            ev.start.getHours() === info.start.getHours()
                        ) {
                            ev.remove();
                        }
                    });
                } else {
                    selectedSlots.push({ day: dayName, time: hour });
                    calendar.addEvent({
                        title: '{{ __('messages.selected_label') }}',
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
                if (!slot.day) return; // safeguard
                const shortDay = slot.day.toLowerCase();
                if (!groupedSlots[shortDay]) {
                    groupedSlots[shortDay] = [];
                }
                groupedSlots[shortDay].push(slot.time);
            });

            for (const day in groupedSlots) {
                formattedSlots.push({
                    day: day,
                    time: groupedSlots[day]
                });
            }

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            $.ajax({
                type: 'POST',
                url: '{{ route("providerslot.store") }}',
                data: {
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
</script>

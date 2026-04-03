<x-master-layout>
    <div class="container-fluid">
        @include('partials._provider')
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

<!-- FullCalendar JS/CSS dependencies -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales-all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        @php
            $fcLocaleMap = ['de' => 'de', 'en' => 'en', 'fr' => 'fr', 'it' => 'it', 'pt' => 'pt'];
            $fcLocale = $fcLocaleMap[app()->getLocale()] ?? 'en';
        @endphp
        const fcLocale = @json($fcLocale);
        const mondayFirst = ['de', 'fr', 'it'].includes(fcLocale);
        const slotEventTitle = @json(__('messages.provider_calendar_event_available'));

        // Previously saved slots from backend (date-based)
        const previouslySavedSlots = {!! json_encode($calendarSlots ?? []) !!};
        
        // Track selected slots by date
        const selectedSlots = {};

        // Initialize with previously saved slots
        Object.keys(previouslySavedSlots).forEach(date => {
            selectedSlots[date] = [...previouslySavedSlots[date]];
        });

        // Function to get dates that have slots (for marking green)
        function getDatesWithSlots() {
            return Object.keys(selectedSlots).filter(date => 
                selectedSlots[date] && selectedSlots[date].length > 0
            );
        }

        // Build events array for FullCalendar
        const events = [];
        Object.keys(selectedSlots).forEach(date => {
            selectedSlots[date].forEach(time => {
                const [hour, minute] = time.split(':');
                const startDateTime = `${date}T${hour}:${minute}:00`;
                const endDateTime = `${date}T${String(parseInt(hour) + 1).padStart(2, '0')}:${minute}:00`;
                
                events.push({
                    title: slotEventTitle,
                    start: startDateTime,
                    end: endDateTime,
                    backgroundColor: '#198754',
                    borderColor: '#198754',
                    classNames: ['preselected-slot'],
                    extendedProps: {
                        isPreselected: true
                    }
                });
            });
        });

        // Get selected date from URL parameter if provided
        const urlParams = new URLSearchParams(window.location.search);
        const selectedDate = urlParams.get('date');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['timeGrid', 'dayGrid', 'interaction'],
            initialView: selectedDate ? 'timeGridDay' : 'dayGridMonth', // Show day view if date is selected
            initialDate: selectedDate || undefined, // Navigate to selected date if provided
            locale: fcLocale,
            firstDay: mondayFirst ? 1 : 0,
            height: 'auto',
            contentHeight: 650,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false, // Use 24-hour format
                meridiem: false
            },
            views: {
                dayGridMonth: {
                    // Show time slots in month view
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                },
                timeGridWeek: {
                    slotDuration: '01:00:00',
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    }
                },
                timeGridDay: {
                    slotDuration: '01:00:00',
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    }
                }
            },
            allDaySlot: false,
            selectable: true,
            selectMirror: true,
            events: events,
            dayCellContent: function(info) {
                // Mark dates with slots in green in month view
                if (info.view.type === 'dayGridMonth') {
                    const dateStr = info.date.toISOString().split('T')[0];
                    const datesWithSlots = getDatesWithSlots();
                    if (datesWithSlots.includes(dateStr)) {
                        info.el.style.backgroundColor = '#198754';
                        info.el.style.color = '#ffffff';
                        info.el.style.borderRadius = '5px';
                        info.el.style.fontWeight = 'bold';
                    }
                }
            },
            select: function (info) {
                // Only allow selection in timeGrid views (week/day), not month view
                if (info.view.type === 'dayGridMonth') {
                    calendar.unselect();
                    return;
                }

                // Handle date/time selection
                const start = info.start;
                const end = info.end || new Date(start.getTime() + 60 * 60 * 1000); // Add 1 hour
                
                // Format date and time properly
                const dateStr = start.toISOString().split('T')[0]; // YYYY-MM-DD
                const hours = String(start.getHours()).padStart(2, '0');
                const minutes = String(start.getMinutes()).padStart(2, '0');
                const timeStr = `${hours}:${minutes}:00`;
                
                // Check if this slot already exists
                const slotExists = selectedSlots[dateStr] && selectedSlots[dateStr].includes(timeStr);
                
                if (slotExists) {
                    // Remove slot
                    if (selectedSlots[dateStr]) {
                        selectedSlots[dateStr] = selectedSlots[dateStr].filter(t => t !== timeStr);
                        if (selectedSlots[dateStr].length === 0) {
                            delete selectedSlots[dateStr];
                        }
                    }
                    
                    // Remove event from calendar (both preselected and user-selected)
                    calendar.getEvents().forEach(ev => {
                        const evDateStr = ev.start.toISOString().split('T')[0];
                        const evHours = String(ev.start.getHours()).padStart(2, '0');
                        const evMinutes = String(ev.start.getMinutes()).padStart(2, '0');
                        const evTimeStr = `${evHours}:${evMinutes}:00`;
                        
                        if (evDateStr === dateStr && evTimeStr === timeStr) {
                            ev.remove();
                        }
                    });
                } else {
                    // Add slot
                    if (!selectedSlots[dateStr]) {
                        selectedSlots[dateStr] = [];
                    }
                    if (!selectedSlots[dateStr].includes(timeStr)) {
                        selectedSlots[dateStr].push(timeStr);
                        selectedSlots[dateStr].sort();
                    }
                    
                    // Add event to calendar
                    calendar.addEvent({
                        title: slotEventTitle,
                        start: start.toISOString(),
                        end: end.toISOString(),
                        backgroundColor: '#198754',
                        borderColor: '#198754',
                        classNames: ['user-slot'],
                        extendedProps: {
                            isPreselected: false
                        }
                    });
                }
                
                // Refresh day cells to update green highlighting
                if (calendar.view.type === 'dayGridMonth') {
                    calendar.render();
                }
                
                calendar.unselect();
            },
            eventClick: function (info) {
                // Allow clicking events to remove them
                const start = info.event.start;
                const dateStr = start.toISOString().split('T')[0];
                const hours = String(start.getHours()).padStart(2, '0');
                const minutes = String(start.getMinutes()).padStart(2, '0');
                const timeStr = `${hours}:${minutes}:00`;
                
                if (selectedSlots[dateStr]) {
                    selectedSlots[dateStr] = selectedSlots[dateStr].filter(t => t !== timeStr);
                    if (selectedSlots[dateStr].length === 0) {
                        delete selectedSlots[dateStr];
                    }
                }
                
                info.event.remove();
                
                // Refresh day cells to update green highlighting
                if (calendar.view.type === 'dayGridMonth') {
                    calendar.render();
                }
            },
            dateClick: function(info) {
                // When clicking on a date in month view, switch to day view for that date
                if (info.view.type === 'dayGridMonth') {
                    calendar.changeView('timeGridDay', info.dateStr);
                }
            }
        });

        calendar.render();

        // Handle form submission
        $('#provider-form').on('submit', function (e) {
            e.preventDefault();

            // Format slots for API (date-based)
            const formattedSlots = [];
            
            Object.keys(selectedSlots).forEach(date => {
                if (selectedSlots[date] && selectedSlots[date].length > 0) {
                    formattedSlots.push({
                        date: date,
                        time: selectedSlots[date]
                    });
                }
            });

            $.ajax({
                type: 'POST',
                url: '{{ route("providerslot.store") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    provider_id: $('#provider-id').val(),
                    slots: formattedSlots
                },
                success: function (res) {
                    alert(res.message || 'Availability updated successfully.');
                    // Optionally reload the page to refresh the calendar
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                },
                error: function (err) {
                    console.error(err);
                    alert('Error updating availability. Please try again.');
                }
            });
        });
    });
</script>

<style>
    #calendar {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* FullCalendar Professional Styling */
    .fc {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    
    .fc-header-toolbar {
        margin-bottom: 1.5em;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }
    
    .fc-button {
        padding: 8px 16px;
        font-size: 14px;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .fc-button-primary {
        background-color: #198754;
        border-color: #198754;
    }
    
    .fc-button-primary:hover {
        background-color: #157347;
        border-color: #146c43;
    }
    
    .fc-button-primary:not(:disabled):active,
    .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #146c43;
        border-color: #146c43;
    }
    
    .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #212529;
    }
    
    .fc-daygrid-day {
        border: 1px solid #e9ecef;
    }
    
    .fc-daygrid-day-number {
        padding: 8px;
        font-weight: 500;
    }
    
    .fc-col-header-cell {
        padding: 10px 0;
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }
    
    .fc-timegrid-slot {
        height: 50px;
    }
    
    .fc-timegrid-col-events {
        margin: 2px;
    }
    
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .fc-event:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .fc-day:hover {
        cursor: pointer;
        background-color: rgba(25, 135, 84, 0.05) !important;
        transition: background-color 0.2s ease;
    }
    
    .fc-daygrid-day.fc-day-today {
        border: 2px solid #198754;
        background-color: rgba(25, 135, 84, 0.05);
    }
    
    .fc-timegrid-now-indicator-line {
        border-color: #198754;
    }
    
    .fc-timegrid-now-indicator-arrow {
        border-color: #198754;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        #calendar {
            padding: 10px;
        }
        .fc-header-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        .fc-toolbar-title {
            font-size: 1.2rem;
        }
        .fc-timegrid-slot {
            height: 40px;
        }
    }
</style>

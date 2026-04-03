<x-master-layout>
    <div class="container-fluid">
        @include('partials._provider')

        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $providerdata->first_name . ' ' . $providerdata->last_name }} {{ $pageTitle }}</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('provider.edit-time-slot', ['id' => $provider_id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-edit"></i> {{ __('messages.update') }}
                                </a>
                                <a href="{{ route('provider.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                        
                        <div class="mt-3">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width: 20px; height: 20px; background-color: #198754; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></div>
                                    <span class="font-weight-semibold" style="color: #495057;">
                                     {{ __('messages.provider_calendar_legend_available') }}
                                    </span>
                                </div>
                                {{-- <div class="text-muted small">
                                    <i class="fa fa-info-circle"></i> Click on any green date to view or edit time slots
                                </div> --}}
                            </div>
                        </div>
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
<!-- SweetAlert2 for professional modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        @php
            $appLocale = app()->getLocale();
            $fcLocaleMap = ['de' => 'de', 'en' => 'en', 'fr' => 'fr', 'it' => 'it', 'pt' => 'pt'];
            $fcLocale = $fcLocaleMap[$appLocale] ?? 'en';
            $intlMap = ['de' => 'de-DE', 'en' => 'en-US', 'fr' => 'fr-FR', 'it' => 'it-IT', 'pt' => 'pt-BR'];
            $intlLocale = $intlMap[$appLocale] ?? 'en-US';
        @endphp
        const fcLocale = @json($fcLocale);
        const intlLocale = @json($intlLocale);
        const calendarI18n = {
            modal_has_slots: @json(__('messages.provider_calendar_modal_has_slots')),
            modal_slots_heading: @json(__('messages.provider_calendar_modal_slots_heading')),
            no_slots: @json(__('messages.provider_calendar_no_slots')),
            close: @json(__('messages.close')),
            slot_word_one: @json(__('messages.provider_calendar_slot_word_one')),
            slot_word_other: @json(__('messages.provider_calendar_slot_word_other')),
        };

        function formatHasSlotsSentence(count) {
            const parts = String(calendarI18n.modal_has_slots).split('|');
            const tpl = Number(count) === 1 ? parts[0] : (parts[1] || parts[0]);
            return tpl.replace(/:count/gi, String(count));
        }

        function formatSlotTime(timeStr) {
            const [hours, minutes] = timeStr.split(':');
            const d = new Date('2000-01-01T' + hours + ':' + minutes + ':00');
            const use24h = intlLocale.startsWith('de') || intlLocale === 'fr-FR' || intlLocale === 'it-IT';
            return d.toLocaleTimeString(intlLocale, use24h
                ? { hour: '2-digit', minute: '2-digit', hour12: false }
                : { hour: 'numeric', minute: '2-digit', hour12: true });
        }

        function formatModalDate(dateStr) {
            const dateObj = new Date(dateStr + 'T00:00:00');
            return dateObj.toLocaleDateString(intlLocale, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        }

        // Dates that have slots (from backend)
        const datesWithSlots = {!! json_encode($datesWithSlots ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
        const calendarSlots = {!! json_encode($calendarSlots ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};
        
        // Ensure calendarSlots keys are strings (in case PHP encoded them as numbers)
        const finalCalendarSlots = {};
        Object.keys(calendarSlots).forEach(key => {
            finalCalendarSlots[String(key)] = calendarSlots[key];
        });

        // Ensure datesWithSlots is an array (handle case where it might be an object)
        const datesArray = Array.isArray(datesWithSlots) ? datesWithSlots : Object.values(datesWithSlots || {});

        // Build events for dates with slots
        const events = [];
        datesArray.forEach(date => {
            // Ensure date is in YYYY-MM-DD format
            const normalizedDate = String(date).split('T')[0];
            const slotsForDate = finalCalendarSlots[normalizedDate] || [];
            const sc = slotsForDate.length;
            const slotLabel = sc === 1 ? calendarI18n.slot_word_one : calendarI18n.slot_word_other;
            events.push({
                title: sc + ' ' + slotLabel,
                start: normalizedDate,
                allDay: true,
                backgroundColor: '#198754',
                borderColor: '#198754',
                textColor: '#ffffff',
                display: 'background',
                classNames: ['has-slots'],
                extendedProps: {
                    date: normalizedDate,
                    slotCount: slotsForDate.length
                }
            });
        });
        
        // Debug: log calendar slots structure
        console.log('Calendar Slots Structure:', finalCalendarSlots);
        console.log('Dates with Slots:', datesArray);

        const mondayFirst = ['de', 'fr', 'it'].includes(fcLocale);

        const calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'interaction'],
            initialView: 'dayGridMonth',
            locale: fcLocale,
            firstDay: mondayFirst ? 1 : 0,
            height: 'auto',
            contentHeight: 600,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            views: {
                dayGridMonth: {
                    // Month view
                },
                timeGridWeek: {
                    slotDuration: '01:00:00',
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                },
                timeGridDay: {
                    slotDuration: '01:00:00',
                    slotMinTime: '00:00:00',
                    slotMaxTime: '24:00:00',
                }
            },
            events: events,
            eventDisplay: 'block',
            dayCellContent: function(info) {
                // Customize day cell to show green background for dates with slots
                const dateStr = info.date.toISOString().split('T')[0];
                const normalizedDateStr = String(dateStr);
                if (datesArray.includes(normalizedDateStr) || datesArray.some(d => String(d).split('T')[0] === normalizedDateStr)) {
                    info.el.style.backgroundColor = '#198754';
                    info.el.style.color = '#ffffff';
                    info.el.style.borderRadius = '5px';
                    info.el.style.fontWeight = 'bold';
                }
            },
            dateClick: function(info) {
                // When clicking on a date, show modal if it has slots, otherwise redirect to edit
                const dateStr = info.dateStr;
                // Ensure date is in YYYY-MM-DD format
                const normalizedDate = String(dateStr).split('T')[0];
                // Try multiple key formats to find slots
                const slots = finalCalendarSlots[normalizedDate] || finalCalendarSlots[dateStr] || [];
                
                // Debug: log to console
                console.log('Date clicked:', dateStr, 'Normalized:', normalizedDate);
                console.log('Calendar slots for this date:', finalCalendarSlots[normalizedDate]);
                console.log('All calendar slots keys:', Object.keys(finalCalendarSlots));
                
                if (slots.length > 0) {
                    // Show the same professional modal
                    const formattedDate = formatModalDate(String(dateStr).split('T')[0]);
                    
                    let slotsHtml = '<div style="text-align: left; margin-top: 15px;">';
                    slotsHtml += '<strong style="color: #198754; display: block; margin-bottom: 10px;">' + calendarI18n.modal_slots_heading + '</strong>';
                    slotsHtml += '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px;">';
                    slots.forEach(slot => {
                        const timeStr = formatSlotTime(slot);
                        slotsHtml += `<span style="display: inline-block; background: #198754; color: white; padding: 6px 12px; border-radius: 5px; font-size: 13px; font-weight: 500;">${timeStr}</span>`;
                    });
                    slotsHtml += '</div>';
                    slotsHtml += '</div>';
                    
                    Swal.fire({
                        title: '<strong style="color: #198754;">' + formattedDate + '</strong>',
                        html: '<div style="text-align: center;">' +
                              '<p style="color: #6c757d; margin-bottom: 15px;">' + formatHasSlotsSentence(slots.length) + '</p>' +
                              slotsHtml +
                              '</div>',
                        icon: 'info',
                        iconColor: '#198754',
                        showConfirmButton: false,
                        showCancelButton: true,
                        cancelButtonText: calendarI18n.close,
                        cancelButtonColor: '#6c757d',
                        width: '500px',
                        padding: '2rem',
                        customClass: {
                            popup: 'slot-modal-popup',
                            title: 'slot-modal-title',
                            htmlContainer: 'slot-modal-content'
                        }
                    });
                } else {
                    // If no slots, directly redirect to edit page
                    window.location.href = '{{ route("provider.edit-time-slot", ["id" => $provider_id]) }}?date=' + normalizedDate;
                }
            },
            eventClick: function(info) {
                // When clicking on an event (date with slots), show professional modal
                const dateStr = info.event.start.toISOString().split('T')[0];
                // Ensure date is in YYYY-MM-DD format
                const normalizedDate = String(dateStr).split('T')[0];
                // Try to get date from extendedProps first, then fallback to calendarSlots
                const eventDate = info.event.extendedProps?.date || normalizedDate;
                const slots = finalCalendarSlots[eventDate] || finalCalendarSlots[normalizedDate] || finalCalendarSlots[dateStr] || [];
                
                // Debug: log to console
                console.log('Event clicked:', dateStr, 'Normalized:', normalizedDate, 'Event Date:', eventDate);
                console.log('Calendar slots for this date:', finalCalendarSlots[eventDate] || finalCalendarSlots[normalizedDate]);
                console.log('All calendar slots keys:', Object.keys(finalCalendarSlots));
                
                // Format date nicely
                const formattedDate = formatModalDate(String(dateStr).split('T')[0]);
                
                // Format time slots nicely
                let slotsHtml = '';
                if (slots.length > 0) {
                    slotsHtml = '<div style="text-align: left; margin-top: 15px;">';
                    slotsHtml += '<strong style="color: #198754; display: block; margin-bottom: 10px;">' + calendarI18n.modal_slots_heading + '</strong>';
                    slotsHtml += '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px;">';
                    slots.forEach(slot => {
                        const timeStr = formatSlotTime(slot);
                        slotsHtml += `<span style="display: inline-block; background: #198754; color: white; padding: 6px 12px; border-radius: 5px; font-size: 13px; font-weight: 500;">${timeStr}</span>`;
                    });
                    slotsHtml += '</div>';
                    slotsHtml += '</div>';
                } else {
                    slotsHtml = '<p style="color: #6c757d; margin-top: 10px;">' + calendarI18n.no_slots + '</p>';
                }
                
                // Show professional SweetAlert2 modal
                Swal.fire({
                    title: '<strong style="color: #198754;">' + formattedDate + '</strong>',
                    html: '<div style="text-align: center;">' +
                          '<p style="color: #6c757d; margin-bottom: 15px;">' + formatHasSlotsSentence(slots.length) + '</p>' +
                          slotsHtml +
                          '</div>',
                    icon: 'info',
                    iconColor: '#198754',
                    showConfirmButton: false,
                    showCancelButton: true,
                    cancelButtonText: calendarI18n.close,
                    cancelButtonColor: '#6c757d',
                    width: '500px',
                    padding: '2rem',
                    customClass: {
                        popup: 'slot-modal-popup',
                        title: 'slot-modal-title',
                        htmlContainer: 'slot-modal-content'
                    },
                    buttonsStyling: true,
                    reverseButtons: false
                });
            }
        });

        calendar.render();

        // Add custom CSS for dates with slots
        const style = document.createElement('style');
        style.textContent = `
            .fc-day.has-slots {
                background-color: #198754 !important;
            }
            .fc-daygrid-day.has-slots .fc-daygrid-day-number {
                color: #ffffff !important;
                font-weight: bold !important;
            }
            .fc-day:hover {
                cursor: pointer;
                opacity: 0.8;
            }
        `;
        document.head.appendChild(style);
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
    
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .fc-day:hover {
        background-color: rgba(25, 135, 84, 0.1) !important;
        transition: background-color 0.2s ease;
    }
    
    /* SweetAlert2 Custom Styling */
    .slot-modal-popup {
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    .slot-modal-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .slot-modal-content {
        font-size: 15px;
        line-height: 1.6;
    }
    .swal2-confirm {
        border-radius: 6px;
        padding: 10px 24px;
        font-weight: 500;
    }
    .swal2-cancel {
        border-radius: 6px;
        padding: 10px 24px;
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
    }
</style>

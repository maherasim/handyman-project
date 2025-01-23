<x-master-layout>
    <div class="container-fluid">
        @include('partials._provider')
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">
                                {{ $providerdata->first_name . ' ' . $providerdata->last_name }} {{ $pageTitle }}
                            </h5>
                            <a href="{{ route('provider.time-slot', ['id' => $provider_id]) }}" class="float-end btn btn-sm btn-primary">
                                <i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('providerslot.store'))->attribute('data-toggle', 'validator')->id('provider-form')->open() }}
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" name="id" id="provider-id" value="{{ $provider_id }}">
                                <div class="form-group has-feedback">
                                    {{ html()->label(__('messages.day').' <span class="text-danger">*</span>', 'Day')->class('form-control-label col-md-12') }}
                                </div>
                                <div class="form-group has-feedback">
                                    <!-- Calendar Container -->
                                    <div id="calendar"></div>
                                </div>
                                <div class="form-group has-feedback mt-4">
                                    <div class="col-md-12">
                                        {{ html()->label(__('messages.time').' <span class="text-danger">*</span>', 'Time')->class('form-control-label col-md-12') }}
                                        <div class="tab-content" id="pills-tabContent-1">
                                            @foreach ($slotsArray['days'] as $day)
                                                @if (isset($day))
                                                    <div class="tab-pane p-1 day-slot @if(strtolower($day) === strtolower($activeDay)) active @endif" id="{{ $day }}">
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @for ($hour = 0; $hour < 24; $hour++)
                                                                @php
                                                                    $slotTime = sprintf('%02d:00', $hour);
                                                                    $isActive = in_array($slotTime, $activeSlots[$day] ?? []);
                                                                @endphp
                                                                <span class="badge bg-primary p-2 time-link @if ($isActive) active @endif slot-link" data-day="{{ $day }}" data-slot="{{ $slotTime }}">
                                                                    {{ $slotTime }}
                                                                </span>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                {{ html()->submit(__('messages.submit'))->class('btn btn-md btn-primary') }}
                            </div>
                        </div>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize FullCalendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap'],
                themeSystem: 'bootstrap',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 600,
                selectable: true,
                editable: false,
                events: [
                    @foreach ($slotsArray['days'] as $day)
                        @if (isset($day) && isset($activeSlots[$day]))
                            @foreach ($activeSlots[$day] as $slot)
                                {
                                    title: "{{ ucfirst($day) }} Slot",
                                    start: "{{ $day . 'T' . $slot }}",
                                    color: 'rgb(19, 193, 240)',
                                    textColor: '#fff'
                                },
                            @endforeach
                        @endif
                    @endforeach
                ],
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    var day = info.event.startStr.split('T')[0];
                    var slot = info.event.startStr.split('T')[1];
                    alert(`Day: ${day}, Slot: ${slot}`);
                }
            });

            calendar.render();

            // Handle time slot toggling
            $('.time-link').on('click', function () {
                $(this).toggleClass('active');
            });

            // Handle form submission
            $('#provider-form').on('submit', function (e) {
                e.preventDefault();
                var selectedSlotsByDay = {};

                $('.slot-link.active').each(function () {
                    var day = $(this).data('day');
                    var slot = $(this).data('slot');
                    if (!selectedSlotsByDay[day]) {
                        selectedSlotsByDay[day] = [];
                    }
                    selectedSlotsByDay[day].push(slot);
                });

                var providerId = $('#provider-id').val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    type: 'POST',
                    url: '{{ route("providerslot.store") }}',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        provider_id: providerId,
                        slots: selectedSlotsByDay
                    },
                    success: function (response) {
                        alert(response.message);
                    },
                    error: function (error) {
                        console.error(error);
                        alert('Error saving slots');
                    }
                });
            });
        });
    </script>
    @endsection
</x-master-layout>

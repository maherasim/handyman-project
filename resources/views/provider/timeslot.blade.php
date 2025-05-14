<x-master-layout>
    <div class="container-fluid">
        @include('partials._provider')

        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="mb-2">{{ __('messages.update') }} {{ $pageTitle }}</h4>
                        <p class="mb-0 fw-bold">
                            {{ $providerdata->first_name . ' ' . $providerdata->last_name }}
                        </p>
                        <a class="btn btn-sm btn-outline-primary mt-3" href="{{ route('provider.edit-time-slot', ['id' => $provider_id]) }}">
                            {{ __('messages.slot') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', '#')->attribute('data-toggle', 'validator')->id('provider')->open() }}
                            {{ html()->hidden('id', $provider_id) }}

                            <!-- Calendar -->
                            <div id="calendar"></div>

                            <!-- Time Slot Display -->
                            <div class="form-group has-feedback mt-4">
                                <label class="form-label">{{ __('messages.time') }} <span class="text-danger">*</span></label>
                                <div class="tab-content">
                                    @foreach ($slotsArray as $slotDay)
                                        @if (isset($slotDay['day']) && isset($slotDay['slot']))
                                            <div class="tab-pane p-1 day-slot @if (strtolower($slotDay['day']) === strtolower($activeDay)) active @endif" id="{{ $slotDay['day'] }}">
                                                @if ($slotDay['slot'])
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach ($slotDay['slot'] as $slot)
                                                            <span class="badge bg-primary p-2">{{ sprintf('%02d:00', $slot) }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div>
                                                        <span>No time slots selected for {{ $slotDay['day'] }}.</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">{{ __('messages.submit') }}</button>
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
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap'],
                themeSystem: 'bootstrap',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay'
                },
                height: 600,
                selectable: true,
                editable: false,
                events: [
                    @foreach ($slotsArray as $slotDay)
                        @if (isset($slotDay['slot']))
                            @foreach ($slotDay['slot'] as $slot)
                                {
                                    title: "{{ ucfirst($slotDay['day']) }} Slot",
                                    startRecur: "{{ now()->startOfWeek()->format('Y-m-d') }}",
                                    daysOfWeek: [{{ ['sun'=>0,'mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6][strtolower($slotDay['day'])] }}],
                                    startTime: "{{ sprintf('%02d:00', (int)$slot) }}",
                                    endTime: "{{ sprintf('%02d:00', (int)($slot+1)) }}",
                                    rendering: 'background',
                                    backgroundColor: 'rgb(19, 193, 240)',
                                    textColor: '#fff'
                                },
                            @endforeach
                        @endif
                    @endforeach
                ]
            });

            calendar.render();
        });
    </script>
    @endsection
</x-master-layout>

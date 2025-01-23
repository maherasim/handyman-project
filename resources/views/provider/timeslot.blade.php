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
                                <div class="form-group has-feedback">
                                    <a class="me-2 float-end btn btn-sm btn-primary" href="{{ route('provider.edit-time-slot', ['id' => $provider_id]) }}" title="{{ __('messages.slot') }}">{{ __('messages.update') }}</a>
                                    {{ html()->label(__('messages.day') . ' <span class="text-danger">*</span>', 'Day')->class('form-control-label col-md-12') }}
                                    
                                    <!-- Day Calendar Design -->
                                     
                                </div>

                                <div class="form-group has-feedback mt-4">
                                    <div class="col-md-12">
                                        {{ html()->label(__('messages.time') . ' <span class="text-danger">*</span>', 'Time')->class('form-control-label col-md-12') }}
                                        {{-- <div class="tab-content" id="pills-tabContent-1">
                                            @foreach ($slotsArray as $slotDay)
                                                @if (isset($slotDay['day']) && isset($slotDay['slot']))
                                                    <div class="tab-pane p-1 day-slot @if (strtolower($slotDay['day']) === strtolower($activeDay)) active @endif" id="{{ $slotDay['day'] }}">
                                                        @if ($slotDay['slot'])
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @foreach ($slotDay['slot'] as $slot)
                                                                    @php
                                                                        $slot = sprintf('%02d:00', $slot);
                                                                    @endphp
                                                                    <span class="badge bg-primary p-2">{{ $slot }}</span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div>
                                                                <span>No time slots selected for {{ $slotDay['day'] }} day.</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            setActiveDay('mon'); // Default active day

            $('.day-link').on('click', function (e) {
                e.preventDefault();
                var selectedDay = $(this).data('day');
                setActiveDay(selectedDay);
                showActiveDaySlots();
            });

            function setActiveDay(day) {
                $('.day-slot').removeClass('active');
                $('.day-link').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#btn-' + day).removeClass('btn-outline-primary').addClass('btn-primary');
                $('.day-slot#' + day).addClass('active');
                activeDay = day;
            }

            function showActiveDaySlots() {
                $('.day-slot').hide();
                $('.day-slot.active').show();
            }
        });
    </script>
</x-master-layout>

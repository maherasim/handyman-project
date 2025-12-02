<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card rounded-3 border-0 shadow-sm">
                    <div class="card-body p-3 p-md-4">
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page end  -->
    </div>
    @section('bottom_script')
        <script>
            if (jQuery('#calendar').length) {
                document.addEventListener('DOMContentLoaded', function() {
                    var calendarEl = document.getElementById('calendar');

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        plugins: ['dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap'],
                        initialView: 'dayGridMonth',
                        displayEventTime: true,
                        themeSystem: 'bootstrap',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                        },
                        height: 600,
                        selectable: true,
                        selectHelper: true,
                        editable: false,
                        eventLimit: false,
                        showNonCurrentDates: false,
                        droppable: false,

                        eventSources: [{
                            events: function(info, successCallback, failureCallback) {
                                $.ajax({
                                    url: "{{ route('home') }}",
                                    dataType: 'json',
                                    data: {
                                        start: info.startStr,
                                        end: info.endStr,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function(response) {
                                        successCallback(
                                        response); // response is an array of event objects with color
                                    },
                                    error: function(xhr) {
                                        console.error("Error loading events:", xhr);
                                        failureCallback(xhr);
                                    }
                                });
                            }
                        }],

                        eventClick: function(info) {
                            var id = info.event.id;
                            var url = "{{ url('booking') }}/" + id;
                            window.location.href = url;
                        }
                    });

                    calendar.render();
                });
            }
        </script>
    @endsection

</x-master-layout>

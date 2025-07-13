<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
              <div class="card">
                <div class="card-body">
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
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ['dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap'],
      defaultView: 'dayGridMonth',
      displayEventTime: true,
      themeSystem: 'bootstrap',
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        clear: ''
      },
      height: 600,
      selectable: true,
      selectHelper: true,
      editable: false,
      eventLimit: false,
      showNonCurrentDates: false,
      droppable: false,

      eventSources: [{
        events: function (info, successCallback, failureCallback) {
          $.ajax({
            url: "{{ route('home') }}",
            dataType: 'JSON',
            data: {
              start: info.startStr,
              end: info.endStr,
              _token: "{{ csrf_token() }}"
            },
            success: function (response) {
              const events = [];

              response.forEach(eventData => {
                if (eventData.service_slots && eventData.service_slots.length > 0) {
                  eventData.service_slots.forEach(slot => {
                    events.push({
                      id: eventData.id,
                      title: eventData.service?.name || 'No Service Name',
                      start: slot.date,
                      allDay: true,
                    });
                  });
                }
              });

              successCallback(events);
            },
            error: function (data) {
              failureCallback(data);
            }
          });
        },
        color: "rgb(19, 193, 240)",
        textColor: "#fff",
      }],

      eventClick: function (info) {
        var id = info.event.id;
        var url = "{{ URL::to('booking') }}/" + id;
        window.location.replace(url);
      },

    });
    calendar.render();
  });
}
</script>
 

    @endsection
</x-master-layout>


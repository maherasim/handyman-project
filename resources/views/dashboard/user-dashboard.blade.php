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
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: ['dayGrid', 'timeGrid', 'list', 'interaction', 'bootstrap'],
      defaultView: 'dayGridMonth',
      displayEventTime: true,
      themeSystem: 'bootstrap',
      header: {
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
            url: "{{ route('home') }}", // or your correct route
            dataType: 'json',
            data: {
              start: info.startStr,
              end: info.endStr,
              _token: "{{ csrf_token() }}"
            },
            success: function(response) {
              successCallback(response); // response must be an array of {title, start, id}
            },
            error: function(xhr) {
              console.error("Error loading events:", xhr);
              failureCallback(xhr);
            }
          });
        },
        color: "rgb(19, 193, 240)",
        textColor: "#fff"
      }],

      eventRender: function(info) {
        if (info.event.allDay === 'true') {
          info.event.allDay = true;
        } else {
          info.event.allDay = false;
        }
      },

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


  <!-- Backend bundle (jQuery, Bootstrap, Select2, etc.) -->
   <script src="{{ asset('js/backend-bundle.min.js')}}"></script>
  @include('helper.app_message')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        if (window.bootstrap && bootstrap.Tooltip) {
          new bootstrap.Tooltip(tooltipTriggerEl)
        }
      })
    });
  </script>
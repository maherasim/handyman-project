  <!-- Backend bundle (jQuery, Bootstrap, Select2, etc.) -->
   <script src="{{ asset('js/backend-bundle.min.js')}}"></script>
  <!-- app JavaScript (page-specific) -->
   <script src="{{ asset('js/app.js')}}"></script>
  @include('helper.app_message')
  <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
  <script>
    (function(){
      var desc = document.getElementById('description');
      var descEditor = document.getElementById('description_editor');
      if (desc && descEditor && window.Quill){
        var q1 = new Quill('#description_editor', { theme: 'snow', modules: { toolbar: [['bold','italic','underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link']] } });
        q1.root.innerHTML = desc.value || descEditor.innerHTML || '';
        q1.on('text-change', function(){ desc.value = q1.root.innerHTML; });
      }

      var can = document.getElementById('cancellation_policy');
      var canEditor = document.getElementById('cancellation_policy_editor');
      if (can && canEditor && window.Quill){
        var q2 = new Quill('#cancellation_policy_editor', { theme: 'snow', modules: { toolbar: [['bold','italic','underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link']] } });
        q2.root.innerHTML = can.value || canEditor.innerHTML || '';
        q2.on('text-change', function(){ can.value = q2.root.innerHTML; });
      }
    })();
  </script>
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
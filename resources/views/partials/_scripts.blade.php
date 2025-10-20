<!-- Backend Bundle JavaScript -->
<script src="{{ asset('js/backend-bundle.min.js') }}"></script>

<!-- TinyMCE -->
<script src="{{ asset('vendor/tinymce/js/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('vendor/tinymce/js/tinymce/jquery.tinymce.min.js') }}"></script>

<!-- Drag & Drop -->
<link href="{{ asset('css/dragula.css') }}" rel="stylesheet">
<script src="{{ asset('js/dragula.min.js') }}"></script>

<!-- Swiper -->
<script src="{{ asset('js/swiper-bundle.min.js') }}"></script>

<script>
@if(!empty($assets) && (in_array('tinymce', $assets) || true))
    // TinyMCE Editor
    function tinymceEditor(target = '.textarea', button = '', callback = null, height = 200) {
        var rtl = $("html[lang=ar]").attr('dir');
        tinymce.init({
            selector: target,
            directionality: rtl,
            height: height,
            skin: 'oxide-dark',
            relative_urls: false,
            remove_script_host: false,
            content_css: [
                'https://fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                'https://www.tinymce.com/css/codepen.min.css'
            ],
            image_advtab: true,
            menubar: false,
            plugins: [
                "textcolor colorpicker image imagetools media charmap link print textcolor code codesample table"
            ],
            toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify | numlist bullist | removeformat | code | image |' + button,
            image_title: true,
            automatic_uploads: true,
            convert_urls: false,
            file_picker_types: 'image',
            setup: callback,
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    reader.onload = function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        cb(blobInfo.blobUri(), { title: file.name });
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            }
        });
    }
@endif

// Show/hide elements based on checkbox
function showCheckLimitData(id) {
    if ($('#' + id).is(":checked")) {
        $('.' + id).removeClass('d-none');
    } else {
        $('.' + id).addClass('d-none');
    }
}

// Modal form validation
function validateModal(modalId) {
    var isValid = true;
    $('#' + modalId + ' input[required], #' + modalId + ' textarea[required]').next('small.help-block').text('');
    $('#' + modalId + ' input[required], #' + modalId + ' textarea[required]').each(function() {
        if ($(this).val().trim() === '') {
            $(this).next('small.help-block').text('This field is required');
            isValid = false;
        }
    });
    if (!isValid) return false;
    $('#' + modalId).modal('hide');
}
</script>

@yield('bottom_script')

<!-- Other Vendor JS -->
<script src="{{ asset('vendor/magnific-popup/jquery.magnific-popup.min.js') }}" defer></script>
<script src="{{ asset('js/flex-tree.min.js') }}" defer></script>
<script src="{{ asset('js/tree.js') }}" defer></script>
<script src="{{ asset('js/sweetalert.js') }}"></script>
<script src="{{ asset('js/vector-map-custom.js') }}"></script>
<script src="{{ asset('js/customizer.js') }}"></script>
<script src="{{ asset('vendor/confirmJs/confirm.min.js') }}"></script>
<script src="{{ asset('vendor/vanillajs-datepicker/dist/js/datepicker-full.js') }}"></script>
<script src="{{ asset('js/charts/progressbar.js') }}"></script>
<script src="{{ asset('js/chart-custom.js') }}"></script>
<script src="{{ asset('js/charts/01.js') }}"></script>
<script src="{{ asset('js/charts/02.js') }}"></script>
<script src="{{ asset('vendor/emoji-picker-element/index.js') }}" type="module"></script>

@if(isset($assets) && (in_array('datatable',$assets) || in_array('datatable_builder',$assets)))
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.select.min.js') }}"></script>
@endif

<!-- FullCalendar -->
<script src="{{ asset('vendor/fullcalendar/core/main.js') }}"></script>
<script src="{{ asset('vendor/fullcalendar/interaction/main.js') }}"></script>
<script src="{{ asset('vendor/fullcalendar/daygrid/main.js') }}"></script>
<script src="{{ asset('vendor/fullcalendar/timegrid/main.js') }}"></script>
<script src="{{ asset('vendor/fullcalendar/list/main.js') }}"></script>
<script src="{{ asset('vendor/fullcalendar/bootstrap/main.js') }}"></script>

<!-- App JS -->
<script src="{{ asset('js/app.js') }}"></script>

@include('helper.app_message')

<script>
// Global SweetAlert handler for cash approval links
$(document).on('click', 'a[data-approve-cash="1"]', function(e){
    e.preventDefault();
    var href = $(this).attr('href');
    var redirect = () => window.location.href = href;

    if (typeof Swal !== 'undefined' && Swal.fire) {
        Swal.fire({
            title: 'Approve Cash Payment?',
            text: 'This will mark the payment as approved and update related records.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve',
            cancelButtonText: 'Cancel'
        }).then(res => { if(res.isConfirmed) redirect(); });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: 'Approve Cash Payment?',
            text: 'This will mark the payment as approved and update related records.',
            icon: 'warning',
            buttons: ['Cancel', 'Yes, approve']
        }).then(willApprove => { if(willApprove) redirect(); });
    } else {
        if (confirm('Approve Cash Payment?')) redirect();
    }
});
</script>

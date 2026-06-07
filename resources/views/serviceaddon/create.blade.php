<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>

                            <a href="{{ route('serviceaddon.index') }}" class=" float-end btn btn-sm btn-primary"><i
                                    class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('serviceaddon.store'))->attribute('enctype', 'multipart/form-data')->attribute('data-toggle', 'validator')->id('serviceaddon')->open() }}
                        {{ html()->hidden('id', $serviceaddon->id ?? null) }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.name') . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                {{ html()->text('name', $serviceaddon->name)->placeholder(__('messages.name'))->class('form-control')->required()->attribute('title', __('messages.name_alphabetic_title')) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.select_name', ['select' => __('messages.service')]) . ' <span class="text-danger">*</span>', 'name')->class('form-control-label') }}
                                <br />
                                @php
                                    if ($auth_user->user_type == 'admin' || $auth_user->user_type == 'demo_admin') {
                                        $route = route('ajax-list', ['type' => 'service-list']);
                                    } else {
                                        $route = route('ajax-list', [
                                            'type' => 'service-list',
                                            'provider_id' => $auth_user->id,
                                        ]);
                                    }

                                @endphp
                                {{ html()->select(
                                        'service_id',
                                        [optional($serviceaddon->service)->id => optional($serviceaddon->service)->name],
                                        optional($serviceaddon->service)->id,
                                    )->class('select2js form-group service')->id('service_id')->required()->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.service')]))->attribute('data-ajax--url', $route) }}

                            </div>
                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.price') . ' <span class="text-danger">*</span>', 'price')->class('form-control-label') }}
                                {{ html()->text('price', $serviceaddon->price)->placeholder(__('messages.price'))->class('form-control')->required()->attribute('pattern', '^\\d+(\\.\\d{1,2})?$') }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ html()->label(__('messages.serviceaddon_status_label') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], $serviceaddon->status)->class('form-control select2js')->required() }}
                            </div>

                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="serviceaddon_image">{{ __('messages.image') }}
                                    <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" name="serviceaddon_image" class="custom-file-input"
                                        onchange="previewImage(event)" accept="image/*" required>
                                    @if ($serviceaddon && getMediaFileExit($serviceaddon, 'serviceaddon_image'))
                                        <label
                                            class="custom-file-label upload-label">{{ $serviceaddon->getFirstMedia('serviceaddon_image')->file_name }}</label>
                                    @else
                                        <label
                                            class="custom-file-label upload-label">{{ __('messages.choose_file', ['file' => __('messages.image')]) }}</label>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-2 mb-2">
                                <div class="image-preview-container">
                                    <img id="serviceaddon_image_preview"
                                        src="{{ getMediaFileExit($serviceaddon, 'serviceaddon_image') ? getSingleMedia($serviceaddon, 'serviceaddon_image') : '' }}"
                                        alt="{{ __('messages.image_preview_alt') }}" class="attachment-image mt-1"
                                        style="width: 150px; {{ getMediaFileExit($serviceaddon, 'serviceaddon_image') ? '' : 'display: none;' }}">
                                    <a class="text-danger remove-file" id="removeButton"
                                        onclick="removeImage(event, '{{ route('remove.file', ['id' => $serviceaddon->id, 'type' => 'serviceaddon_image']) }}')"
                                        style="{{ getMediaFileExit($serviceaddon, 'serviceaddon_image') ? 'display: inline;' : 'display: none;' }}">
                                        <i class="ri-close-circle-line"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-end')->id('saveButton') }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('bottom_script')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script type="text/javascript">
            const MAX_ORIGINAL_IMAGE_BYTES = 4 * 1024 * 1024;
            const MAX_UPLOAD_IMAGE_BYTES = 120 * 1024;
            const MAX_UPLOAD_IMAGE_SIDE = 800;

            function formatBytes(bytes) {
                if (!bytes) {
                    return '0 KB';
                }

                return bytes >= 1024 * 1024
                    ? (bytes / (1024 * 1024)).toFixed(2) + ' MB'
                    : Math.ceil(bytes / 1024) + ' KB';
            }

            function loadImageFromFile(file) {
                return new Promise((resolve, reject) => {
                    const image = new Image();
                    image.onload = function() {
                        URL.revokeObjectURL(image.src);
                        resolve(image);
                    };
                    image.onerror = reject;
                    image.src = URL.createObjectURL(file);
                });
            }

            function canvasToBlob(canvas, quality) {
                return new Promise((resolve) => {
                    canvas.toBlob(resolve, 'image/jpeg', quality);
                });
            }

            async function compressImage(file) {
                if (!file || !file.type || !file.type.startsWith('image/')) {
                    return file;
                }

                const image = await loadImageFromFile(file);
                const scale = Math.min(1, MAX_UPLOAD_IMAGE_SIDE / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(image.width * scale));
                canvas.height = Math.max(1, Math.round(image.height * scale));

                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                let compressedBlob = null;
                for (const quality of [0.62, 0.52, 0.44, 0.36]) {
                    compressedBlob = await canvasToBlob(canvas, quality);
                    if (compressedBlob && compressedBlob.size <= MAX_UPLOAD_IMAGE_BYTES) {
                        break;
                    }
                }

                if (!compressedBlob || compressedBlob.size >= file.size) {
                    return file;
                }

                const fileName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                return new File([compressedBlob], fileName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
            }

            function replaceSelectedFile(input, file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
            }

            function previewImage(event) {
                const preview = document.getElementById('serviceaddon_image_preview');
                const fileLabel = document.querySelector('.custom-file-label');
                const saveButton = document.getElementById('saveButton');
                const removeButton = document.getElementById('removeButton');
                const input = event.target;
                const selectedFile = input.files[0];

                if (!selectedFile) {
                    return;
                }

                saveButton.disabled = true;
                saveButton.value = 'Preparing image...';
                fileLabel.textContent = 'Preparing image...';

                if (selectedFile.size > MAX_ORIGINAL_IMAGE_BYTES) {
                    input.value = '';
                    preview.src = '';
                    preview.style.display = 'none';
                    fileLabel.textContent = '{{ __('messages.choose_file', ['file' => __('messages.image')]) }}';
                    removeButton.style.display = 'none';
                    saveButton.disabled = true;
                    saveButton.value = '{{ __('messages.save') }}';
                    Swal.fire('Upload error', 'Image must be 4 MB or smaller.', 'error');
                    return;
                }

                compressImage(selectedFile).then(function(file) {
                    if (file !== selectedFile) {
                        replaceSelectedFile(input, file);
                    }

                    preview.src = URL.createObjectURL(file);
                    preview.style.display = 'block'; // Show the image
                    fileLabel.textContent = file.name; // Update label with the file name

                    // Show the remove button and enable the save button
                    removeButton.style.display = 'inline';
                    saveButton.disabled = false;
                    saveButton.value = '{{ __('messages.save') }}';
                }).catch(function(error) {
                    console.error('Image optimization failed:', error);
                    input.value = '';
                    preview.src = '';
                    preview.style.display = 'none';
                    fileLabel.textContent = '{{ __('messages.choose_file', ['file' => __('messages.image')]) }}';
                    removeButton.style.display = 'none';
                    saveButton.disabled = true;
                    saveButton.value = '{{ __('messages.save') }}';
                    Swal.fire('Upload error', 'Please choose a valid JPG, PNG, or WebP image.', 'error');
                });
            }

            function removeImage(event, removeUrl) {
                event.preventDefault(); // Prevent default link behavior

                // SweetAlert confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to remove the serviceaddon image?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const preview = document.getElementById('serviceaddon_image_preview');
                        const fileLabel = document.querySelector('.custom-file-label');
                        const saveButton = document.getElementById('saveButton');
                        const removeButton = document.getElementById('removeButton');

                        // AJAX request to remove the media file
                        $.ajax({
                            url: removeUrl,
                            type: 'POST',
                            success: function(result) {
                                // Handle success
                                preview.src = '';
                                preview.style.display = 'none';
                                document.querySelector('input[name="serviceaddon_image"]').value =
                                ''; // Clear the file input
                                fileLabel.textContent =
                                    '{{ __('messages.choose_file', ['file' => __('messages.image')]) }}'; // Reset the label text
                                saveButton.disabled = true; // Disable the save button
                                removeButton.style.display = 'none'; // Hide the remove button

                                // Optionally show a success message
                                Swal.fire(
                                    '{{ __("messages.deleted") }}',
                                    '{{ __("messages.serviceaddon_image_removed") }}',
                                    'success'
                                );
                            },
                            error: function(xhr, status, error) {
                                console.error('Error removing media file:', error);
                            }
                        });
                    }
                });
            }

            function removeLocalImage() {
                const preview = document.getElementById('serviceaddon_image_preview');
                const fileLabel = document.querySelector('.custom-file-label');
                const saveButton = document.getElementById('saveButton');
                const removeButton = document.getElementById('removeButton');

                // Check if the image exists before removing
                if (preview.src) {
                    preview.src = '';
                    preview.style.display = 'none';
                    document.querySelector('input[name="serviceaddon_image"]').value = ''; // Clear the file input
                    fileLabel.textContent =
                    '{{ __('messages.choose_file', ['file' => __('messages.image')]) }}'; // Reset the label text
                    saveButton.disabled = true; // Disable the save button
                    removeButton.style.display = 'none'; // Hide the remove button
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                checkImage();
            });

            function checkImage() {
                var id = @json($serviceaddon->id);
                var route = "{{ route('check-image', ':id') }}";
                route = route.replace(':id', id);
                var type = 'serviceaddon';

                $.ajax({
                    url: route,
                    type: 'GET',
                    data: {
                        type: type,
                    },
                    success: function(result) {
                        var attachments = result.results;
                        var attachmentsCount = Object.keys(attachments).length;
                        if (attachmentsCount == 0) {
                            $('input[name="serviceaddon_image"]').attr('required', 'required');
                            document.getElementById('saveButton').disabled = true; // Disable button initially
                        } else {
                            $('input[name="serviceaddon_image"]').removeAttr('required');
                            document.getElementById('saveButton').disabled = false; // Enable if there's an image
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            }
        </script>
    @endsection
</x-master-layout>

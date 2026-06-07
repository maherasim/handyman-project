<x-master-layout>
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                                <a href="{{ route('helpdesk.index') }}" class=" float-end btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('helpdesk.store'))
                            ->attribute('enctype', 'multipart/form-data')
                            ->attribute('data-toggle', 'validator')
                            ->id('helpdesk-form')
                            ->open()
                        }}
                        {{ html()->hidden('id', $helpdesk->id ?? null) }}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    
                                    {{ html()->label(__('messages.subject') . ' <span class="text-danger">*</span>', 'subject')->class('form-control-label') }}
                                    {{ html()->text('subject', $helpdesk->subject)->placeholder(__('messages.subject'))->class('form-control')->attributes(['title' => 'Please enter alphabetic characters and spaces only'])}}
                                    
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.select_name',[ 'select' => __('messages.users') ]) . ' <span class="text-danger">*</span>', 'subject')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select('employee_id', [ optional($helpdesk->users)->id => optional($helpdesk->users)->display_name], optional($helpdesk->users)->id)
                                        ->class('select2js form-group')
                                        ->id('employee_id')
                                        ->required()
                                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.users')]))
                                        ->attribute('data-ajax--url', route('ajax-list', ['type' => 'provider-user-handyman']))
                                    }}
                                    
                                </div>
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.mode') . ' <span class="text-danger">*</span>', 'mode')->class('form-control-label') }}
                                    {{ html()->select('mode',['email' => __('messages.email'),'phone' => __('messages.phone'),'other' => __('messages.other')], $helpdesk->mode)->class('form-control select2js')->required()->id('mode')}}
                                </div>
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.email') , 'email')->class('form-control-label') }}
                                    {{ html()->text('email',null)->attributes(['pattern' => '[^@]+@[^@]+\.[a-zA-Z]{2,}'])->placeholder(__('messages.email'))->class('form-control')->id('email')}}
                                
                                </div>
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.contact_number') , 'contact_number')->class('form-control-label') }}
                                    {{ html()->text('contact_number',null)->placeholder(__('messages.contact_number'))->class('form-control contact_number')->id('contact_number')}}
                                
                                    <small class="help-block with-errors text-danger " id="contact_number_err"></small>
                                </div>
                                @endif
                                <div class="form-group col-md-4">
                                    <label class="form-control-label" for="helpdesk_attachment">{{ __('messages.image') }}
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" id="helpdesk_attachment_input" onchange="prepareHelpdeskAttachments(this)" name="helpdesk_attachment[]" class="custom-file-input"
                                            data-file-error="{{ __('messages.files_not_allowed') }}" accept="image/*" multiple>
                                        <label
                                            class="custom-file-label upload-label">{{ __('messages.choose_file',['file' =>  __('messages.attachments') ]) }}</label>
                                    </div>
                                </div>
                               
                                
                            </div>
                            <div class="row helpdesk_attachment_div">
                            <div class="col-md-12">


                                <div class="row" id="new_helpdesk_attachment_previews"></div>

                                @if(getMediaFileExit($helpdesk, 'helpdesk_attachment'))
                                @php

                                $attchments = $helpdesk->getMedia('helpdesk_attachment');

                                $file_extention = config('constant.IMAGE_EXTENTIONS');
                                @endphp
                                <div class="border-start">
                                    <p class="ms-2"><b>{{ __('messages.attached_files') }}</b></p>
                                    <div class="ms-2 my-3">
                                        <div class="row">
                                            @foreach($attchments as $attchment )
                                            <?php
                                            $extention = in_array(strtolower(imageExtention($attchment->getFullUrl())), $file_extention);
                                            ?>

                                            <div class="col-md-2 pe-10 text-center galary file-gallary-{{$helpdesk->id}} position-relative"
                                                data-gallery=".file-gallary-{{$helpdesk->id}}"
                                                id="helpdesk_attachment_preview_{{$attchment->id}}">
                                                @if($extention)
                                                <a id="attachment_files" href="{{ $attchment->getFullUrl() }}"
                                                    class="list-group-item-action attachment-list" target="_blank">
                                                    <img src="{{ $attchment->getFullUrl() }}" class="attachment-image"
                                                        alt="">
                                                </a>
                                                @else
                                                <a id="attachment_files"
                                                    class="video list-group-item-action attachment-list"
                                                    href="{{ $attchment->getFullUrl() }}">
                                                    <img src="{{ asset('images/file.png') }}" class="attachment-file">
                                                </a>
                                                @endif
                                                <a class="text-danger remove-file"
                                                    href="{{ route('remove.file', ['id' => $attchment->id, 'type' => 'helpdesk_attachment']) }}"
                                                    data--submit="confirm_form" data--confirmation='true'
                                                    data--ajax="true" data-toggle="tooltip"
                                                    title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}'
                                                    data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}'
                                                    data-message='{{ __("messages.remove_file_msg") }}'>
                                                    <i class="ri-close-circle-line"></i>
                                                </a>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                        <div class="form-group col-md-12">
                           {{ html()->label(trans('messages.description'). ' <span class="text-danger">*</span>', 'description')->class('form-control-label') }}
                           {{ html()->textarea('description', $helpdesk->description)->class('form-control textarea')->required()->rows(3)->placeholder(__('messages.description')) }}
                        </div>
                                
                        </div>
                            
                            {{ html()->submit( __('messages.save'))->class('btn btn-md btn-primary float-end')->id('helpdesk-submit-button')->attribute('data-default-text', __('messages.save')) }}
                            {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>
<script>
        async function prepareHelpdeskAttachments(input) {
            const previewRow = document.getElementById('new_helpdesk_attachment_previews');
            if (previewRow) previewRow.innerHTML = '';
            setHelpdeskAttachmentsPreparing(true);

            try {
                if (!input.files || !input.files.length) return;

                const dataTransfer = new DataTransfer();
                const files = Array.from(input.files);

                for (const file of files) {
                    const preparedFile = await prepareHelpdeskImageForUpload(file);
                    dataTransfer.items.add(preparedFile);

                    if (previewRow && preparedFile.type && preparedFile.type.startsWith('image/')) {
                        const objectUrl = URL.createObjectURL(preparedFile);
                        const col = document.createElement('div');
                        col.className = 'col-md-2 text-center';
                        col.innerHTML = '<img src="' + objectUrl + '" class="attachment-image" alt="">';
                        previewRow.appendChild(col);

                        col.querySelector('img').addEventListener('load', function() {
                            URL.revokeObjectURL(objectUrl);
                        }, { once: true });
                    }
                }

                input.files = dataTransfer.files;
                $('.upload-label').text(files.length > 1 ? files.length + ' files selected' : input.files[0].name);
            } finally {
                setHelpdeskAttachmentsPreparing(false);
            }
        }

        function setHelpdeskAttachmentsPreparing(isPreparing) {
            window.helpdeskAttachmentsPreparing = isPreparing;

            const submitButton = document.getElementById('helpdesk-submit-button');
            if (!submitButton) return;

            if (!submitButton.dataset.defaultText) {
                submitButton.dataset.defaultText = submitButton.value || submitButton.textContent || 'Save';
            }

            submitButton.disabled = isPreparing;

            if (submitButton.tagName === 'INPUT') {
                submitButton.value = isPreparing ? 'Preparing images...' : submitButton.dataset.defaultText;
            } else {
                submitButton.textContent = isPreparing ? 'Preparing images...' : submitButton.dataset.defaultText;
            }
        }

        function prepareHelpdeskImageForUpload(file) {
            const maxSize = 450 * 1024;
            const maxDimension = 1200;

            if (!file.type || !file.type.startsWith('image/')) {
                return Promise.resolve(file);
            }

            return new Promise(function(resolve) {
                const image = new Image();
                const objectUrl = URL.createObjectURL(file);

                image.onload = function() {
                    URL.revokeObjectURL(objectUrl);

                    if (file.size <= maxSize && Math.max(image.width, image.height) <= maxDimension) {
                        resolve(file);
                        return;
                    }

                    const ratio = Math.min(maxDimension / image.width, maxDimension / image.height, 1);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.width * ratio));
                    canvas.height = Math.max(1, Math.round(image.height * ratio));

                    const context = canvas.getContext('2d');
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    compressHelpdeskImage(canvas, file, [0.78, 0.68, 0.58], maxSize, resolve);
                };

                image.onerror = function() {
                    URL.revokeObjectURL(objectUrl);
                    resolve(file);
                };

                image.src = objectUrl;
            });
        }

        function compressHelpdeskImage(canvas, originalFile, qualities, maxSize, resolve) {
            const quality = qualities.shift() || 0.58;

            canvas.toBlob(function(blob) {
                if (!blob) {
                    resolve(originalFile);
                    return;
                }

                if (blob.size > maxSize && qualities.length) {
                    compressHelpdeskImage(canvas, originalFile, qualities, maxSize, resolve);
                    return;
                }

                const safeName = originalFile.name.replace(/\.[^.]+$/, '') + '.jpg';
                resolve(new File([blob], safeName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                }));
            }, 'image/jpeg', quality);
        }

        $(document).on('submit', '#helpdesk-form', function() {
            if (window.helpdeskAttachmentsPreparing) {
                Snackbar.show({
                    text: 'Please wait, images are preparing for upload.',
                    pos: 'bottom-center',
                    backgroundColor: '#d32f2f',
                    actionTextColor: '#fff'
                });
                return false;
            }
        });
        $(document).ready(function() {
    $(document).on('keyup', '.contact_number', function() {
        var contactNumberInput = document.getElementById('contact_number');
        var inputValue = contactNumberInput.value;

        // Remove any characters that aren't digits, +, -, or space
        var cleanedInput = inputValue.replace(/[^0-9+\- ]/g, '');
        
        // Set the cleaned input value back to the input field
        contactNumberInput.value = cleanedInput;
        // Check if input exceeds 15 characters
        if (cleanedInput.length > 15) {
            $('#contact_number_err').text('Contact number should not exceed 15 characters');
        } 
        // Pattern validation for valid input
        else if (!cleanedInput.match(/^[0-9+\- ]*$/)) {
            $('#contact_number_err').text('Please enter a valid mobile number');
        } 
        // Clear error message if valid
        else {
            $('#contact_number_err').text('');
        }
    });
});

        function toggleLanguageForm(languageIndex) {
            // Hide all language forms
            document.querySelectorAll('.language-form').forEach(function(form) {
                form.style.display = 'none';
            });
            // Display the selected language form
            document.getElementById('form-language-' + languageIndex).style.display = 'block';
   
            // Remove primary style from all buttons and add it to the selected one
            document.querySelectorAll('.language-btn').forEach(function(btn) {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            });
            document.querySelectorAll('.language-btn')[languageIndex].classList.add('btn-primary');
        }

</script>

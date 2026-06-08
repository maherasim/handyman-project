<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="fw-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if($auth_user->can('blog list'))
                            <a href="{{ route('blog.index') }}" class=" float-end btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ html()->form('POST', route('blog.store'))->attributes(['enctype' => 'multipart/form-data', 'data-toggle' => 'validator', 'id' => 'blog'])->open() }}
                        {{ html()->hidden('id',$blogdata->id ?? null) }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ html()->label(trans('messages.title') . ' <span class="text-danger">*</span>', 'title')->class('form-control-label') }}
                                {{ html()->text('title', $blogdata->title)->placeholder(trans('messages.title'))->class('form-control')->required() }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                <div class="form-group col-md-4">
                                    {{ html()->label(__('messages.select_name', ['select' => __('messages.author')]), 'name')->class('form-control-label') }}
                                    <br />
                                    {{ html()->select('author_id', [optional($blogdata->author)->id => optional($blogdata->author)->display_name], optional($blogdata->author)->id)
                                        ->class('select2js form-group')
                                        ->id('author_id')
                                        ->attribute('data-placeholder', __('messages.select_name', ['select' => __('messages.author')]))
                                        ->attribute('data-ajax--url', route('ajax-list', ['type' => 'provider'])) 
                                        }}
                                </div>
                            @endif
                            <div class="form-group col-md-4">
                                {{ html()->label(trans('messages.status') . ' <span class="text-danger">*</span>', 'status')->class('form-control-label') }}
                                {{ html()->select('status', ['1' => __('messages.active'), '0' => __('messages.inactive')], $blogdata->status)->id('role')->class('form-control select2js')->required() }}
                            </div>
                    
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="blog_attachment">{{ __('messages.image') }} <span class="text-danger"></span> </label>
                                <div class="custom-file">
                                    <input type="file" id="blog_attachment_input" onchange="prepareBlogAttachments(this)" name="blog_attachment[]" class="custom-file-input" data-file-error="{{ __('messages.files_not_allowed') }}" accept="image/*" multiple>
                                    <label class="custom-file-label upload-label">{{ __('messages.choose_file',['file' =>  __('messages.attachments') ]) }}</label>
                                </div>
                                <small id="blog-image-prepare-status" class="d-none mt-1 text-primary fw-bold"></small>
                            </div>
                    
                            <div class="form-group col-md-4">
                            <label class="form-control-label" for="tags">{{ __('messages.tags') }} </label>
                                <select class="form-control select2-tag" name="tags[]" multiple="">
                                    @if(isset($blogdata))
                                    @if($blogdata->tags != null)
                                    @foreach(json_decode($blogdata->tags) as $tags)
                                    <option value="{{$tags}}" selected="">{{$tags}}</option>
                                        @endforeach
                                    @endif
                                    @endif
                                </select>
                            </div>
                    
                            <div class="row blog_attachment_div">
                                <div class="col-md-12">
                                    <div class="row" id="new_blog_attachment_previews"></div>
                                    @if(getMediaFileExit($blogdata, 'blog_attachment'))
                                        @php
                                    $attchments = $blogdata->getMedia('blog_attachment');
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

                                                <div class="col-md-2 pe-10 text-center galary file-gallary-{{$blogdata->id}} position-relative" data-gallery=".file-gallary-{{$blogdata->id}}" id="blog_attachment_preview_{{$attchment->id}}">
                                                    @if($extention)
                                                    <a id="attachment_files" href="{{ $attchment->getFullUrl() }}" class="list-group-item-action attachment-list" target="_blank">
                                                        <img src="{{ $attchment->getFullUrl() }}" class="attachment-image" alt="">
                                                                </a>
                                                            @else
                                                    <a id="attachment_files" class="video list-group-item-action attachment-list" href="{{ $attchment->getFullUrl() }}">
                                                                    <img src="{{ asset('images/file.png') }}" class="attachment-file">
                                                                </a>
                                                            @endif
                                                    <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $attchment->id, 'type' => 'blog_attachment']) }}" data--submit="confirm_form" data--confirmation='true' data--ajax="true" data-toggle="tooltip" title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}' data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}' data-message='{{ __("messages.remove_file_msg") }}'>
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
                            {{-- <!-- <div class="form-group col-md-12">
                                {{ Form::label('description',trans('messages.description'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('description', null, ['class'=>"form-control textarea" , 'rows'=>3  , 'placeholder'=> __('messages.description') ]) }}
                            </div> --> --}}
                            <div class="form-group col-md-12">
                                {{ html()->label(__('messages.description'), 'description')->class('form-control-label') }}
                                {{ html()->textarea('description',$blogdata->description)->class('form-control tinymce-description')->placeholder(__('messages.description')) }}
                            </div>
                        </div>
                        
                        {{ html()->submit(trans('messages.save'))->class('btn btn-md btn-primary float-end')->id('blog-submit-button')->attribute('data-default-text', trans('messages.save')) }}
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>                                                                                                                                                                                                                                       
    @section('bottom_script')     
        <script>
            function preview() {
                blog_image_preview.src = URL.createObjectURL(event.target.files[0]);
            }

            $(document).ready(function() {
                $('.select2-tag').select2({
                tags: true,
                createTag: function (params) {
                    if (params.term.length > 2) {
                    return {
                        id: params.term,
                        text: params.term,
                        newTag: true
                    }
                    }
                    return null;
                }
                });
            });

            (function($) {
                $(document).ready(function(){
                    tinymceEditor('.tinymce-description',' ',function (ed) {

                    }, 450)
                
                });

            })(jQuery);
            document.addEventListener('DOMContentLoaded', function() { 
              checkImage();
});
   function checkImage() { 
    var id = @json($blogdata->id ); 
    var route = "{{ route('check-image', ':id') }}";
    route = route.replace(':id', id);  
    var type = 'blog_attachment';

    $.ajax({
        url: route,
        type: 'GET',   
        data: {
            type: type,   
        }, 
        success: function(result) {  
            var attachments = result.results;  

            if (attachments.length === 0) { 
                $('input[name="blog_attachment[]"]').attr('required', 'required');
            } else { 
                $('input[name="blog_attachment[]"]').removeAttr('required');
            }         
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);  
        }
    });
}

        async function prepareBlogAttachments(input) {
            const previewRow = document.getElementById('new_blog_attachment_previews');
            if (previewRow) previewRow.innerHTML = '';
            setBlogAttachmentsPreparing(true);

            try {
                if (!input.files || !input.files.length) return;

                const dataTransfer = new DataTransfer();
                const files = Array.from(input.files);
                const maxOriginalSize = 4 * 1024 * 1024;
                const rejectedFiles = [];
                updateBlogImagePrepareStatus('Preparing 0 of ' + files.length + ' images...');

                for (let index = 0; index < files.length; index++) {
                    const file = files[index];
                    updateBlogImagePrepareStatus('Preparing ' + (index + 1) + ' of ' + files.length + ' images...');
                    if (file.size > maxOriginalSize) {
                        rejectedFiles.push(file.name);
                        continue;
                    }

                    const preparedFile = await prepareBlogImageForUpload(file, files.length);
                    dataTransfer.items.add(preparedFile);

                    if (previewRow && preparedFile.type && preparedFile.type.startsWith('image/')) {
                        const objectUrl = URL.createObjectURL(preparedFile);
                        const col = document.createElement('div');
                        col.className = 'col-md-2 text-center mt-2';
                        col.innerHTML = '<img src="' + objectUrl + '" class="attachment-image" alt="">';
                        previewRow.appendChild(col);

                        col.querySelector('img').addEventListener('load', function() {
                            URL.revokeObjectURL(objectUrl);
                        }, { once: true });
                    }
                }

                input.files = dataTransfer.files;
                if (input.files.length > 0) {
                    $('.upload-label').text(input.files.length > 1 ? input.files.length + ' files selected' : input.files[0].name);
                } else {
                    $('.upload-label').text("{{ __('messages.choose_file',['file' =>  __('messages.attachments') ]) }}");
                }
                updateBlogImagePrepareStatus(input.files.length ? input.files.length + ' image' + (input.files.length === 1 ? '' : 's') + ' ready for upload.' : '');
                if (rejectedFiles.length) {
                    if (typeof Snackbar !== 'undefined') {
                        Snackbar.show({ text: 'Each image must be 4 MB or smaller.', pos: 'bottom-center', backgroundColor: '#d32f2f', actionTextColor: '#fff' });
                    } else {
                        alert('Each image must be 4 MB or smaller.');
                    }
                }
            } finally {
                setBlogAttachmentsPreparing(false);
            }
        }

        function updateBlogImagePrepareStatus(message) {
            const status = document.getElementById('blog-image-prepare-status');
            if (!status) return;

            status.textContent = message;
            status.classList.toggle('d-none', !message);
        }

        function setBlogAttachmentsPreparing(isPreparing) {
            window.blogAttachmentsPreparing = isPreparing;

            const submitButton = document.getElementById('blog-submit-button');
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

        function prepareBlogImageForUpload(file, batchSize) {
            const isLargeBatch = batchSize >= 3;
            const maxSize = isLargeBatch ? 120 * 1024 : 250 * 1024;
            const maxDimension = isLargeBatch ? 800 : 1000;

            if (!file.type || !file.type.startsWith('image/')) {
                return Promise.resolve(file);
            }

            return new Promise(function(resolve) {
                const image = new Image();
                const objectUrl = URL.createObjectURL(file);

                image.onload = function() {
                    URL.revokeObjectURL(objectUrl);

                    if (!isLargeBatch && file.size <= maxSize && Math.max(image.width, image.height) <= maxDimension) {
                        resolve(file);
                        return;
                    }

                    const ratio = Math.min(maxDimension / image.width, maxDimension / image.height, 1);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.width * ratio));
                    canvas.height = Math.max(1, Math.round(image.height * ratio));

                    const context = canvas.getContext('2d');
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    const qualities = isLargeBatch ? [0.62, 0.52, 0.44, 0.36] : [0.72, 0.62, 0.52, 0.44];
                    compressBlogImage(canvas, file, qualities, maxSize, resolve);
                };

                image.onerror = function() {
                    URL.revokeObjectURL(objectUrl);
                    resolve(file);
                };

                image.src = objectUrl;
            });
        }

        function compressBlogImage(canvas, originalFile, qualities, maxSize, resolve) {
            const quality = qualities.shift() || 0.36;

            canvas.toBlob(function(blob) {
                if (!blob) {
                    resolve(originalFile);
                    return;
                }

                if (blob.size > maxSize && qualities.length) {
                    compressBlogImage(canvas, originalFile, qualities, maxSize, resolve);
                    return;
                }

                const safeName = originalFile.name.replace(/\.[^.]+$/, '') + '.jpg';
                resolve(new File([blob], safeName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                }));
            }, 'image/jpeg', quality);
        }

        $(document).on('submit', '#blog', function() {
            if (window.blogAttachmentsPreparing) {
                if (typeof Snackbar !== 'undefined') {
                    Snackbar.show({
                        text: 'Please wait, images are preparing for upload.',
                        pos: 'bottom-center',
                        backgroundColor: '#d32f2f',
                        actionTextColor: '#fff'
                    });
                } else {
                    alert('Please wait, images are preparing for upload.');
                }
                return false;
            }
        });
        </script>
    @endsection
</x-master-layout>
<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 offset-md-2">

                {{-- Page header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">{{ __('messages.bulk_category_image_upload') }}</h4>
                    <a href="{{ route('category.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> {{ __('messages.back') }}
                    </a>
                </div>

                {{-- Instruction box --}}
                <div class="alert alert-info mb-4">
                    <i class="ri-information-line me-1"></i>
                    {!! __('messages.bulk_upload_instruction') !!}
                </div>

                {{-- Result: matched --}}
                @if(session('bulk_matched') && count(session('bulk_matched')))
                <div class="alert alert-success">
                    <strong>{{ __('messages.bulk_upload_matched') }} ({{ count(session('bulk_matched')) }}):</strong>
                    <ul class="mb-0 mt-1">
                        @foreach(session('bulk_matched') as $name)
                            <li><i class="ri-checkbox-circle-line me-1"></i> {{ $name }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Result: skipped --}}
                @if(session('bulk_skipped') && count(session('bulk_skipped')))
                <div class="alert alert-warning">
                    <strong>{{ __('messages.bulk_upload_skipped') }} ({{ count(session('bulk_skipped')) }}):</strong>
                    <ul class="mb-0 mt-1">
                        @foreach(session('bulk_skipped') as $msg)
                            <li><i class="ri-error-warning-line me-1"></i> {{ $msg }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Upload form --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('category.bulk-image.store') }}" enctype="multipart/form-data" id="bulkUploadForm">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    {{ __('messages.bulk_upload_select_images') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                       name="images[]"
                                       id="bulk_images"
                                       class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                                       multiple
                                       accept=".jpg,.jpeg,.png,.gif,.webp">
                                <small class="text-muted">
                                    JPG, PNG, GIF, WEBP — max 5 MB each. Accepted formats: <code>CategoryName.jpg</code>
                                </small>
                                @error('images')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('images.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Preview area --}}
                            <div id="preview-area" class="row g-3 mb-4"></div>

                            <button type="submit" class="btn btn-primary" id="upload-btn" style="background:#3333ff !important; border:none;">
                                <i class="ri-upload-cloud-line me-1"></i> {{ __('messages.bulk_upload_submit') }}
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.getElementById('bulk_images').addEventListener('change', function () {
        var preview = document.getElementById('preview-area');
        preview.innerHTML = '';
        var files = Array.from(this.files);
        files.forEach(function (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                col.innerHTML =
                    '<div class="card border text-center p-2">' +
                        '<img src="' + e.target.result + '" class="img-fluid rounded mb-2" style="height:90px;object-fit:cover;">' +
                        '<small class="text-muted text-truncate d-block" title="' + file.name + '">' + file.name + '</small>' +
                    '</div>';
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });
    </script>
</x-master-layout>

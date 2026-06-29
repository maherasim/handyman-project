{{ html()->form('POST', route('envSetting'))->attribute('data-toggle', 'validator')->open() }}

{{ html()->hidden('id', null)->class('form-control') }}
{{ html()->hidden('page', $page)->class('form-control') }}
{{ html()->hidden('type', 'mail')->class('form-control') }}

    
    <div class="col-md-12 mt-20">
        <div class="row">
            @foreach(config('constant.MAIL_SETTING') as $key => $value)
                <div class="col-md-6">
                    <div class="form-group">
                            <label class="form-control-label text-capitalize">
                                {{ strtolower(str_replace('_', ' ', $key)) }}
                            </label>
                            <div class="input-group">
                                <input type="{{ $key == 'MAIL_PASSWORD' ? 'password' : 'text' }}"
                                    value="{{ $value }}"
                                    name="ENV[{{ $key }}]"
                                    id="mail_field_{{ $loop->index }}"
                                    class="form-control"
                                    placeholder="{{ config('constant.MAIL_PLACEHOLDER.'.$key) }}">
                                @if($key == 'MAIL_PASSWORD')
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="var f=document.getElementById('mail_field_{{ $loop->index }}'); f.type = f.type==='password' ? 'text' : 'password';">
                                    <i class="ri-eye-line"></i>
                                </button>
                                @endif
                            </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

{{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end') }}
{{ html()->form()->close() }}
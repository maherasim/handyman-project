@php
    $currentLang = app()->getLocale();
    $setting = $currentLang === 'de' ? $deSettings : $enSettings;
@endphp

{{ html()->form('POST', route('bankTransferSetting'))->attribute('data-toggle', 'validator')->open() }}
@csrf
{{ html()->hidden('language', $currentLang) }}
{{ html()->hidden('page', $page) }}

<div class="row">
    <div class="col-lg-6">
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_recipient'))->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('recipient', $setting->recipient ?? '')->class('form-control') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_iban'))->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('iban', $setting->iban ?? '')->class('form-control') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bic'))->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('bic', $setting->bic ?? '')->class('form-control') }}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bank_name'))->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('bank_name', $setting->bank_name ?? '')->class('form-control') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bank_address'))->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->textarea('bank_address', $setting->bank_address ?? '')->class('form-control')->rows(3) }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_email'))->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->email('email', $setting->email ?? '')->class('form-control') }}
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-group">
            <div class="col-sm-12">
                {{ html()->submit(__('messages.save'))->class('btn btn-md btn-primary float-md-end') }}
            </div>
        </div>
    </div>
</div>

{{ html()->form()->close() }}

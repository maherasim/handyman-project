@php
    $enSettings = $enSettings ?? null;
    $deSettings = $deSettings ?? null;
@endphp

{{-- ===== ENGLISH FORM ===== --}}
{{ html()->form('POST', route('bankTransferSetting'))->attribute('data-toggle', 'validator')->open() }}
@csrf
{{ html()->hidden('language', 'en') }}
{{ html()->hidden('page', $page) }}

<div class="row">
    <div class="col-lg-6">
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_recipient') . ' (EN)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('recipient', $enSettings->recipient ?? '')->class('form-control')->placeholder('e.g. Frobster Marketplace') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_iban') . ' (EN)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('iban', $enSettings->iban ?? '')->class('form-control')->placeholder('e.g. DE02 1001 0178 1361 6331 79') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bic') . ' (EN)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('bic', $enSettings->bic ?? '')->class('form-control')->placeholder('e.g. REVODEB2') }}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bank_name') . ' (EN)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('bank_name', $enSettings->bank_name ?? '')->class('form-control')->placeholder('e.g. Revolut Bank UAB') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bank_address') . ' (EN)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->textarea('bank_address', $enSettings->bank_address ?? '')->class('form-control')->rows(3)->placeholder('Full bank address') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_email') . ' (EN)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->email('email', $enSettings->email ?? '')->class('form-control')->placeholder('e.g. billing@frobster.com') }}
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

<hr class="my-4">

{{-- ===== DEUTSCH FORM ===== --}}
{{ html()->form('POST', route('bankTransferSetting'))->attribute('data-toggle', 'validator')->open() }}
@csrf
{{ html()->hidden('language', 'de') }}
{{ html()->hidden('page', $page) }}

<div class="row">
    <div class="col-lg-6">
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_recipient') . ' (DE)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('recipient', $deSettings->recipient ?? '')->class('form-control')->placeholder('z.B. Frobster Marketplace') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_iban') . ' (DE)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('iban', $deSettings->iban ?? '')->class('form-control')->placeholder('z.B. DE02 1001 0178 1361 6331 79') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bic') . ' (DE)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('bic', $deSettings->bic ?? '')->class('form-control')->placeholder('z.B. REVODEB2') }}
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bank_name') . ' (DE)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->text('bank_name', $deSettings->bank_name ?? '')->class('form-control')->placeholder('z.B. Revolut Bank UAB') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_bank_address') . ' (DE)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->textarea('bank_address', $deSettings->bank_address ?? '')->class('form-control')->rows(3)->placeholder('Vollständige Bankadresse') }}
            </div>
        </div>
        <div class="form-group mb-3">
            {{ html()->label(__('messages.bank_transfer_email') . ' (DE)')->class('form-control-label mb-1') }}
            <div class="col-sm-12">
                {{ html()->email('email', $deSettings->email ?? '')->class('form-control')->placeholder('z.B. billing@frobster.com') }}
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

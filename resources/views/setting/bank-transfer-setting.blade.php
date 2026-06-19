@php
    $enSettings = $enSettings ?? null;
    $deSettings = $deSettings ?? null;
@endphp

<ul class="nav nav-tabs mb-3" id="bankTransferTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-en-btn" data-bs-toggle="tab" data-bs-target="#tab-en" type="button" role="tab">
            English (EN)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-de-btn" data-bs-toggle="tab" data-bs-target="#tab-de" type="button" role="tab">
            Deutsch (DE)
        </button>
    </li>
</ul>

<div class="tab-content" id="bankTransferTabContent">

    {{-- ===== ENGLISH TAB ===== --}}
    <div class="tab-pane fade show active" id="tab-en" role="tabpanel">
        {{ html()->form('POST', route('bankTransferSetting'))->attribute('data-toggle', 'validator')->open() }}
        @csrf
        {{ html()->hidden('language', 'en') }}
        {{ html()->hidden('page', $page) }}

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_recipient'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('recipient', $enSettings->recipient ?? '')->class('form-control')->placeholder('e.g. Frobster Marketplace') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_iban'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('iban', $enSettings->iban ?? '')->class('form-control')->placeholder('e.g. DE02 1001 0178 1361 6331 79') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_bic'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('bic', $enSettings->bic ?? '')->class('form-control')->placeholder('e.g. REVODEB2') }}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_bank_name'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('bank_name', $enSettings->bank_name ?? '')->class('form-control')->placeholder('e.g. Revolut Bank UAB') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_bank_address'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->textarea('bank_address', $enSettings->bank_address ?? '')->class('form-control')->rows(3)->placeholder('Full bank address') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_email'))->class('form-control-label mb-1') }}
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
    </div>

    {{-- ===== DEUTSCH TAB ===== --}}
    <div class="tab-pane fade" id="tab-de" role="tabpanel">
        {{ html()->form('POST', route('bankTransferSetting'))->attribute('data-toggle', 'validator')->open() }}
        @csrf
        {{ html()->hidden('language', 'de') }}
        {{ html()->hidden('page', $page) }}

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_recipient'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('recipient', $deSettings->recipient ?? '')->class('form-control')->placeholder('z.B. Frobster Marketplace') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_iban'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('iban', $deSettings->iban ?? '')->class('form-control')->placeholder('z.B. DE02 1001 0178 1361 6331 79') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_bic'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('bic', $deSettings->bic ?? '')->class('form-control')->placeholder('z.B. REVODEB2') }}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_bank_name'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->text('bank_name', $deSettings->bank_name ?? '')->class('form-control')->placeholder('z.B. Revolut Bank UAB') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_bank_address'))->class('form-control-label mb-1') }}
                    <div class="col-sm-12">
                        {{ html()->textarea('bank_address', $deSettings->bank_address ?? '')->class('form-control')->rows(3)->placeholder('Vollständige Bankadresse') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label(__('messages.bank_transfer_email'))->class('form-control-label mb-1') }}
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
    </div>

</div>

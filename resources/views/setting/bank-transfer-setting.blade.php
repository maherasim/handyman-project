@php
    $enSettings = $enSettings ?? null;
    $deSettings = $deSettings ?? null;
@endphp

<ul class="nav nav-tabs mb-3" id="bankTransferTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-en-btn" data-bs-toggle="tab" data-bs-target="#tab-en" type="button" role="tab">
            🇬🇧 English (EN)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-de-btn" data-bs-toggle="tab" data-bs-target="#tab-de" type="button" role="tab">
            🇩🇪 Deutsch (DE)
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
                    {{ html()->label('Recipient / Company Name')->class('form-control-label mb-1')->for('recipient_en') }}
                    <div class="col-sm-12">
                        {{ html()->text('recipient', $enSettings->recipient ?? '')->id('recipient_en')->class('form-control')->placeholder('e.g. Frobster Marketplace') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('IBAN')->class('form-control-label mb-1')->for('iban_en') }}
                    <div class="col-sm-12">
                        {{ html()->text('iban', $enSettings->iban ?? '')->id('iban_en')->class('form-control')->placeholder('e.g. DE02 1001 0178 1361 6331 79') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('BIC / SWIFT')->class('form-control-label mb-1')->for('bic_en') }}
                    <div class="col-sm-12">
                        {{ html()->text('bic', $enSettings->bic ?? '')->id('bic_en')->class('form-control')->placeholder('e.g. REVODEB2') }}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group mb-3">
                    {{ html()->label('Bank Name')->class('form-control-label mb-1')->for('bank_name_en') }}
                    <div class="col-sm-12">
                        {{ html()->text('bank_name', $enSettings->bank_name ?? '')->id('bank_name_en')->class('form-control')->placeholder('e.g. Revolut Bank UAB') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('Bank Address')->class('form-control-label mb-1')->for('bank_address_en') }}
                    <div class="col-sm-12">
                        {{ html()->textarea('bank_address', $enSettings->bank_address ?? '')->id('bank_address_en')->class('form-control')->rows(3)->placeholder('Full bank address') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('Billing Email')->class('form-control-label mb-1')->for('email_en') }}
                    <div class="col-sm-12">
                        {{ html()->email('email', $enSettings->email ?? '')->id('email_en')->class('form-control')->placeholder('e.g. billing@frobster.com') }}
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
                    {{ html()->label('Empfänger / Firmenname')->class('form-control-label mb-1')->for('recipient_de') }}
                    <div class="col-sm-12">
                        {{ html()->text('recipient', $deSettings->recipient ?? '')->id('recipient_de')->class('form-control')->placeholder('z.B. Frobster Marketplace') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('IBAN')->class('form-control-label mb-1')->for('iban_de') }}
                    <div class="col-sm-12">
                        {{ html()->text('iban', $deSettings->iban ?? '')->id('iban_de')->class('form-control')->placeholder('z.B. DE02 1001 0178 1361 6331 79') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('BIC / SWIFT')->class('form-control-label mb-1')->for('bic_de') }}
                    <div class="col-sm-12">
                        {{ html()->text('bic', $deSettings->bic ?? '')->id('bic_de')->class('form-control')->placeholder('z.B. REVODEB2') }}
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group mb-3">
                    {{ html()->label('Bankname')->class('form-control-label mb-1')->for('bank_name_de') }}
                    <div class="col-sm-12">
                        {{ html()->text('bank_name', $deSettings->bank_name ?? '')->id('bank_name_de')->class('form-control')->placeholder('z.B. Revolut Bank UAB') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('Bankadresse')->class('form-control-label mb-1')->for('bank_address_de') }}
                    <div class="col-sm-12">
                        {{ html()->textarea('bank_address', $deSettings->bank_address ?? '')->id('bank_address_de')->class('form-control')->rows(3)->placeholder('Vollständige Bankadresse') }}
                    </div>
                </div>
                <div class="form-group mb-3">
                    {{ html()->label('Rechnungs-E-Mail')->class('form-control-label mb-1')->for('email_de') }}
                    <div class="col-sm-12">
                        {{ html()->email('email', $deSettings->email ?? '')->id('email_de')->class('form-control')->placeholder('z.B. billing@frobster.com') }}
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

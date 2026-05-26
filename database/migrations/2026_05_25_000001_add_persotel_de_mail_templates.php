<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $manage = '<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de </li>';

        $footer = '<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>';

        $templates = [
            ['add_booking', 'admin', 'Neue Buchung erhalten', '<p>Hallo [[ admin_name ]],</p>
<p>Nachfolgend finden Sie die Buchungsdetails zu einer neuen Buchungsanfrage von [[ customer_name ]].</p>
<p>Bitte überprüfen Sie die Buchungsdetails und bestätigen Sie die Anfrage rechtzeitig.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>' . $manage . '
</ul>' . $footer],
            ['add_booking', 'provider', 'Neue Buchung erhalten', '<p>Hallo [[ provider_name ]],</p>
<p>Nachfolgend finden Sie die Buchungsdetails zu einer neuen Buchungsanfrage von [[ customer_name ]].</p>
<p>Bitte überprüfen Sie die Buchungsdetails und bestätigen Sie die Anfrage rechtzeitig.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>' . $manage . '
</ul>' . $footer],

            ['assigned_booking', 'handyman', 'Buchung zugewiesen!', '<p>Hallo [[ handyman_name ]],</p>
<p>Ihnen wurde die Durchführung einer Buchung zugewiesen. Bitte bereiten Sie sich darauf vor, <strong>[[ booking_services_name ]]</strong> auszuführen.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>' . $manage . '
</ul>' . $footer],
            ['assigned_booking', 'user', 'Buchung zugewiesen!', '<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass Ihre Buchung #[[ booking_id ]] [[ assignee_name ]] zugewiesen wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Angestellte(r): [[ assignee_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>' . $manage . '
</ul>' . $footer],
            ['assigned_booking', 'provider', 'Buchung zugewiesen!', '<p>Hallo [[ provider_name ]],</p>
<p>Sie wurden mit der Durchführung der Buchung #[[ booking_id ]] beauftragt. Bitte bereiten Sie sich darauf vor, den Service <strong>[[ booking_services_name ]]</strong> zu erbringen.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>' . $manage . '
</ul>' . $footer],

            ['update_booking_status', 'admin', 'Buchungsstatus aktualisiert', '<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie informieren, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p>&nbsp;</p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>
</ul>' . $footer],
            ['update_booking_status', 'provider', 'Buchungsstatus aktualisiert', '<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>' . $manage . '
</ul>' . $footer],
            ['update_booking_status', 'handyman', 'Buchungsstatus aktualisiert', '<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Arbeitgeber: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>' . $manage . '
</ul>' . $footer],
            ['update_booking_status', 'user', 'Buchungsstatus aktualisiert', '<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>' . $manage . '
</ul>' . $footer],

            ['cancel_booking', 'admin', 'Buchung storniert', $this->cancelBody('admin_name', 'admin', $manage, $footer)],
            ['cancel_booking', 'provider', 'Buchung storniert', $this->cancelBody('provider_name', 'provider', $manage, $footer)],
            ['cancel_booking', 'handyman', 'Buchung storniert', $this->cancelBody('handyman_name', 'handyman', $manage, $footer)],
            ['cancel_booking', 'user', 'Buchung storniert', $this->cancelBody('customer_name', 'user', $manage, $footer)],

            ['payment_message_status', 'user', 'Zahlungsstatus aktualisiert', $this->paymentBody('customer_name', 'user', $manage, $footer)],
            ['payment_message_status', 'admin', 'Zahlungsstatus aktualisiert', $this->paymentBody('admin_name', 'admin', $manage, $footer)],
            ['payment_message_status', 'provider', 'Zahlungsstatus aktualisiert', $this->paymentBody('provider_name', 'provider', $manage, $footer)],
            ['payment_message_status', 'handyman', 'Zahlungsstatus aktualisiert', $this->paymentBody('handyman_name', 'handyman', $manage, $footer)],

            ['wallet_payout_transfer', 'admin', 'Wallet-Auszahlung', '<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>' . $footer],
            ['wallet_payout_transfer', 'provider', 'Auszahlung erhalten', $this->payoutReceivedBody('provider_name', $footer)],
            ['wallet_payout_transfer', 'handyman', 'Auszahlung erhalten', $this->payoutReceivedBody('handyman_name', $footer)],
            ['provider_payout', 'admin', 'Auszahlung verarbeitet', '<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>' . $footer],
            ['provider_payout', 'provider', 'Auszahlung erhalten', $this->payoutReceivedBody('provider_name', $footer)],
            ['handyman_payout', 'provider', 'Auszahlung verarbeitet', $this->payoutReceivedBody('provider_name', $footer)],
            ['handyman_payout', 'handyman', 'Auszahlung erhalten', $this->payoutReceivedBody('handyman_name', $footer)],

            ['wallet_top_up', 'admin', 'Wallet aufgeladen', '<p>Hallo [[ admin_name ]],</p>
<p>[[ customer_name ]] hat das Wallet mit [[ credit_debit_amount ]] aufgeladen.</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: [[ customer_name ]]</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktionsdatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionszeit: [[ wallet_transaction_time ]]</li>
</ul>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>' . $footer],
            ['wallet_top_up', 'provider', 'Wallet aufgeladen', $this->walletTopUpBody('provider_name', $manage, $footer)],
            ['wallet_top_up', 'user', 'Wallet aufgeladen', $this->walletTopUpBody('customer_name', $manage, $footer)],

            ['wallet_refund', 'admin', 'Wallet-Rückerstattung', '<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] gebuchter Service für [[ customer_name ]] storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden vorgenommen.</p>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>' . $footer],
            ['wallet_refund', 'provider', 'Wallet-Rückerstattung', $this->refundBody('provider_name', 'provider', $manage, $footer)],
            ['wallet_refund', 'user', 'Wallet-Rückerstattung', $this->refundBody('customer_name', 'user', $manage, $footer)],

            ['paid_with_wallet', 'admin', 'Wallet-Zahlung erfolgreich', $this->walletPaidBody('admin_name', 'admin', $manage, $footer)],
            ['paid_with_wallet', 'provider', 'Wallet-Zahlung erfolgreich', $this->walletPaidBody('provider_name', 'provider', $manage, $footer)],
            ['paid_with_wallet', 'handyman', 'Wallet-Zahlung erfolgreich', $this->walletPaidBody('handyman_name', 'handyman', $manage, $footer)],
            ['paid_with_wallet', 'user', 'Wallet-Zahlung erfolgreich', $this->walletPaidBody('customer_name', 'user', $manage, $footer)],

            ['job_requested', 'admin', 'Neue Jobanfrage auf [[ company_name ]]', '<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass eine neue Jobanfrage auf [[ company_name ]] veröffentlicht wurde.</p>
<p><strong>Auftragsdetails:</strong></p>
<p><strong>Auftrag #[[ job_id ]]</strong></p>
<p><strong>Auftraggeber:</strong><br>[[ customer_name ]]</p>
<p><strong>Jobauftrag:</strong><br>[[ job_request_name ]]</p>
<p><strong>Start Datum:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong><br>[[ job_request_created_at ]]</p>
<p>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>' . $footer],
            ['job_requested', 'provider', 'Neue Jobanfrage auf [[ company_name ]]', '<p>Hallo [[ provider_name ]],</p>
<p>Eine neue Jobanfrage wurde auf [[ company_name ]] veröffentlicht, die zu Ihren Dienstleistungen passen könnte.</p>
<p>Der Kunde sucht Unterstützung für folgende Aufgabe:</p>
<p><strong>Auftragsdetails:</strong></p>
<p><strong>Auftrag #[[ job_request_id ]]</strong></p>
<p><strong>Auftraggeber:</strong><br>[[ customer_name ]]</p>
<p><strong>Jobauftrag:</strong><br>[[ job_request_name ]]</p>
<p><strong>Start Datum:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong><br>[[ job_request_created_at ]]</p>
<p>Wenn dieser Auftrag zu Ihren Fähigkeiten passt, melden Sie sich bei [[ company_name ]] an (oder öffnen Sie die App), sehen Sie sich die vollständigen Details an und reichen Sie Ihr Angebot ein.</p>
<p>Je schneller Sie antworten, desto höher sind Ihre Chancen.</p>
<p><strong><a href="[[ link ]]" style="color:#2563eb;">Job ansehen und Angebot abgeben</a></strong></p>
<p>Falls der Link nicht funktioniert, kopieren Sie bitte diese URL in Ihren Browser:<br>[[ link ]]</p>
<p>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>' . $footer],
        ];

        foreach ($templates as [$type, $userType, $subject, $body]) {
            $this->upsertMailTemplateContent($type, $userType, 'de', $subject, $body);
        }
    }

    public function down(): void
    {
        $types = [
            'add_booking', 'assigned_booking', 'update_booking_status', 'cancel_booking',
            'payment_message_status', 'wallet_payout_transfer', 'provider_payout',
            'handyman_payout', 'wallet_top_up', 'wallet_refund', 'paid_with_wallet',
            'job_requested',
        ];

        $templateIds = DB::table('mail_templates')->whereIn('type', $types)->pluck('id');

        DB::table('mail_template_content_mappings')
            ->whereIn('template_id', $templateIds)
            ->where('language', 'de')
            ->delete();
    }

    private function cancelBody(string $nameKey, string $role, string $manage, string $footer): string
    {
        $serviceKey = in_array($role, ['handyman', 'user'], true) ? 'booking_services_names' : 'booking_services_name';
        $providerLabel = $role === 'handyman' ? 'Arbeitgeber' : 'Auftragnehmer';
        $intro = $role === 'admin'
            ? 'Hiermit informieren wir Sie, dass die Buchung #[[ booking_id ]] für [[ booking_services_name ]] von [[ cancelled_user_name ]] storniert wurde.'
            : 'Hiermit informieren wir Sie, dass die Buchung #[[ booking_id ]] für [[ booking_services_name ]] von [[ cancelled_user_name ]] storniert wurde. Bitte prüfen Sie die Details und ergreifen Sie gegebenenfalls erforderliche Maßnahmen.';

        return '<p>Hallo [[ ' . $nameKey . ' ]],</p>
<p>' . $intro . '</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ ' . $serviceKey . ' ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>' . $providerLabel . ': [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>' . ($role === 'handyman' ? '' : '
  <li>Betrag: [[ total_amount ]]</li>') . $manage . '
</ul>' . $footer;
    }

    private function paymentBody(string $nameKey, string $role, string $manage, string $footer): string
    {
        $providerLabel = $role === 'handyman' ? 'Auftragnehmer' : 'Auftragnehmer';

        return '<p>Hallo [[ ' . $nameKey . ' ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf: "[[ payment_status ]]" geändert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>' . $providerLabel . ': [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>' . ($role === 'handyman' ? '' : '
  <li>Betrag: [[ total_amount ]]</li>') . $manage . '
</ul>' . $footer;
    }

    private function payoutReceivedBody(string $nameKey, string $footer): string
    {
        return '<p>Hallo [[ ' . $nameKey . ' ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>' . $footer;
    }

    private function walletTopUpBody(string $nameKey, string $manage, string $footer): string
    {
        $recipient = $nameKey === 'provider_name' ? '[[ provider_name ]]' : '[[ customer_name ]]';
        $intro = $nameKey === 'provider_name'
            ? 'Wir freuen uns, Ihnen mitzuteilen, dass [[ credit_debit_amount ]] Ihrem Wallet gutgeschrieben wurden.'
            : 'Ihr Wallet wurde erfolgreich mit [[ credit_debit_amount ]] aufgeladen.';

        return '<p>Hallo [[ ' . $nameKey . ' ]],</p>
<p>' . $intro . '</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: ' . $recipient . '</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktionsdatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionszeit: [[ wallet_transaction_time ]]</li>' . $manage . '
</ul>' . $footer;
    }

    private function refundBody(string $nameKey, string $role, string $manage, string $footer): string
    {
        $intro = $role === 'provider'
            ? 'Wir möchten Sie darüber informieren, dass der von Ihnen für [[ customer_name ]] erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden veranlasst.'
            : 'Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] für Sie gebuchter Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] Ihrem Wallet gutgeschrieben.';

        return '<p>Hallo [[ ' . $nameKey . ' ]],</p>
<p>' . $intro . '</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>' . ($role === 'user' ? '
  <li>Betrag: [[ amount ]]</li>' : '') . $manage . '
</ul>' . $footer;
    }

    private function walletPaidBody(string $nameKey, string $role, string $manage, string $footer): string
    {
        $intro = [
            'admin' => '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.',
            'provider' => 'Die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet bezahlt. Bitte prüfen Sie die untenstehenden Buchungsdetails und verwalten Sie die Buchung entsprechend.',
            'handyman' => 'Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet durchgeführt. Bitte prüfen Sie die untenstehenden Buchungsdetails und verwalten Sie die Buchung entsprechend.',
            'user' => 'Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über Ihr Wallet durchgeführt.',
        ][$role];

        return '<p>Hallo [[ ' . $nameKey . ' ]],</p>
<p>' . $intro . '</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>' . ($role === 'handyman' || $role === 'user' ? '
  <li>Angestellte(r): [[ assignee_name ]]</li>' : '
  <li>Betrag: [[ amount ]]</li>') . $manage . '
</ul>' . $footer;
    }

    private function upsertMailTemplateContent(string $type, string $userType, string $language, string $subject, string $body): void
    {
        $templateId = DB::table('mail_templates')->where('type', $type)->value('id');
        if (!$templateId) {
            return;
        }

        DB::table('mail_template_content_mappings')->updateOrInsert(
            [
                'template_id' => $templateId,
                'user_type' => $userType,
                'language' => $language,
            ],
            [
                'notification_link' => '',
                'notification_message' => '',
                'status' => 1,
                'subject' => $subject,
                'template_detail' => $body,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
};

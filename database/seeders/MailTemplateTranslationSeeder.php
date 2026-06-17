<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailTemplates;
use App\Models\MailTemplateContentMapping;

/**
 * Upserts DE + EN content for every mail template.
 * Safe to run multiple times — uses updateOrCreate, never deletes rows.
 *
 * Run with:
 *   php artisan db:seed --class=MailTemplateTranslationSeeder
 */
class MailTemplateTranslationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $type => $recipients) {
            $template = MailTemplates::where('type', $type)->first();
            if (! $template) {
                $this->command->warn("MailTemplate type '{$type}' not found — skipping.");
                continue;
            }

            foreach ($recipients as $userType => $langs) {
                foreach ($langs as $lang => $data) {
                    MailTemplateContentMapping::updateOrCreate(
                        [
                            'template_id' => $template->id,
                            'user_type'   => $userType,
                            'language'    => $lang,
                        ],
                        [
                            'subject'              => $data['subject'],
                            'template_detail'      => $data['body'],
                            'status'               => 1,
                            'notification_link'    => '',
                            'notification_message' => '',
                        ]
                    );
                }
            }

            $this->command->info("✓ {$type}");
        }

        $this->command->info('Done — all mail template translations upserted.');
    }

    private function templates(): array
    {
        // ── Shared footers ────────────────────────────────────────────────────
        $footer_de = '<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>';

        $footer_en = '<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>';

        $support_de = '<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de </li>';

        $support_en = '<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>';

        return [

            // =================================================================
            // add_booking
            // =================================================================
            'add_booking' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Neue Buchungsanfrage #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
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
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'New Booking Received #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>We want to inform you that a new booking request has been submitted by a customer.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer Name: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Service Booked: [[ booking_services_name ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Neue Buchungsanfrage #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
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
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'New Booking Received #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>You have received a new booking request from [[ customer_name ]].</p>
<p>Please review the booking details and confirm the request in a timely manner.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer Name: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Service Booked: [[ booking_services_name ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // assigned_booking
            // =================================================================
            'assigned_booking' => [

                'handyman' => [
                    'de' => [
                        'subject' => 'Buchung #[[ booking_id ]] zugewiesen',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Ihnen wurde die Durchführung einer Buchung zugewiesen. Bitte bereiten Sie sich darauf vor, <strong>[[ booking_services_name ]]</strong> auszuführen.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking #[[ booking_id ]] Assigned to You',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>You have been assigned to manage a booking. Please be prepared to provide service for <strong>[[ booking_services_name ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer Name: [[ customer_name ]]</li>
  <li>Employer: [[ provider_name ]]</li>
  <li>Service Booked: [[ booking_services_name ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'user' => [
                    'de' => [
                        'subject' => 'Ihre Buchung #[[ booking_id ]] wurde zugewiesen',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
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
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Your Booking #[[ booking_id ]] Has Been Assigned',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that your booking #[[ booking_id ]] has been assigned to [[ assignee_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Assigned To: [[ assignee_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Buchung #[[ booking_id ]] — Auftrag zugewiesen',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
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
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking #[[ booking_id ]] — Job Assigned to You',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>You have been assigned to fulfil booking #[[ booking_id ]]. Please be prepared to deliver <strong>[[ booking_services_name ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // update_booking_status
            // =================================================================
            'update_booking_status' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Buchungsstatus geändert — #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie informieren, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p>&nbsp;</p>
<li>Buchungs-ID: #[[ booking_id ]]</li>
<li>Service: [[ booking_services_name ]]</li>
<li>Kunde: [[ customer_name ]]</li>
<li>Auftragnehmer: [[ provider_name ]]</li>
<li>Datum: [[ booking_date ]]</li>
<li>Uhrzeit: [[ booking_time ]]</li>
<li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
<li>Betrag: [[total_amount ]]</li>
<li> </li>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking Status Updated — #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>The status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been updated to <strong>[[ booking_status ]]</strong>.</p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Buchungsstatus geändert — #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking Status Updated — #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>The status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has changed to <strong>[[ booking_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'handyman' => [
                    'de' => [
                        'subject' => 'Buchungsstatus geändert — #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Arbeitgeber: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking Status Updated — #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>The status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has changed to <strong>[[ booking_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Employer: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'user' => [
                    'de' => [
                        'subject' => 'Buchungsstatus geändert — #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
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
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking Status Updated — #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>The status of your booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has changed to <strong>[[ booking_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // cancel_booking
            // =================================================================
            'cancel_booking' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Buchung #[[ booking_id ]] storniert',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Buchung #[[ booking_id ]] für [[ booking_services_name ]] von [[ cancelled_user_name ]] storniert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_name ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking #[[ booking_id ]] Cancelled',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>Booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Buchung #[[ booking_id ]] storniert',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Buchung #[[ booking_id ]] für [[ booking_services_name ]] von [[ cancelled_user_name ]] storniert wurde. Bitte prüfen Sie die Details und ergreifen Sie gegebenenfalls erforderliche Maßnahmen.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_name ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking #[[ booking_id ]] Cancelled',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>Booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'handyman' => [
                    'de' => [
                        'subject' => 'Buchung #[[ booking_id ]] storniert',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Buchung #[[ booking_id ]] für [[ booking_services_name ]] von [[ cancelled_user_name ]] storniert wurde. Bitte prüfen Sie die Details und ergreifen Sie gegebenenfalls erforderliche Maßnahmen.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Arbeitgeber: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking #[[ booking_id ]] Cancelled',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>Booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Employer: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'user' => [
                    'de' => [
                        'subject' => 'Buchung #[[ booking_id ]] storniert',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Buchung #[[ booking_id ]] für [[ booking_services_name ]] von [[ cancelled_user_name ]] storniert wurde. Bitte prüfen Sie die Details und ergreifen Sie gegebenenfalls erforderliche Maßnahmen.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Booking #[[ booking_id ]] Cancelled',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>Booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // payment_message_status
            // =================================================================
            'payment_message_status' => [

                'user' => [
                    'de' => [
                        'subject' => 'Zahlungsstatus geändert — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf:" [[ payment_status ]]" geändert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payment Status Updated — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>The payment status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been updated to <strong>[[ payment_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'admin' => [
                    'de' => [
                        'subject' => 'Zahlungsstatus geändert — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf:" [[ payment_status ]]" geändert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payment Status Updated — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>The payment status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been updated to <strong>[[ payment_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Zahlungsstatus geändert — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf:" [[ payment_status ]]" geändert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[total_amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payment Status Updated — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>The payment status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been updated to <strong>[[ payment_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'handyman' => [
                    'de' => [
                        'subject' => 'Zahlungsstatus geändert — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf:" [[ payment_status ]]" geändert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payment Status Updated — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>The payment status of booking #[[ booking_id ]] for <strong>[[ booking_services_name ]]</strong> has been updated to <strong>[[ payment_status ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // wallet_payout_transfer
            // =================================================================
            'wallet_payout_transfer' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Auszahlung verarbeitet',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payout Processed',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>We want to inform you that a payout of [[ pay_amount ]] has been successfully processed to [[ user_name ]].</p>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Ihre Auszahlung wurde verarbeitet',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Your Payout Has Been Processed',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that a payout of [[ pay_amount ]] has been successfully processed to your account.</p>
<p>If you have any questions, please contact our support team at info@frobster.com.</p>' . $footer_en,
                    ],
                ],

                'handyman' => [
                    'de' => [
                        'subject' => 'Ihre Auszahlung wurde verarbeitet',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Your Payout Has Been Processed',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>We are pleased to inform you that a payout of [[ pay_amount ]] has been successfully processed to your account.</p>
<p>If you have any questions, please contact our support team at info@frobster.com.</p>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // wallet_top_up
            // =================================================================
            'wallet_top_up' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Wallet aufgeladen — [[ customer_name ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>[[ customer_name ]] hat das Wallet mit [[ credit_debit_amount ]] aufgeladen.</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: [[ customer_name ]]</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktiondatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionzeit: [[ wallet_transaction_time ]]</li>
</ul>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Wallet Topped Up — [[ customer_name ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>[[ customer_name ]] has topped up their wallet with [[ credit_debit_amount ]].</p>
<p><strong>Transaction Details:</strong></p>
<ul>
  <li>Customer: [[ customer_name ]]</li>
  <li>Transaction ID: [[ wallet_transaction_id ]]</li>
  <li>Transaction Type: [[ wallet_transaction_type ]]</li>
  <li>Amount: [[ wallet_amount ]]</li>
  <li>Transaction Date: [[ wallet_transaction_date ]]</li>
  <li>Transaction Time: [[ wallet_transaction_time ]]</li>
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Wallet aufgeladen',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitzuteilen, dass [[ credit_debit_amount ]] Ihrem Wallet gutgeschrieben wurden.</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: [[ provider_name ]]</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktionsdatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionszeit: [[ wallet_transaction_time ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Wallet Credited',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that [[ credit_debit_amount ]] has been credited to your wallet.</p>
<p><strong>Transaction Details:</strong></p>
<ul>
  <li>User: [[ provider_name ]]</li>
  <li>Transaction ID: [[ wallet_transaction_id ]]</li>
  <li>Transaction Type: [[ wallet_transaction_type ]]</li>
  <li>Amount: [[ wallet_amount ]]</li>
  <li>Transaction Date: [[ wallet_transaction_date ]]</li>
  <li>Transaction Time: [[ wallet_transaction_time ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'user' => [
                    'de' => [
                        'subject' => 'Wallet erfolgreich aufgeladen',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
<p>Ihr Wallet wurde erfolgreich mit [[ credit_debit_amount ]] aufgeladen.</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: [[ customer_name ]]</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktionsdatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionszeit: [[ wallet_transaction_time ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Wallet Successfully Topped Up',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>Your wallet has been successfully topped up with [[ credit_debit_amount ]].</p>
<p><strong>Transaction Details:</strong></p>
<ul>
  <li>Customer: [[ customer_name ]]</li>
  <li>Transaction ID: [[ wallet_transaction_id ]]</li>
  <li>Transaction Type: [[ wallet_transaction_type ]]</li>
  <li>Amount: [[ wallet_amount ]]</li>
  <li>Transaction Date: [[ wallet_transaction_date ]]</li>
  <li>Transaction Time: [[ wallet_transaction_time ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // wallet_refund
            // =================================================================
            'wallet_refund' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Rückerstattung veranlasst — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] erbrachte Service für [[ customer_name ]] storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden vorgenommen.</p>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Refund Issued — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>The service booked by [[ provider_name ]] for [[ customer_name ]] has been cancelled. As a result, a refund of [[ refund_amount ]] has been issued to the customer.</p>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Rückerstattung veranlasst — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von Ihnen für [[ customer_name ]] erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden veranlasst.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Refund Issued — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>The service you provided for [[ customer_name ]] has been cancelled. A refund of [[ refund_amount ]] has been issued to the customer.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'user' => [
                    'de' => [
                        'subject' => 'Rückerstattung erhalten — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] für Sie erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] Ihrem Wallet gutgeschrieben.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Refund Credited — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>The service booked by [[ provider_name ]] for you has been cancelled. A refund of [[ refund_amount ]] has been credited to your wallet.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // paid_with_wallet
            // =================================================================
            'paid_with_wallet' => [

                'admin' => [
                    'de' => [
                        'subject' => 'Wallet-Zahlung — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Wallet Payment — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>The wallet payment of [[ amount ]] for booking #[[ booking_id ]] has been completed successfully.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'provider' => [
                    'de' => [
                        'subject' => 'Wallet-Zahlung erhalten — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet bezahlt.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Wallet Payment Received — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>Booking #[[ booking_id ]] has been successfully paid via wallet.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'handyman' => [
                    'de' => [
                        'subject' => 'Wallet-Zahlung — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet durchgeführt.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Wallet Payment — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>The wallet payment of [[ amount ]] for booking #[[ booking_id ]] has been completed.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],

                'user' => [
                    'de' => [
                        'subject' => 'Zahlung erfolgreich — Buchung #[[ booking_id ]]',
                        'body'    => '<p>Hallo [[ customer_name ]],</p>
<p>Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über Ihr Wallet durchgeführt.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ amount ]]</li>
  <li> </li>
  ' . $support_de . '
</ul>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payment Successful — Booking #[[ booking_id ]]',
                        'body'    => '<p>Hello [[ customer_name ]],</p>
<p>Your wallet payment of [[ amount ]] for booking #[[ booking_id ]] has been completed successfully.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Booking Date: [[ booking_date ]]</li>
  <li>Booking Time: [[ booking_time ]]</li>
  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ amount ]]</li>
  <li> </li>
  ' . $support_en . '
</ul>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // job_requested
            // =================================================================
            'job_requested' => [
                'admin' => [
                    'de' => [
                        'subject' => 'Neue Jobanfrage #[[ job_id ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie informieren, dass eine neue Jobanfrage auf Persotel veröffentlicht wurde.</p>
<p><strong>Auftrag #[[ job_id ]]</strong></p>
<p><strong>Auftraggeber:</strong> [[ customer_name ]]</p>
<p><strong>Jobauftrag:</strong> [[ job_request_name ]]</p>
<p><strong>Startdatum:</strong> [[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong> [[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong> [[ jo_brequest_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong> [[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong> [[ job_request_created_at ]]</p>
<p>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'New Job Request #[[ job_id ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>A new job request has been published on Frobster.</p>
<p><strong>Job #[[ job_id ]]</strong></p>
<p><strong>Customer:</strong> [[ customer_name ]]</p>
<p><strong>Job Title:</strong> [[ job_request_name ]]</p>
<p><strong>Start Date:</strong> [[ job_request_start_date ]]</p>
<p><strong>End Date:</strong> [[ job_request_end_date ]]</p>
<p><strong>Location:</strong> [[ jo_brequest_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong> [[ job_request_amount ]]</p>
<p><strong>Created:</strong> [[ job_request_created_at ]]</p>
<p>You can view and manage this request in your admin panel.</p>
<p>If you have any questions, please contact our support team at info@frobster.com.</p>' . $footer_en,
                    ],
                ],
                'provider' => [
                    'de' => [
                        'subject' => 'Neue Jobanfrage passend zu Ihren Leistungen',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Eine neue Jobanfrage wurde auf Persotel veröffentlicht, die zu Ihren Dienstleistungen passen könnte.</p>
<p><strong>Auftrag #[[ job_request_id ]]</strong></p>
<p><strong>Auftraggeber:</strong> [[ customer_name ]]</p>
<p><strong>Jobauftrag:</strong> [[ job_request_name ]]</p>
<p><strong>Startdatum:</strong> [[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong> [[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong> [[ jo_brequest_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong> [[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong> [[ job_request_created_at ]]</p>
<p>Wenn dieser Auftrag zu Ihren Fähigkeiten passt, melden Sie sich bei Persotel an und reichen Sie Ihr Angebot ein.</p>
<p><strong><a href="[[ link ]]" style="color:#2563eb;">Job ansehen und Angebot abgeben</a></strong></p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'New Job Request Matching Your Services',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>A new job request has been published on Frobster that may match your services.</p>
<p><strong>Job #[[ job_request_id ]]</strong></p>
<p><strong>Customer:</strong> [[ customer_name ]]</p>
<p><strong>Job Title:</strong> [[ job_request_name ]]</p>
<p><strong>Start Date:</strong> [[ job_request_start_date ]]</p>
<p><strong>End Date:</strong> [[ job_request_end_date ]]</p>
<p><strong>Location:</strong> [[ jo_brequest_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong> [[ job_request_amount ]]</p>
<p><strong>Created:</strong> [[ job_request_created_at ]]</p>
<p>If this job matches your skills, log in to Frobster and submit your bid.</p>
<p><strong><a href="[[ link ]]" style="color:#2563eb;">View Job &amp; Submit Bid</a></strong></p>
<p>If you have any questions, please contact our support team at info@frobster.com.</p>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // provider_payout
            // =================================================================
            'provider_payout' => [
                'provider' => [
                    'de' => [
                        'subject' => 'Auszahlung erhalten',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitzuteilen, dass eine Auszahlung in Höhe von [[ amount ]] erfolgreich auf Ihr Konto überwiesen wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payout Received',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that a payout of [[ amount ]] has been successfully processed to your account.</p>
<p>If you have any questions, please contact our support team at info@frobster.com.</p>' . $footer_en,
                    ],
                ],
                'admin' => [
                    'de' => [
                        'subject' => 'Auszahlung verarbeitet — [[ provider_name ]]',
                        'body'    => '<p>Hallo [[ admin_name ]],</p>
<p>Eine Auszahlung in Höhe von [[ amount ]] wurde erfolgreich an [[ provider_name ]] verarbeitet.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payout Processed — [[ provider_name ]]',
                        'body'    => '<p>Hello [[ admin_name ]],</p>
<p>A payout of [[ amount ]] has been successfully processed to [[ provider_name ]].</p>' . $footer_en,
                    ],
                ],
            ],

            // =================================================================
            // handyman_payout
            // =================================================================
            'handyman_payout' => [
                'handyman' => [
                    'de' => [
                        'subject' => 'Auszahlung erhalten',
                        'body'    => '<p>Hallo [[ handyman_name ]],</p>
<p>Wir freuen uns, Ihnen mitzuteilen, dass eine Auszahlung in Höhe von [[ amount ]] von [[ provider_name ]] erfolgreich auf Ihr Konto überwiesen wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payout Received',
                        'body'    => '<p>Hello [[ handyman_name ]],</p>
<p>We are pleased to inform you that a payout of [[ amount ]] from [[ provider_name ]] has been successfully processed to your account.</p>
<p>If you have any questions, please contact our support team at info@frobster.com.</p>' . $footer_en,
                    ],
                ],
                'provider' => [
                    'de' => [
                        'subject' => 'Auszahlung verarbeitet — [[ handyman_name ]]',
                        'body'    => '<p>Hallo [[ provider_name ]],</p>
<p>Eine Auszahlung in Höhe von [[ amount ]] wurde erfolgreich an [[ handyman_name ]] verarbeitet.</p>
<p>&nbsp;</p>' . $footer_de,
                    ],
                    'en' => [
                        'subject' => 'Payout Processed — [[ handyman_name ]]',
                        'body'    => '<p>Hello [[ provider_name ]],</p>
<p>A payout of [[ amount ]] has been successfully processed to [[ handyman_name ]].</p>' . $footer_en,
                    ],
                ],
            ],

        ];
    }
}

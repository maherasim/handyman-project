<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $bookingReceivedEn = '<p>Hello [[ admin_name ]],</p>
                                  <p>We want to inform you that a new booking request has been submitted by a customer.</p>
                                  <p>Please find the full details below, including all relevant information needed to process and respond to the request accordingly.</p>
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
                                  </ul>
                                  <ul>
                                  <li>You can view and manage this request in your admin panel.</li>
                                  <li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
                                  </ul>
                                  <p>Best regards,</p>
                                  <p>Your Frobster-Team</p>
                                  <p>Website: frobster.com</p>
                                  <p>Email: info@frobster.com</p>';

        $bookingReceivedDe = '<p>Hallo [[ admin_name ]],</p>
                                  <p>Wir mochten Sie daruber informieren, dass eine neue Buchungsanfrage von einem Kunden eingereicht wurde.</p>
                                  <p>Nachfolgend finden Sie alle Details, einschliesslich aller relevanten Informationen, die zur Bearbeitung und Beantwortung der Anfrage erforderlich sind.</p>
                                  <p><strong>Buchungsdetails:</strong></p>
                                  <ul>
                                  <li>Buchungs-ID: #[[ booking_id ]]</li>
                                  <li>Kundenname: [[ customer_name ]]</li>
                                  <li>Anbieter: [[ provider_name ]]</li>
                                  <li>Gebuchte Dienstleistung: [[ booking_services_name ]]</li>
                                  <li>Serviceort: [[ city_id ]] - [[ country_id ]]</li>
                                  <li>Buchungsdatum: [[ booking_date ]]</li>
                                  <li>Buchungszeit: [[ booking_time ]]</li>
                                  <li>Betrag: [[ total_amount ]]</li>
                                  </ul>
                                  <ul>
                                  <li>Sie konnen diese Anfrage in Ihrem Admin-Panel ansehen und verwalten.</li>
                                  <li>Wenn Sie Fragen haben oder weitere Unterstutzung benotigen, kontaktieren Sie bitte unser Support-Team unter: info@frobster.com</li>
                                  </ul>
                                  <p>Mit freundlichen Grussen,</p>
                                  <p>Ihr Frobster-Team</p>
                                  <p>Website: frobster.com</p>
                                  <p>E-Mail: info@frobster.com</p>';

        $bookingAssignedEn = '<p>Hello [[ handyman_name ]],</p>
                                  <p>You have been assigned to manage a booking. Please be prepared to provide service for [[ booking_services_name ]].</p>
                                  <p>&nbsp;</p>
                                  <p><strong>Booking Details:</strong></p>
                                  <ul>
                                  <li>Booking ID: #[[ booking_id ]]</li>
                                  <li>Customer Name: [[ customer_name ]]</li>
                                  <li>Employer: [[ provider_name ]]</li>
                                  <li>Service Booked: [[ booking_services_name ]]</li>
                                  <li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
                                  <li>Booking Date: [[ booking_date ]]</li>
                                  <li>Booking Time: [[ booking_time ]]</li>
                                  </ul>
                                  <ul>
                                  <li>You can view and manage this request in your admin panel.</li>
                                  <li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
                                  </ul>
                                  <p>Best regards,</p>
                                  <p>Your Frobster-Team</p>
                                  <p>Website: frobster.com</p>
                                  <p>Email: info@frobster.com</p>';

        $bookingAssignedDe = '<p>Hallo [[ handyman_name ]],</p>
                                  <p>Ihnen wurde eine Buchung zur Bearbeitung zugewiesen. Bitte seien Sie darauf vorbereitet, die Dienstleistung fur [[ booking_services_name ]] zu erbringen.</p>
                                  <p>&nbsp;</p>
                                  <p><strong>Buchungsdetails:</strong></p>
                                  <ul>
                                  <li>Buchungs-ID: #[[ booking_id ]]</li>
                                  <li>Kundenname: [[ customer_name ]]</li>
                                  <li>Arbeitgeber: [[ provider_name ]]</li>
                                  <li>Gebuchte Dienstleistung: [[ booking_services_name ]]</li>
                                  <li>Serviceort: [[ city_id ]] - [[ country_id ]]</li>
                                  <li>Buchungsdatum: [[ booking_date ]]</li>
                                  <li>Buchungszeit: [[ booking_time ]]</li>
                                  </ul>
                                  <ul>
                                  <li>Sie konnen diese Anfrage in Ihrem Admin-Panel ansehen und verwalten.</li>
                                  <li>Wenn Sie Fragen haben oder weitere Unterstutzung benotigen, kontaktieren Sie bitte unser Support-Team unter: info@frobster.com</li>
                                  </ul>
                                  <p>Mit freundlichen Grussen,</p>
                                  <p>Ihr Frobster-Team</p>
                                  <p>Website: frobster.com</p>
                                  <p>E-Mail: info@frobster.com</p>';

        $bookingAssignedProviderEn = str_replace('Hello [[ handyman_name ]]', 'Hello [[ provider_name ]]', $bookingAssignedEn);
        $bookingAssignedProviderDe = str_replace('Hallo [[ handyman_name ]]', 'Hallo [[ provider_name ]]', $bookingAssignedDe);

        $this->upsertMailTemplateContent('add_booking', 'admin', 'en', 'New Booking Received', $bookingReceivedEn);
        $this->upsertMailTemplateContent('add_booking', 'provider', 'en', 'New Booking Received', $bookingReceivedEn);
        $this->upsertMailTemplateContent('add_booking', 'admin', 'de', 'Neue Buchung erhalten', $bookingReceivedDe);
        $this->upsertMailTemplateContent('add_booking', 'provider', 'de', 'Neue Buchung erhalten', $bookingReceivedDe);

        $this->upsertMailTemplateContent('assigned_booking', 'handyman', 'en', 'Booking Assigned!', $bookingAssignedEn);
        $this->upsertMailTemplateContent('assigned_booking', 'provider', 'en', 'Booking Assigned!', $bookingAssignedProviderEn);
        $this->upsertMailTemplateContent('assigned_booking', 'handyman', 'de', 'Buchung zugewiesen!', $bookingAssignedDe);
        $this->upsertMailTemplateContent('assigned_booking', 'provider', 'de', 'Buchung zugewiesen!', $bookingAssignedProviderDe);

        $this->upsertConstant('notification_param_button', 'city_id', 'City');
        $this->upsertConstant('notification_param_button', 'country_id', 'Country');
        $this->upsertConstant('notification_param_button', 'total_amount', 'Total Amount');
    }

    public function down(): void
    {
        $templateIds = DB::table('mail_templates')
            ->whereIn('type', ['add_booking', 'assigned_booking'])
            ->pluck('id');

        DB::table('mail_template_content_mappings')
            ->whereIn('template_id', $templateIds)
            ->where('language', 'de')
            ->whereIn('user_type', ['admin', 'provider', 'handyman'])
            ->delete();
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

    private function upsertConstant(string $type, string $value, string $name): void
    {
        DB::table('constants')->updateOrInsert(
            [
                'type' => $type,
                'value' => $value,
            ],
            [
                'name' => $name,
                'status' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
};

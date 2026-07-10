-- =============================================================================
-- Restore plain-text email templates for both EN and DE locales.
-- Run on the live DB to replace colorful/wrong templates with the correct format.
-- Safe to re-run: uses upsert (UPDATE if exists, INSERT if not).
-- =============================================================================
SET NAMES utf8mb4;

DROP PROCEDURE IF EXISTS upsert_mail_template_content;
DELIMITER $$
CREATE PROCEDURE upsert_mail_template_content(
    IN p_type VARCHAR(255),
    IN p_user_type VARCHAR(255),
    IN p_language VARCHAR(10),
    IN p_subject TEXT,
    IN p_body LONGTEXT
)
BEGIN
    DECLARE v_template_id BIGINT DEFAULT NULL;
    SELECT id INTO v_template_id FROM mail_templates WHERE type = p_type LIMIT 1;
    IF v_template_id IS NOT NULL THEN
        IF EXISTS (
            SELECT 1 FROM mail_template_content_mappings
            WHERE template_id = v_template_id AND user_type = p_user_type AND language = p_language LIMIT 1
        ) THEN
            UPDATE mail_template_content_mappings
            SET subject = p_subject, template_detail = p_body, updated_at = NOW()
            WHERE template_id = v_template_id AND user_type = p_user_type AND language = p_language;
        ELSE
            INSERT INTO mail_template_content_mappings
                (template_id, language, notification_link, notification_message, user_type, status, subject, template_detail, created_at, updated_at)
            VALUES
                (v_template_id, p_language, '', '', p_user_type, 1, p_subject, p_body, NOW(), NOW());
        END IF;
    END IF;
END$$
DELIMITER ;

-- ---------------------------------------------------------------------------
-- Shared snippets
-- ---------------------------------------------------------------------------
SET @de_manage = '<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de </li>';

SET @de_footer = '<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>';

SET @en_manage = '<li> </li>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>';

SET @en_footer = '<p>&nbsp;</p>
<p>Best regards,</p>
<p>&nbsp;</p>
<p>Your Frobster-Team</p>
<p>Web: https://frobster.com</p>
<p>E-Mail: info@frobster.com</p>';

-- ===========================================================================
-- 1. add_booking  (new booking received by provider / admin)
-- ===========================================================================
SET @add_booking_de_provider = CONCAT(
'<p>Hallo [[ provider_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer);

SET @add_booking_de_admin = REPLACE(@add_booking_de_provider,
    'Hallo [[ provider_name ]]', 'Hallo [[ admin_name ]]');

CALL upsert_mail_template_content('add_booking', 'provider', 'de', 'Neue Buchung erhalten', @add_booking_de_provider);
CALL upsert_mail_template_content('add_booking', 'admin',    'de', 'Neue Buchung erhalten', @add_booking_de_admin);

SET @add_booking_en_provider = CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>Below you will find the booking details for a new booking request from [[ customer_name ]].</p>
<p>Please review the booking details and confirm the request in a timely manner.</p>
<p>&nbsp;</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer);

SET @add_booking_en_admin = REPLACE(@add_booking_en_provider,
    'Hello [[ provider_name ]]', 'Hello [[ admin_name ]]');

CALL upsert_mail_template_content('add_booking', 'provider', 'en', 'New Booking Received', @add_booking_en_provider);
CALL upsert_mail_template_content('add_booking', 'admin',    'en', 'New Booking Received', @add_booking_en_admin);

-- ===========================================================================
-- 2. assigned_booking  (handyman assigned / customer notified / provider notified)
-- ===========================================================================

-- handyman DE
CALL upsert_mail_template_content('assigned_booking', 'handyman', 'de', 'Buchung zugewiesen!', CONCAT(
'<p>Hallo [[ handyman_name ]],</p>
<p>Ihnen wurde die Durchführung einer Buchung zugewiesen. Bitte bereiten Sie sich darauf vor, <strong>[[ booking_services_name ]]</strong> auszuführen.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>',
@de_manage,
'
</ul>', @de_footer));

-- customer/user DE
CALL upsert_mail_template_content('assigned_booking', 'user', 'de', 'Buchung zugewiesen!', CONCAT(
'<p>Hallo [[ customer_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer));

-- provider DE
CALL upsert_mail_template_content('assigned_booking', 'provider', 'de', 'Buchung zugewiesen!', CONCAT(
'<p>Hallo [[ provider_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer));

-- handyman EN
CALL upsert_mail_template_content('assigned_booking', 'handyman', 'en', 'Booking Assigned!', CONCAT(
'<p>Hello [[ handyman_name ]],</p>
<p>You have been assigned to carry out a booking. Please prepare to provide <strong>[[ booking_services_name ]]</strong>.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Employer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>',
@en_manage,
'
</ul>', @en_footer));

-- customer/user EN
CALL upsert_mail_template_content('assigned_booking', 'user', 'en', 'Booking Assigned!', CONCAT(
'<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that your booking #[[ booking_id ]] has been assigned to [[ assignee_name ]].</p>
<p>&nbsp;</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Assigned To: [[ assignee_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer));

-- provider EN
CALL upsert_mail_template_content('assigned_booking', 'provider', 'en', 'Booking Assigned!', CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>You have been assigned to carry out booking #[[ booking_id ]]. Please prepare to provide <strong>[[ booking_services_name ]]</strong>.</p>
<p>&nbsp;</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer));

-- ===========================================================================
-- 3. update_booking_status
-- ===========================================================================

-- admin DE
CALL upsert_mail_template_content('update_booking_status', 'admin', 'de', 'Buchungsstatus aktualisiert', CONCAT(
'<p>Hallo [[ admin_name ]],</p>
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
</ul>', @de_footer));

-- provider DE
CALL upsert_mail_template_content('update_booking_status', 'provider', 'de', 'Buchungsstatus aktualisiert', CONCAT(
'<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer));

-- handyman DE
CALL upsert_mail_template_content('update_booking_status', 'handyman', 'de', 'Buchungsstatus aktualisiert', CONCAT(
'<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Arbeitgeber: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>',
@de_manage,
'
</ul>', @de_footer));

-- customer/user DE
CALL upsert_mail_template_content('update_booking_status', 'user', 'de', 'Buchungsstatus aktualisiert', CONCAT(
'<p>Hallo [[ customer_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer));

-- admin EN
CALL upsert_mail_template_content('update_booking_status', 'admin', 'en', 'Booking Status Updated', CONCAT(
'<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
<p>&nbsp;</p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>
</ul>', @en_footer));

-- provider EN
CALL upsert_mail_template_content('update_booking_status', 'provider', 'en', 'Booking Status Updated', CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer));

-- handyman EN
CALL upsert_mail_template_content('update_booking_status', 'handyman', 'en', 'Booking Status Updated', CONCAT(
'<p>Hello [[ handyman_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Employer: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>',
@en_manage,
'
</ul>', @en_footer));

-- customer/user EN
CALL upsert_mail_template_content('update_booking_status', 'user', 'en', 'Booking Status Updated', CONCAT(
'<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer));

-- ===========================================================================
-- 4. cancel_booking
-- ===========================================================================
SET @cancel_de_base = CONCAT(
'<p>Hallo [[ admin_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer);

CALL upsert_mail_template_content('cancel_booking', 'admin', 'de', 'Buchung storniert', @cancel_de_base);

CALL upsert_mail_template_content('cancel_booking', 'provider', 'de', 'Buchung storniert', REPLACE(
    REPLACE(@cancel_de_base, 'Hallo [[ admin_name ]]', 'Hallo [[ provider_name ]]'),
    'storniert wurde.</p>', 'storniert wurde. Bitte prüfen Sie die Details und ergreifen Sie gegebenenfalls erforderliche Maßnahmen.</p>'));

CALL upsert_mail_template_content('cancel_booking', 'handyman', 'de', 'Buchung storniert', REPLACE(
    REPLACE(
        REPLACE(@cancel_de_base, 'Hallo [[ admin_name ]]', 'Hallo [[ handyman_name ]]'),
        'Auftragnehmer: [[ provider_name ]]</li>', 'Arbeitgeber: [[ provider_name ]]</li>'),
    '<li>Betrag: [[ total_amount ]]</li>', ''));

CALL upsert_mail_template_content('cancel_booking', 'user', 'de', 'Buchung storniert', REPLACE(
    @cancel_de_base, 'Hallo [[ admin_name ]]', 'Hallo [[ customer_name ]]'));

SET @cancel_en_base = CONCAT(
'<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] for [[ booking_services_name ]] has been cancelled by [[ cancelled_user_name ]].</p>
<p>&nbsp;</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_name ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer);

CALL upsert_mail_template_content('cancel_booking', 'admin', 'en', 'Booking Cancelled', @cancel_en_base);

CALL upsert_mail_template_content('cancel_booking', 'provider', 'en', 'Booking Cancelled', REPLACE(
    REPLACE(@cancel_en_base, 'Hello [[ admin_name ]]', 'Hello [[ provider_name ]]'),
    'cancelled.</p>', 'cancelled. Please review the details and take any necessary action.</p>'));

CALL upsert_mail_template_content('cancel_booking', 'handyman', 'en', 'Booking Cancelled', REPLACE(
    REPLACE(
        REPLACE(@cancel_en_base, 'Hello [[ admin_name ]]', 'Hello [[ handyman_name ]]'),
        'Provider: [[ provider_name ]]</li>', 'Employer: [[ provider_name ]]</li>'),
    '<li>Amount: [[ total_amount ]]</li>', ''));

CALL upsert_mail_template_content('cancel_booking', 'user', 'en', 'Booking Cancelled', REPLACE(
    @cancel_en_base, 'Hello [[ admin_name ]]', 'Hello [[ customer_name ]]'));

-- ===========================================================================
-- 5. payment_message_status
-- ===========================================================================
SET @payment_de_base = CONCAT(
'<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf: "[[ payment_status ]]" geändert wurde.</p>
<p>&nbsp;</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>',
@de_manage,
'
</ul>', @de_footer);

CALL upsert_mail_template_content('payment_message_status', 'user',     'de', 'Zahlungsstatus aktualisiert', @payment_de_base);
CALL upsert_mail_template_content('payment_message_status', 'admin',    'de', 'Zahlungsstatus aktualisiert', REPLACE(@payment_de_base, 'Hallo [[ customer_name ]]', 'Hallo [[ admin_name ]]'));
CALL upsert_mail_template_content('payment_message_status', 'provider', 'de', 'Zahlungsstatus aktualisiert', REPLACE(@payment_de_base, 'Hallo [[ customer_name ]]', 'Hallo [[ provider_name ]]'));
CALL upsert_mail_template_content('payment_message_status', 'handyman', 'de', 'Zahlungsstatus aktualisiert', REPLACE(
    REPLACE(@payment_de_base, 'Hallo [[ customer_name ]]', 'Hallo [[ handyman_name ]]'),
    '<li>Betrag: [[ total_amount ]]</li>', ''));

SET @payment_en_base = CONCAT(
'<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that the payment status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to: "[[ payment_status ]]".</p>
<p>&nbsp;</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Service: [[ booking_services_names ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ total_amount ]]</li>',
@en_manage,
'
</ul>', @en_footer);

CALL upsert_mail_template_content('payment_message_status', 'user',     'en', 'Payment Status Updated', @payment_en_base);
CALL upsert_mail_template_content('payment_message_status', 'admin',    'en', 'Payment Status Updated', REPLACE(@payment_en_base, 'Hello [[ customer_name ]]', 'Hello [[ admin_name ]]'));
CALL upsert_mail_template_content('payment_message_status', 'provider', 'en', 'Payment Status Updated', REPLACE(@payment_en_base, 'Hello [[ customer_name ]]', 'Hello [[ provider_name ]]'));
CALL upsert_mail_template_content('payment_message_status', 'handyman', 'en', 'Payment Status Updated', REPLACE(
    REPLACE(@payment_en_base, 'Hello [[ customer_name ]]', 'Hello [[ handyman_name ]]'),
    '<li>Amount: [[ total_amount ]]</li>', ''));

-- ===========================================================================
-- 6. wallet_payout_transfer / provider_payout / handyman_payout
-- ===========================================================================
SET @payout_de_provider = CONCAT(
'<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>',
@de_footer);

CALL upsert_mail_template_content('wallet_payout_transfer', 'admin',    'de', 'Wallet-Auszahlung',
    CONCAT('<p>Hallo [[ admin_name ]],</p><p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>', @de_footer));
CALL upsert_mail_template_content('wallet_payout_transfer', 'provider', 'de', 'Auszahlung erhalten', @payout_de_provider);
CALL upsert_mail_template_content('wallet_payout_transfer', 'handyman', 'de', 'Auszahlung erhalten', REPLACE(@payout_de_provider, '[[ provider_name ]]', '[[ handyman_name ]]'));
CALL upsert_mail_template_content('provider_payout',        'admin',    'de', 'Auszahlung verarbeitet',
    CONCAT('<p>Hallo [[ admin_name ]],</p><p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>', @de_footer));
CALL upsert_mail_template_content('provider_payout',        'provider', 'de', 'Auszahlung erhalten', @payout_de_provider);
CALL upsert_mail_template_content('handyman_payout',        'provider', 'de', 'Auszahlung verarbeitet', @payout_de_provider);
CALL upsert_mail_template_content('handyman_payout',        'handyman', 'de', 'Auszahlung erhalten', REPLACE(@payout_de_provider, '[[ provider_name ]]', '[[ handyman_name ]]'));

SET @payout_en_provider = CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that a payout of [[ pay_amount ]] has been successfully processed.</p>
<p>If you have any questions or need further assistance, please contact our support team at: info@frobster.com.</p>',
@en_footer);

CALL upsert_mail_template_content('wallet_payout_transfer', 'admin',    'en', 'Wallet Payout',
    CONCAT('<p>Hello [[ admin_name ]],</p><p>We would like to inform you that [[ pay_amount ]] has been successfully paid out to [[ user_name ]].</p>', @en_footer));
CALL upsert_mail_template_content('wallet_payout_transfer', 'provider', 'en', 'Payout Received', @payout_en_provider);
CALL upsert_mail_template_content('wallet_payout_transfer', 'handyman', 'en', 'Payout Received', REPLACE(@payout_en_provider, '[[ provider_name ]]', '[[ handyman_name ]]'));
CALL upsert_mail_template_content('provider_payout',        'admin',    'en', 'Payout Processed',
    CONCAT('<p>Hello [[ admin_name ]],</p><p>We would like to inform you that [[ pay_amount ]] has been successfully paid out to [[ user_name ]].</p>', @en_footer));
CALL upsert_mail_template_content('provider_payout',        'provider', 'en', 'Payout Received', @payout_en_provider);
CALL upsert_mail_template_content('handyman_payout',        'provider', 'en', 'Payout Processed', @payout_en_provider);
CALL upsert_mail_template_content('handyman_payout',        'handyman', 'en', 'Payout Received', REPLACE(@payout_en_provider, '[[ provider_name ]]', '[[ handyman_name ]]'));

-- ===========================================================================
-- 7. wallet_top_up
-- ===========================================================================
CALL upsert_mail_template_content('wallet_top_up', 'admin', 'de', 'Wallet aufgeladen', CONCAT(
'<p>Hallo [[ admin_name ]],</p>
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
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>',
@de_footer));

SET @wallet_top_up_de_provider = CONCAT(
'<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitzuteilen, dass [[ credit_debit_amount ]] Ihrem Wallet gutgeschrieben wurden.</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: [[ provider_name ]]</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktionsdatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionszeit: [[ wallet_transaction_time ]]</li>',
@de_manage,
'
</ul>', @de_footer);

CALL upsert_mail_template_content('wallet_top_up', 'provider', 'de', 'Wallet aufgeladen', @wallet_top_up_de_provider);
CALL upsert_mail_template_content('wallet_top_up', 'user',     'de', 'Wallet aufgeladen', REPLACE(
    REPLACE(@wallet_top_up_de_provider,
        'Hallo [[ provider_name ]]', 'Hallo [[ customer_name ]]'),
    'dass [[ credit_debit_amount ]] Ihrem Wallet gutgeschrieben wurden.',
    'Ihr Wallet wurde erfolgreich mit [[ credit_debit_amount ]] aufgeladen.'));

CALL upsert_mail_template_content('wallet_top_up', 'admin', 'en', 'Wallet Top-Up', CONCAT(
'<p>Hello [[ admin_name ]],</p>
<p>[[ customer_name ]] has topped up their wallet with [[ credit_debit_amount ]].</p>
<p>&nbsp;</p>
<p><strong>Transaction Details:</strong></p>
<ul>
  <li>Customer: [[ customer_name ]]</li>
  <li>Transaction ID: [[ wallet_transaction_id ]]</li>
  <li>Transaction Type: [[ wallet_transaction_type ]]</li>
  <li>Amount: [[ wallet_amount ]]</li>
  <li>Transaction Date: [[ wallet_transaction_date ]]</li>
  <li>Transaction Time: [[ wallet_transaction_time ]]</li>
</ul>
<p>&nbsp;</p>
<p>If you have any questions or need further assistance, please feel free to contact us.</p>',
@en_footer));

SET @wallet_top_up_en_provider = CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that [[ credit_debit_amount ]] has been credited to your wallet.</p>
<p>&nbsp;</p>
<p><strong>Transaction Details:</strong></p>
<ul>
  <li>Account: [[ provider_name ]]</li>
  <li>Transaction ID: [[ wallet_transaction_id ]]</li>
  <li>Transaction Type: [[ wallet_transaction_type ]]</li>
  <li>Amount: [[ wallet_amount ]]</li>
  <li>Transaction Date: [[ wallet_transaction_date ]]</li>
  <li>Transaction Time: [[ wallet_transaction_time ]]</li>',
@en_manage,
'
</ul>', @en_footer);

CALL upsert_mail_template_content('wallet_top_up', 'provider', 'en', 'Wallet Top-Up', @wallet_top_up_en_provider);
CALL upsert_mail_template_content('wallet_top_up', 'user',     'en', 'Wallet Top-Up', REPLACE(
    REPLACE(@wallet_top_up_en_provider,
        'Hello [[ provider_name ]]', 'Hello [[ customer_name ]]'),
    'that [[ credit_debit_amount ]] has been credited to your wallet.',
    'Your wallet has been successfully topped up with [[ credit_debit_amount ]].'));

-- ===========================================================================
-- 8. wallet_refund
-- ===========================================================================
CALL upsert_mail_template_content('wallet_refund', 'admin', 'de', 'Wallet-Rückerstattung', CONCAT(
'<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] erbrachte Service für [[ customer_name ]] storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden vorgenommen.</p>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>',
@de_footer));

SET @refund_de_provider = CONCAT(
'<p>Hallo [[ provider_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von Ihnen für [[ customer_name ]] erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden veranlasst.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>',
@de_manage,
'
</ul>', @de_footer);

CALL upsert_mail_template_content('wallet_refund', 'provider', 'de', 'Wallet-Rückerstattung', @refund_de_provider);
CALL upsert_mail_template_content('wallet_refund', 'user',     'de', 'Wallet-Rückerstattung', REPLACE(
    REPLACE(@refund_de_provider,
        'Hallo [[ provider_name ]]', 'Hallo [[ customer_name ]]'),
    'der von Ihnen für [[ customer_name ]] erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden veranlasst.',
    'der von [[ provider_name ]] für Sie erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] Ihrem Wallet gutgeschrieben.'));

CALL upsert_mail_template_content('wallet_refund', 'admin', 'en', 'Wallet Refund', CONCAT(
'<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that the service provided by [[ provider_name ]] for [[ customer_name ]] has been cancelled. A refund of [[ refund_amount ]] has been issued to the customer.</p>
<p>&nbsp;</p>
<p>If you have any questions or need further assistance, please feel free to contact us.</p>',
@en_footer));

SET @refund_en_provider = CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that the service you provided for [[ customer_name ]] has been cancelled. A refund of [[ refund_amount ]] has been issued to the customer.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Cancelled Service: [[ booking_services_names ]]</li>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>',
@en_manage,
'
</ul>', @en_footer);

CALL upsert_mail_template_content('wallet_refund', 'provider', 'en', 'Wallet Refund', @refund_en_provider);
CALL upsert_mail_template_content('wallet_refund', 'user',     'en', 'Wallet Refund', REPLACE(
    REPLACE(@refund_en_provider,
        'Hello [[ provider_name ]]', 'Hello [[ customer_name ]]'),
    'the service you provided for [[ customer_name ]] has been cancelled. A refund of [[ refund_amount ]] has been issued to the customer.',
    'the service provided by [[ provider_name ]] has been cancelled. A refund of [[ refund_amount ]] has been credited to your wallet.'));

-- ===========================================================================
-- 9. paid_with_wallet
-- ===========================================================================
SET @paid_de_admin = CONCAT(
'<p>Hallo [[ admin_name ]],</p>
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
  <li>Betrag: [[ amount ]]</li>',
@de_manage,
'
</ul>', @de_footer);

CALL upsert_mail_template_content('paid_with_wallet', 'admin',    'de', 'Wallet-Zahlung erfolgreich', @paid_de_admin);
CALL upsert_mail_template_content('paid_with_wallet', 'provider', 'de', 'Wallet-Zahlung erfolgreich', REPLACE(
    REPLACE(@paid_de_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ provider_name ]]'),
    '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.',
    'Die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet bezahlt. Bitte prüfen Sie die untenstehenden Buchungsdetails.'));
CALL upsert_mail_template_content('paid_with_wallet', 'handyman', 'de', 'Wallet-Zahlung erfolgreich', REPLACE(
    REPLACE(@paid_de_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ handyman_name ]]'),
    '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.',
    'Die Zahlung für die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet durchgeführt.'));
CALL upsert_mail_template_content('paid_with_wallet', 'user',     'de', 'Wallet-Zahlung erfolgreich', REPLACE(
    REPLACE(@paid_de_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ customer_name ]]'),
    '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.',
    'Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über Ihr Wallet durchgeführt.'));

SET @paid_en_admin = CONCAT(
'<p>Hello [[ admin_name ]],</p>
<p>#[[ booking_id ]] – The wallet payment of [[ amount ]] was completed successfully. Please review the details below.</p>
<p><strong>Booking Details:</strong></p>
<ul>
  <li>Booking ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Customer: [[ customer_name ]]</li>
  <li>Provider: [[ provider_name ]]</li>
  <li>Date: [[ booking_date ]]</li>
  <li>Time: [[ booking_time ]]</li>
  <li>Location: [[ city_id ]] - [[ country_id ]]</li>
  <li>Amount: [[ amount ]]</li>',
@en_manage,
'
</ul>', @en_footer);

CALL upsert_mail_template_content('paid_with_wallet', 'admin',    'en', 'Wallet Payment Successful', @paid_en_admin);
CALL upsert_mail_template_content('paid_with_wallet', 'provider', 'en', 'Wallet Payment Successful', REPLACE(
    REPLACE(@paid_en_admin, 'Hello [[ admin_name ]]', 'Hello [[ provider_name ]]'),
    '#[[ booking_id ]] – The wallet payment of [[ amount ]] was completed successfully. Please review the details below.',
    'Booking #[[ booking_id ]] has been paid successfully via wallet. Please review the booking details below.'));
CALL upsert_mail_template_content('paid_with_wallet', 'handyman', 'en', 'Wallet Payment Successful', REPLACE(
    REPLACE(@paid_en_admin, 'Hello [[ admin_name ]]', 'Hello [[ handyman_name ]]'),
    '#[[ booking_id ]] – The wallet payment of [[ amount ]] was completed successfully. Please review the details below.',
    'The wallet payment for booking #[[ booking_id ]] has been completed successfully.'));
CALL upsert_mail_template_content('paid_with_wallet', 'user',     'en', 'Wallet Payment Successful', REPLACE(
    REPLACE(@paid_en_admin, 'Hello [[ admin_name ]]', 'Hello [[ customer_name ]]'),
    '#[[ booking_id ]] – The wallet payment of [[ amount ]] was completed successfully. Please review the details below.',
    'Your wallet payment of [[ amount ]] for booking #[[ booking_id ]] was completed successfully.'));

-- ===========================================================================
-- 10. job_requested
-- ===========================================================================
CALL upsert_mail_template_content('job_requested', 'admin', 'de', 'Neue Jobanfrage auf [[ company_name ]]', CONCAT(
'<p>Hallo [[ admin_name ]],</p>
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
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>',
@de_footer));

CALL upsert_mail_template_content('job_requested', 'provider', 'de', 'Neue Jobanfrage auf [[ company_name ]]', CONCAT(
'<p>Hallo [[ provider_name ]],</p>
<p>Eine neue Jobanfrage wurde auf [[ company_name ]] veröffentlicht, die zu Ihren Dienstleistungen passen könnte.</p>
<p><strong>Auftragsdetails:</strong></p>
<p><strong>Auftrag #[[ job_request_id ]]</strong></p>
<p><strong>Auftraggeber:</strong><br>[[ customer_name ]]</p>
<p><strong>Jobauftrag:</strong><br>[[ job_request_name ]]</p>
<p><strong>Start Datum:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong><br>[[ job_request_created_at ]]</p>
<p>Wenn dieser Auftrag zu Ihren Fähigkeiten passt, melden Sie sich bei [[ company_name ]] an, sehen Sie sich die vollständigen Details an und reichen Sie Ihr Angebot ein.</p>
<p><strong><a href="[[ link ]]">Job ansehen und Angebot abgeben</a></strong></p>
<p>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>',
@de_footer));

CALL upsert_mail_template_content('job_requested', 'admin', 'en', 'New Job Request on [[ company_name ]]', CONCAT(
'<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that a new job request has been posted on [[ company_name ]].</p>
<p><strong>Job Details:</strong></p>
<p><strong>Job #[[ job_id ]]</strong></p>
<p><strong>Customer:</strong><br>[[ customer_name ]]</p>
<p><strong>Job Title:</strong><br>[[ job_request_name ]]</p>
<p><strong>Start Date:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>End Date:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Location:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Created At:</strong><br>[[ job_request_created_at ]]</p>
<p>You can view and manage this request in your admin panel.</p>
<p>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com.</p>',
@en_footer));

CALL upsert_mail_template_content('job_requested', 'provider', 'en', 'New Job Request on [[ company_name ]]', CONCAT(
'<p>Hello [[ provider_name ]],</p>
<p>A new job request has been posted on [[ company_name ]] that may match your services.</p>
<p><strong>Job Details:</strong></p>
<p><strong>Job #[[ job_request_id ]]</strong></p>
<p><strong>Customer:</strong><br>[[ customer_name ]]</p>
<p><strong>Job Title:</strong><br>[[ job_request_name ]]</p>
<p><strong>Start Date:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>End Date:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Location:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Created At:</strong><br>[[ job_request_created_at ]]</p>
<p>If this job matches your skills, log in to [[ company_name ]] or open the app, view the full details and submit your offer.</p>
<p><strong><a href="[[ link ]]">View Job and Submit Offer</a></strong></p>
<p>You can view and manage this request in your admin panel.</p>
<p>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com.</p>',
@en_footer));

-- ===========================================================================
-- Ensure city_id / country_id / total_amount appear as param buttons
-- ===========================================================================
UPDATE constants SET name = 'City',         status = 1, updated_at = NOW() WHERE type = 'notification_param_button' AND value = 'city_id';
UPDATE constants SET name = 'Country',      status = 1, updated_at = NOW() WHERE type = 'notification_param_button' AND value = 'country_id';
UPDATE constants SET name = 'Total Amount', status = 1, updated_at = NOW() WHERE type = 'notification_param_button' AND value = 'total_amount';

DROP PROCEDURE IF EXISTS upsert_mail_template_content;

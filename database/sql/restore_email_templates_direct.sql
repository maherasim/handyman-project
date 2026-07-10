-- =============================================================================
-- Restore plain-text email templates — direct INSERT/UPDATE, no stored procedure.
-- Based on actual DB dump: mail_template_content_mappings (IDs 59-118, EN only).
-- This script:
--   1. Fixes EN rows: "Hallo"→"Hello", contact@→info@, FROBSTERR→Frobster-Team, {{ $var }}→[[ var ]]
--   2. Inserts all DE rows (none exist yet) — covering ALL template types
-- Safe to re-run: deletes existing DE rows first, then inserts fresh.
-- =============================================================================
SET NAMES utf8mb4;
SET SQL_MODE = '';

-- ---------------------------------------------------------------------------
-- PART 1: Fix existing EN rows
-- ---------------------------------------------------------------------------

-- Fix "Hallo [[ provider_name ]]" → "Hello [[ provider_name ]]" in EN rows
UPDATE mail_template_content_mappings SET template_detail = REPLACE(template_detail, '<p>Hallo [[ provider_name ]],</p>', '<p>Hello [[ provider_name ]],</p>'), updated_at = NOW() WHERE language = 'en' AND template_detail LIKE '%<p>Hallo [[ provider_name ]],</p>%';

-- Fix "Hallo {{ $admin_name }}" and similar "Hallo" patterns in EN rows
UPDATE mail_template_content_mappings SET template_detail = REPLACE(REPLACE(template_detail, '<p>Hallo {{ $admin_name }},</p>', '<p>Hello [[ admin_name ]],</p>'), '<p>Hallo {{ $provider_name }},</p>', '<p>Hello [[ provider_name ]],</p>'), updated_at = NOW() WHERE language = 'en' AND template_detail LIKE '%<p>Hallo {%';

-- Fix contact@frobster.com → info@frobster.com in EN rows
UPDATE mail_template_content_mappings SET template_detail = REPLACE(template_detail, 'contact@frobster.com', 'info@frobster.com'), updated_at = NOW() WHERE language = 'en' AND template_detail LIKE '%contact@frobster.com%';

-- Fix "FROBSTERR Team" / "FROBSTER Team" → "Frobster-Team" in EN rows
UPDATE mail_template_content_mappings SET template_detail = REPLACE(REPLACE(template_detail, 'FROBSTERR Team', 'Frobster-Team'), 'FROBSTER Team', 'Frobster-Team'), updated_at = NOW() WHERE language = 'en' AND (template_detail LIKE '%FROBSTERR Team%' OR template_detail LIKE '%FROBSTER Team%');

-- Fix job_requested EN (ids 89, 90): replace {{ $var }} with [[ var ]]
UPDATE mail_template_content_mappings SET
    template_detail = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
        template_detail,
        '{{ $admin_name }}',   '[[ admin_name ]]'),
        '{{ $provider_name }}','[[ provider_name ]]'),
        '{{ $company_name }}', '[[ company_name ]]'),
        '{{ $job_id }}',       '[[ job_id ]]'),
        '{{ $job_request_name }}',  '[[ job_request_name ]]'),
        '{{ $customer_name }}',     '[[ customer_name ]]'),
        '{{ $job_request_start_date }}', '[[ job_request_start_date ]]'),
        '{{ $job_request_end_date }}',   '[[ job_request_end_date ]]'),
        '{{ $job_request_city }}',  '[[ job_request_city ]]'),
        '{{ $job_country }}',       '[[ job_country ]]'),
        '{{ $job_request_amount }}','[[ job_request_amount ]]'),
        '{{ $job_request_created_at }}', '[[ job_request_created_at ]]'),
    updated_at = NOW()
WHERE language = 'en' AND template_detail LIKE '%{{ $%';

-- ---------------------------------------------------------------------------
-- PART 2: Remove any existing DE rows for booking-related templates
--         (template_id 22-30, 34, 38) so we can insert fresh ones cleanly
-- ---------------------------------------------------------------------------
DELETE FROM mail_template_content_mappings
WHERE language = 'de'
  AND template_id IN (22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43);

-- ---------------------------------------------------------------------------
-- PART 3: Insert DE rows
-- ---------------------------------------------------------------------------

-- template_id 22 = add_booking
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(22, 'de', 'admin', 1, 'Neue Buchung erhalten',
'<p>Hallo [[ admin_name ]],</p>
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(22, 'de', 'provider', 1, 'Neue Buchung erhalten',
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 23 = assigned_booking
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(23, 'de', 'handyman', 1, 'Buchung zugewiesen!',
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
<li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(23, 'de', 'user', 1, 'Buchung zugewiesen!',
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(23, 'de', 'provider', 1, 'Buchung zugewiesen!',
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 24 = update_booking_status
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(24, 'de', 'admin', 1, 'Buchungsstatus aktualisiert',
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
<li> </li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(24, 'de', 'provider', 1, 'Buchungsstatus aktualisiert',
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(24, 'de', 'handyman', 1, 'Buchungsstatus aktualisiert',
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
<li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(24, 'de', 'user', 1, 'Buchungsstatus aktualisiert',
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 25 = cancel_booking
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(25, 'de', 'admin', 1, 'Buchung storniert',
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(25, 'de', 'provider', 1, 'Buchung storniert',
'<p>Hallo [[ provider_name ]],</p>
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(25, 'de', 'handyman', 1, 'Buchung storniert',
'<p>Hallo [[ handyman_name ]],</p>
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
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(25, 'de', 'user', 1, 'Buchung storniert',
'<p>Hallo [[ customer_name ]],</p>
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 26 = payment_message_status
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(26, 'de', 'user', 1, 'Zahlungsstatus aktualisiert',
'<p>Hallo [[ customer_name ]],</p>
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(26, 'de', 'admin', 1, 'Zahlungsstatus aktualisiert',
'<p>Hallo [[ admin_name ]],</p>
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(26, 'de', 'provider', 1, 'Zahlungsstatus aktualisiert',
'<p>Hallo [[ provider_name ]],</p>
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
<li>Betrag: [[ total_amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(26, 'de', 'handyman', 1, 'Zahlungsstatus aktualisiert',
'<p>Hallo [[ handyman_name ]],</p>
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
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 27 = wallet_payout_transfer
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(27, 'de', 'admin', 1, 'Wallet-Auszahlung',
'<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(27, 'de', 'provider', 1, 'Auszahlung erhalten',
'<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(27, 'de', 'handyman', 1, 'Auszahlung erhalten',
'<p>Hallo [[ handyman_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 28 = wallet_top_up
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(28, 'de', 'admin', 1, 'Wallet aufgeladen',
'<p>Hallo [[ admin_name ]],</p>
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
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(28, 'de', 'provider', 1, 'Wallet aufgeladen',
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
<li>Transaktionszeit: [[ wallet_transaction_time ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(28, 'de', 'user', 1, 'Wallet aufgeladen',
'<p>Hallo [[ customer_name ]],</p>
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
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 29 = wallet_refund
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(29, 'de', 'admin', 1, 'Wallet-Rückerstattung',
'<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] erbrachte Service für [[ customer_name ]] storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden vorgenommen.</p>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(29, 'de', 'provider', 1, 'Wallet-Rückerstattung',
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
<li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(29, 'de', 'user', 1, 'Wallet-Rückerstattung',
'<p>Hallo [[ customer_name ]],</p>
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
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 30 = paid_with_wallet
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(30, 'de', 'admin', 1, 'Wallet-Zahlung erfolgreich',
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
<li>Betrag: [[ amount ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(30, 'de', 'provider', 1, 'Wallet-Zahlung erfolgreich',
'<p>Hallo [[ provider_name ]],</p>
<p>Die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet bezahlt. Bitte prüfen Sie die untenstehenden Buchungsdetails und verwalten Sie die Buchung entsprechend.</p>
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
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(30, 'de', 'handyman', 1, 'Wallet-Zahlung erfolgreich',
'<p>Hallo [[ handyman_name ]],</p>
<p>Die Zahlung für die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet durchgeführt.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
<li>Buchungs-ID: #[[ booking_id ]]</li>
<li>Service: [[ booking_services_name ]]</li>
<li>Kunde: [[ customer_name ]]</li>
<li>Auftragnehmer: [[ provider_name ]]</li>
<li>Datum: [[ booking_date ]]</li>
<li>Uhrzeit: [[ booking_time ]]</li>
<li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(30, 'de', 'user', 1, 'Wallet-Zahlung erfolgreich',
'<p>Hallo [[ customer_name ]],</p>
<p>Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über Ihr Wallet durchgeführt.</p>
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
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de</li>
</ul>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 31 = job_requested
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(31, 'de', 'admin', 1, 'Neue Jobanfrage auf [[ company_name ]]',
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
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(31, 'de', 'provider', 1, 'Neue Jobanfrage auf [[ company_name ]]',
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
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 34 = provider_payout
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(34, 'de', 'provider', 1, 'Auszahlung erhalten',
'<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(34, 'de', 'admin', 1, 'Auszahlung verarbeitet',
'<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 38 = handyman_payout
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(38, 'de', 'handyman', 1, 'Auszahlung erhalten',
'<p>Hallo [[ handyman_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(38, 'de', 'provider', 1, 'Auszahlung verarbeitet',
'<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 32 = provider_bid_placed (new bid received — sent to user/customer)
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(32, 'de', 'user', 1, 'Neues Angebot erhalten',
'<p>Hallo [[ customer_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass Sie ein neues Angebot in Höhe von [[ bid_amount ]] von [[ provider_name ]] für Ihre Jobanfrage erhalten haben.</p>
<p><strong>Auftragsdetails:</strong></p>
<p><strong>Auftrag #[[ job_id ]]</strong></p>
<p><strong>Jobauftrag:</strong><br>[[ job_request_name ]]</p>
<p><strong>Auftragnehmer:</strong><br>[[ provider_name ]]</p>
<p><strong>Start Datum:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong><br>[[ job_request_created_at ]]</p>
<p>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 33 = bid_accepted (bid accepted — sent to provider)
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(33, 'de', 'provider', 1, 'Angebot angenommen',
'<p>Hallo [[ provider_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass Ihr Angebot in Höhe von [[ job_price ]] für die Jobanfrage von [[ customer_name ]] angenommen wurde.</p>
<p><strong>Auftragsdetails:</strong></p>
<p><strong>Auftrag #[[ job_id ]]</strong></p>
<p><strong>Jobauftrag:</strong><br>[[ job_request_name ]]</p>
<p><strong>Auftraggeber:</strong><br>[[ customer_name ]]</p>
<p><strong>Start Datum:</strong><br>[[ job_request_start_date ]]</p>
<p><strong>Enddatum:</strong><br>[[ job_request_end_date ]]</p>
<p><strong>Einsatzort:</strong><br>[[ job_request_city ]] - [[ job_country ]]</p>
<p><strong>Budget:</strong><br>[[ job_request_amount ]]</p>
<p><strong>Erstellt am:</strong><br>[[ job_request_created_at ]]</p>
<p>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 35 = subscription
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(35, 'de', 'admin', 1, 'Neues Abonnement aktiviert',
'<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass [[ provider_name ]] ein neues Abonnement abgeschlossen hat: [[ plan_name ]].</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(35, 'de', 'provider', 1, 'Neues Abonnement aktiviert',
'<p>Hallo [[ provider_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass Ihr neues Abonnement — [[ plan_name ]] — erfolgreich aktiviert wurde.</p>
<p><strong>Abonnementdetails:</strong></p>
<ul>
<li>Neuer Plan: [[ plan_name ]]</li>
<li>Buchungsdatum: [[ plan_booking_date ]]</li>
<li>Startdatum: [[ plan_start_date ]]</li>
<li>Enddatum: [[ plan_end_date ]]</li>
<li>Plangebühr: [[ plan_amount_fees ]]</li>
</ul>
<p>Sie können Ihren Plan jederzeit in Ihrem Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 36 = new_user / registration
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(36, 'de', 'admin', 1, 'Neue Benutzerregistrierung',
'<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass [[ user_name ]] sich soeben bei uns registriert hat.</p>
<p><strong>Registrierungsdetails:</strong></p>
<ul>
<li>Benutzer: [[ user_name ]]</li>
<li>Registrierungsdatum: [[ registration_date ]]</li>
<li>Standort: [[ city_id ]] - [[ country_id ]]</li>
<li>Benutzertyp: [[ user_type ]]</li>
<li>Beruf: [[ user_occupation ]]</li>
</ul>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(36, 'de', 'provider', 1, 'Willkommen bei Persotel!',
'<p>Hallo [[ provider_name ]],</p>
<p>Herzlich willkommen im Persotel-Netzwerk! Wir freuen uns, Sie in unserer Gemeinschaft aus vertrauenswürdigen Agenturen, Dienstleistern, Freelancern und Handwerkern begrüßen zu dürfen.</p>
<p>Ihre Fachkenntnisse und Dienstleistungen spielen eine wichtige Rolle dabei, unseren Kunden beim Erreichen ihrer Ziele zu helfen. Wir freuen uns darauf, Sie auf unserem Weg zum Erfolg zu begleiten.</p>
<p>Sie können Ihre Aktivitäten, Dienstleistungsangebote, Buchungen und Jobanfragen jederzeit über Ihr Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder beim Einstieg Unterstützung benötigen, steht Ihnen unser Support-Team gerne zur Verfügung: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(36, 'de', 'handyman', 1, 'Willkommen bei Persotel!',
'<p>Hallo [[ handyman_name ]],</p>
<p>Herzlich willkommen im Persotel-Netzwerk! Wir freuen uns, Sie in unserer Gemeinschaft aus vertrauenswürdigen Agenturen, Dienstleistern, Freelancern und Handwerkern begrüßen zu dürfen.</p>
<p>Ihre Fachkenntnisse und Dienstleistungen spielen eine wichtige Rolle dabei, unseren Kunden beim Erreichen ihrer Ziele zu helfen. Wir freuen uns darauf, Sie auf unserem Weg zum Erfolg zu begleiten.</p>
<p>Sie können Ihre Aktivitäten jederzeit über Ihr Admin-Panel einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder beim Einstieg Unterstützung benötigen, steht Ihnen unser Support-Team gerne zur Verfügung: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(36, 'de', 'user', 1, 'Willkommen bei Persotel!',
'<p>Hallo [[ customer_name ]],</p>
<p>Herzlich willkommen bei Persotel! Wir freuen uns, Sie in unserer Gemeinschaft begrüßen zu dürfen.</p>
<p>Unsere Plattform verbindet Sie mit vertrauenswürdigen Fachleuten und Dienstleistungen und erleichtert es Ihnen, Ihre Ziele zu erreichen. Wir freuen uns darauf, Ihre Erfahrungen auf unserer Plattform zu bereichern.</p>
<p>Sie können Ihre Aktivitäten, Buchungen und Jobanfragen jederzeit über Ihr Benutzerkonto einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder beim Einstieg Unterstützung benötigen, steht Ihnen unser Support-Team gerne zur Verfügung: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 37 = money_withdrawn (wallet withdrawal)
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(37, 'de', 'admin', 1, 'Auszahlungsanfrage eingegangen',
'<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass <strong>[[ user_name ]]</strong> erfolgreich eine Wallet-Auszahlungsanfrage über <strong>[[ amount ]]</strong> eingereicht hat.</p>
<p>Bitte überprüfen Sie die Transaktionsdetails im Admin-Panel, falls weitere Maßnahmen erforderlich sind.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an unser Support-Team: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(37, 'de', 'provider', 1, 'Auszahlungsanfrage eingereicht',
'<p>Hallo [[ provider_name ]],</p>
<p>Hiermit bestätigen wir, dass Ihre Wallet-Auszahlungsanfrage über <strong>[[ amount ]]</strong> erfolgreich eingereicht wurde.</p>
<p>Sie können die Transaktionsdetails einsehen und den Status Ihrer Auszahlung jederzeit über Ihr Konto-Dashboard verfolgen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an unser Support-Team: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(37, 'de', 'user', 1, 'Auszahlungsanfrage eingereicht',
'<p>Hallo [[ customer_name ]],</p>
<p>Hiermit bestätigen wir, dass Ihre Wallet-Auszahlungsanfrage über <strong>[[ amount ]]</strong> erfolgreich eingereicht wurde.</p>
<p>Sie können die Transaktionsdetails einsehen und den Status Ihrer Auszahlung jederzeit über Ihr Konto-Dashboard verfolgen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an unser Support-Team: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 39 = helpdesk new query
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(39, 'de', 'admin', 1, 'Neue Anfrage eingegangen',
'<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass eine neue Anfrage über Persotel eingegangen ist.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Bitte melden Sie sich in Ihrem Admin-Dashboard an, um die vollständige Nachricht zu lesen und gegebenenfalls erforderliche Maßnahmen zu ergreifen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 40 = helpdesk query closed
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(40, 'de', 'admin', 1, 'Anfrage geschlossen',
'<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> geschlossen wurde.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Sie können sich in Ihrem Admin-Dashboard anmelden, um die Anfragedetails, den Schließungsverlauf und die zugehörige Korrespondenz einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(40, 'de', 'provider', 1, 'Anfrage geschlossen',
'<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> geschlossen wurde.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Sie können sich in Ihrem Dashboard anmelden, um die Anfragedetails einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(40, 'de', 'handyman', 1, 'Anfrage geschlossen',
'<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> geschlossen wurde.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Sie können sich in Ihrem Dashboard anmelden, um die Anfragedetails einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(40, 'de', 'user', 1, 'Anfrage geschlossen',
'<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> geschlossen wurde.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Sie können sich in Ihrem Dashboard anmelden, um die Anfragedetails einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 41 = helpdesk query replied
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(41, 'de', 'admin', 1, 'Neue Antwort auf Anfrage',
'<p>Hallo [[ receiver_name ]],</p>
<p>Hiermit informieren wir Sie, dass eine neue Antwort auf die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> eingegangen ist.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Bitte melden Sie sich in Ihrem Admin-Dashboard an, um die neueste Antwort einzusehen und das Gespräch bei Bedarf fortzusetzen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(41, 'de', 'provider', 1, 'Neue Antwort auf Anfrage',
'<p>Hallo [[ receiver_name ]],</p>
<p>Hiermit informieren wir Sie, dass eine neue Antwort auf die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> eingegangen ist.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Bitte melden Sie sich in Ihrem Dashboard an, um die neueste Antwort einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(41, 'de', 'handyman', 1, 'Neue Antwort auf Anfrage',
'<p>Hallo [[ receiver_name ]],</p>
<p>Hiermit informieren wir Sie, dass eine neue Antwort auf die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> eingegangen ist.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Bitte melden Sie sich in Ihrem Dashboard an, um die neueste Antwort einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(41, 'de', 'user', 1, 'Neue Antwort auf Anfrage',
'<p>Hallo [[ receiver_name ]],</p>
<p>Hiermit informieren wir Sie, dass eine neue Antwort auf die Anfrage <strong>#[[ helpdesk_id ]]</strong> von <strong>[[ sender_name ]]</strong> eingegangen ist.</p>
<p><strong>Absender:</strong> [[ sender_name ]]<br>
<strong>Betreff:</strong> [[ subject ]]</p>
<p>Bitte melden Sie sich in Ihrem Dashboard an, um die neueste Antwort einzusehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 42 = cancellation_charges
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(42, 'de', 'admin', 1, 'Stornogebühren',
'<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass für die Buchung <strong>#[[ booking_id ]]</strong> eine Stornogebühr von <strong>[[ paid_amount ]]</strong> erhoben wurde.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
<li><strong>Stornierte Buchungs-ID:</strong> #[[ booking_id ]]</li>
<li><strong>Stornierter Service:</strong> [[ booking_services_name ]]</li>
<li><strong>Kunde:</strong> [[ customer_name ]]</li>
<li><strong>Auftragnehmer:</strong> [[ provider_name ]]</li>
<li><strong>Buchungsbetrag:</strong> [[ amount ]]</li>
<li><strong>Stornogebühr:</strong> [[ paid_amount ]]</li>
</ul>
<p>Sie können die Details im Admin-Panel einsehen.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(42, 'de', 'user', 1, 'Stornogebühren',
'<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass für die Stornierung der Buchung <strong>#[[ booking_id ]]</strong> eine Stornogebühr von <strong>[[ paid_amount ]]</strong> von Ihrem Wallet abgebucht wurde.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
<li><strong>Stornierte Buchungs-ID:</strong> #[[ booking_id ]]</li>
<li><strong>Stornierter Service:</strong> [[ booking_services_name ]]</li>
<li><strong>Kunde:</strong> [[ customer_name ]]</li>
<li><strong>Auftragnehmer:</strong> [[ provider_name ]]</li>
<li><strong>Datum:</strong> [[ booking_date ]]</li>
<li><strong>Uhrzeit:</strong> [[ booking_time ]]</li>
<li><strong>Einsatzort:</strong> [[ city_id ]], [[ country_id ]]</li>
<li><strong>Buchungsbetrag:</strong> [[ amount ]]</li>
<li><strong>Stornogebühr:</strong> [[ paid_amount ]]</li>
</ul>
<p>Sie können Ihren Wallet-Transaktionsverlauf und Ihre Buchungsdetails jederzeit über Ihr Konto-Dashboard einsehen.</p>
<p>Falls Sie Fragen haben oder glauben, dass diese Gebühr fälschlicherweise erhoben wurde, wenden Sie sich bitte an unser Support-Team: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

-- template_id 43 = post_job_bid_status
INSERT INTO mail_template_content_mappings (template_id, language, user_type, status, subject, template_detail, notification_message, notification_link, created_at, updated_at) VALUES
(43, 'de', 'user', 1, 'Jobanfrage #[[ job_id ]] – Status geändert zu [[ bid_status ]]',
'<p>Hallo [[ customer_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass <strong>[[ provider_name ]]</strong> den Status Ihrer Jobanfrage <strong>#[[ job_id ]] - [[ job_name ]]</strong> auf <strong>[[ bid_status ]]</strong> aktualisiert hat.</p>
<p><strong>Auftragsdetails:</strong></p>
<ul>
<li><strong>Auftrags-ID:</strong> #[[ job_id ]]</li>
<li><strong>Jobauftrag:</strong> [[ job_request_name ]]</li>
<li><strong>Auftraggeber:</strong> [[ customer_name ]]</li>
<li><strong>Startdatum:</strong> [[ job_request_start_date ]]</li>
<li><strong>Enddatum:</strong> [[ job_request_end_date ]]</li>
<li><strong>Einsatzort:</strong> [[ job_request_city ]] - [[ job_country ]]</li>
<li><strong>Budget:</strong> [[ job_request_amount ]]</li>
<li><strong>Erstellt am:</strong> [[ job_request_created_at ]]</li>
<li><strong>Aktueller Status:</strong> [[ bid_status ]]</li>
</ul>
<p>Bitte überprüfen Sie die Angebotsseite für weitere Details und etwaige erforderliche Maßnahmen.</p>
<p>Sie können diese Jobanfrage jederzeit über Ihr Konto-Dashboard einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW()),

(43, 'de', 'provider', 1, 'Jobanfrage #[[ job_id ]] – Status geändert zu [[ bid_status ]]',
'<p>Hallo [[ provider_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass <strong>[[ customer_name ]]</strong> den Status Ihrer Jobanfrage <strong>#[[ job_id ]] - [[ job_name ]]</strong> auf <strong>[[ bid_status ]]</strong> aktualisiert hat.</p>
<p><strong>Auftragsdetails:</strong></p>
<ul>
<li><strong>Auftrags-ID:</strong> #[[ job_id ]]</li>
<li><strong>Jobauftrag:</strong> [[ job_request_name ]]</li>
<li><strong>Auftraggeber:</strong> [[ customer_name ]]</li>
<li><strong>Startdatum:</strong> [[ job_request_start_date ]]</li>
<li><strong>Enddatum:</strong> [[ job_request_end_date ]]</li>
<li><strong>Einsatzort:</strong> [[ job_request_city ]] - [[ job_country ]]</li>
<li><strong>Budget:</strong> [[ job_request_amount ]]</li>
<li><strong>Erstellt am:</strong> [[ job_request_created_at ]]</li>
<li><strong>Aktueller Status:</strong> [[ bid_status ]]</li>
</ul>
<p>Bitte überprüfen Sie die Angebotsseite für weitere Details und etwaige erforderliche Maßnahmen.</p>
<p>Sie können diese Jobanfrage jederzeit über Ihr Konto-Dashboard einsehen und verwalten.</p>
<p>Falls Sie Fragen haben oder Unterstützung benötigen, wenden Sie sich bitte an: info@persotel.de</p>
<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>',
'', '', NOW(), NOW());

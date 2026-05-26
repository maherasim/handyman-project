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

    SELECT id INTO v_template_id
    FROM mail_templates
    WHERE type = p_type
    LIMIT 1;

    IF v_template_id IS NOT NULL THEN
        IF EXISTS (
            SELECT 1
            FROM mail_template_content_mappings
            WHERE template_id = v_template_id
              AND user_type = p_user_type
              AND language = p_language
            LIMIT 1
        ) THEN
            UPDATE mail_template_content_mappings
            SET notification_link = '',
                notification_message = '',
                status = 1,
                subject = p_subject,
                template_detail = p_body,
                updated_at = NOW()
            WHERE template_id = v_template_id
              AND user_type = p_user_type
              AND language = p_language;
        ELSE
            INSERT INTO mail_template_content_mappings
                (template_id, language, notification_link, notification_message, user_type, status, subject, template_detail, created_at, updated_at)
            VALUES
                (v_template_id, p_language, '', '', p_user_type, 1, p_subject, p_body, NOW(), NOW());
        END IF;
    END IF;
END$$
DELIMITER ;

SET @manage = '<li> </li>
<li>Sie können diese Anfrage in Ihrem Admin-Panel einsehen und verwalten.</li>
<li>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de </li>';

SET @footer = '<p>&nbsp;</p>
<p>Mit freundlichen Grüßen,</p>
<p>&nbsp;</p>
<p>Ihr Persotel-Team</p>
<p>Web: https://persotel.de</p>
<p>E-Mail: info@persotel.de</p>';

CALL upsert_mail_template_content('add_booking', 'admin', 'de', 'Neue Buchung erhalten', CONCAT('<p>Hallo [[ admin_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('add_booking', 'provider', 'de', 'Neue Buchung erhalten', CONCAT('<p>Hallo [[ provider_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('assigned_booking', 'handyman', 'de', 'Buchung zugewiesen!', CONCAT('<p>Hallo [[ handyman_name ]],</p>
<p>Ihnen wurde die Durchführung einer Buchung zugewiesen. Bitte bereiten Sie sich darauf vor, <strong>[[ booking_services_name ]]</strong> auszuführen.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('assigned_booking', 'user', 'de', 'Buchung zugewiesen!', CONCAT('<p>Hallo [[ customer_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('assigned_booking', 'provider', 'de', 'Buchung zugewiesen!', CONCAT('<p>Hallo [[ provider_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('update_booking_status', 'admin', 'de', 'Buchungsstatus aktualisiert', CONCAT('<p>Hallo [[ admin_name ]],</p>
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
</ul>', @footer));

CALL upsert_mail_template_content('update_booking_status', 'provider', 'de', 'Buchungsstatus aktualisiert', CONCAT('<p>Hallo [[ provider_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('update_booking_status', 'handyman', 'de', 'Buchungsstatus aktualisiert', CONCAT('<p>Hallo [[ handyman_name ]],</p>
<p>Hiermit informieren wir Sie, dass sich der Status der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf [[ booking_status ]] geändert hat.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Service: [[ booking_services_name ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Arbeitgeber: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>', @manage, '
</ul>', @footer));

CALL upsert_mail_template_content('update_booking_status', 'user', 'de', 'Buchungsstatus aktualisiert', CONCAT('<p>Hallo [[ customer_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer));

SET @cancel_admin = CONCAT('<p>Hallo [[ admin_name ]],</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer);
CALL upsert_mail_template_content('cancel_booking', 'admin', 'de', 'Buchung storniert', @cancel_admin);
CALL upsert_mail_template_content('cancel_booking', 'provider', 'de', 'Buchung storniert', REPLACE(REPLACE(@cancel_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ provider_name ]]'), 'wurde.</p>', 'wurde. Bitte prüfen Sie die Details und ergreifen Sie gegebenenfalls erforderliche Maßnahmen.</p>'));
CALL upsert_mail_template_content('cancel_booking', 'handyman', 'de', 'Buchung storniert', REPLACE(REPLACE(REPLACE(@cancel_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ handyman_name ]]'), 'Auftragnehmer:', 'Arbeitgeber:'), '<li>Betrag: [[ total_amount ]]</li>', ''));
CALL upsert_mail_template_content('cancel_booking', 'user', 'de', 'Buchung storniert', REPLACE(@cancel_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ customer_name ]]'));

SET @payment_user = CONCAT('<p>Hallo [[ customer_name ]],</p>
<p>Hiermit informieren wir Sie, dass der Zahlungsstatus der Buchung #[[ booking_id ]] für [[ booking_services_name ]] auf: "[[ payment_status ]]" geändert wurde.</p>
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
  <li>Betrag: [[ total_amount ]]</li>', @manage, '
</ul>', @footer);
CALL upsert_mail_template_content('payment_message_status', 'user', 'de', 'Zahlungsstatus aktualisiert', @payment_user);
CALL upsert_mail_template_content('payment_message_status', 'admin', 'de', 'Zahlungsstatus aktualisiert', REPLACE(@payment_user, 'Hallo [[ customer_name ]]', 'Hallo [[ admin_name ]]'));
CALL upsert_mail_template_content('payment_message_status', 'provider', 'de', 'Zahlungsstatus aktualisiert', REPLACE(@payment_user, 'Hallo [[ customer_name ]]', 'Hallo [[ provider_name ]]'));
CALL upsert_mail_template_content('payment_message_status', 'handyman', 'de', 'Zahlungsstatus aktualisiert', REPLACE(REPLACE(@payment_user, 'Hallo [[ customer_name ]]', 'Hallo [[ handyman_name ]]'), '<li>Betrag: [[ total_amount ]]</li>', ''));

CALL upsert_mail_template_content('wallet_payout_transfer', 'admin', 'de', 'Wallet-Auszahlung', CONCAT('<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>', @footer));
SET @payout_provider = CONCAT('<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitteilen zu können, dass eine Auszahlung in Höhe von [[ pay_amount ]] erfolgreich verarbeitet wurde.</p>
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>', @footer);
CALL upsert_mail_template_content('wallet_payout_transfer', 'provider', 'de', 'Auszahlung erhalten', @payout_provider);
CALL upsert_mail_template_content('wallet_payout_transfer', 'handyman', 'de', 'Auszahlung erhalten', REPLACE(@payout_provider, '[[ provider_name ]]', '[[ handyman_name ]]'));
CALL upsert_mail_template_content('provider_payout', 'admin', 'de', 'Auszahlung verarbeitet', CONCAT('<p>Hallo [[ admin_name ]],</p>
<p>Hiermit informieren wir Sie, dass [[ pay_amount ]] erfolgreich an [[ user_name ]] ausgezahlt wurde.</p>', @footer));
CALL upsert_mail_template_content('provider_payout', 'provider', 'de', 'Auszahlung erhalten', @payout_provider);
CALL upsert_mail_template_content('handyman_payout', 'provider', 'de', 'Auszahlung verarbeitet', @payout_provider);
CALL upsert_mail_template_content('handyman_payout', 'handyman', 'de', 'Auszahlung erhalten', REPLACE(@payout_provider, '[[ provider_name ]]', '[[ handyman_name ]]'));

CALL upsert_mail_template_content('wallet_top_up', 'admin', 'de', 'Wallet aufgeladen', CONCAT('<p>Hallo [[ admin_name ]],</p>
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
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>', @footer));
SET @wallet_top_up_provider = CONCAT('<p>Hallo [[ provider_name ]],</p>
<p>Wir freuen uns, Ihnen mitzuteilen, dass [[ credit_debit_amount ]] Ihrem Wallet gutgeschrieben wurden.</p>
<p>&nbsp;</p>
<p><strong>Transaktionsdetails:</strong></p>
<ul>
  <li>Customer: [[ provider_name ]]</li>
  <li>Transaktions-ID: [[ wallet_transaction_id ]]</li>
  <li>Transaktionstyp: [[ wallet_transaction_type ]]</li>
  <li>Betrag: [[ wallet_amount ]]</li>
  <li>Transaktionsdatum: [[ wallet_transaction_date ]]</li>
  <li>Transaktionszeit: [[ wallet_transaction_time ]]</li>', @manage, '
</ul>', @footer);
CALL upsert_mail_template_content('wallet_top_up', 'provider', 'de', 'Wallet aufgeladen', @wallet_top_up_provider);
CALL upsert_mail_template_content('wallet_top_up', 'user', 'de', 'Wallet aufgeladen', REPLACE(REPLACE(@wallet_top_up_provider, 'Hallo [[ provider_name ]]', 'Hallo [[ customer_name ]]'), 'Wir freuen uns, Ihnen mitzuteilen, dass [[ credit_debit_amount ]] Ihrem Wallet gutgeschrieben wurden.', 'Ihr Wallet wurde erfolgreich mit [[ credit_debit_amount ]] aufgeladen.'));

CALL upsert_mail_template_content('wallet_refund', 'admin', 'de', 'Wallet-Rückerstattung', CONCAT('<p>Hallo [[ admin_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von [[ provider_name ]] gebuchter Service für [[ customer_name ]] storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden vorgenommen.</p>
<p>&nbsp;</p>
<p>Sollten Sie Fragen haben oder zusätzliche Unterstützung benötigen, kontaktieren Sie uns bitte jederzeit.</p>', @footer));
SET @refund_provider = CONCAT('<p>Hallo [[ provider_name ]],</p>
<p>Wir möchten Sie darüber informieren, dass der von Ihnen für [[ customer_name ]] erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden veranlasst.</p>
<p><strong>Buchungsdetails:</strong></p>
<ul>
  <li>Stornierter Service: [[ booking_services_names ]]</li>
  <li>Buchungs-ID: #[[ booking_id ]]</li>
  <li>Kunde: [[ customer_name ]]</li>
  <li>Auftragnehmer: [[ provider_name ]]</li>
  <li>Datum: [[ booking_date ]]</li>
  <li>Uhrzeit: [[ booking_time ]]</li>
  <li>Einsatzort: [[ city_id ]] - [[ country_id ]]</li>', @manage, '
</ul>', @footer);
CALL upsert_mail_template_content('wallet_refund', 'provider', 'de', 'Wallet-Rückerstattung', @refund_provider);
CALL upsert_mail_template_content('wallet_refund', 'user', 'de', 'Wallet-Rückerstattung', REPLACE(REPLACE(@refund_provider, 'Hallo [[ provider_name ]]', 'Hallo [[ customer_name ]]'), 'der von Ihnen für [[ customer_name ]] erbrachte Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] an den Kunden veranlasst.', 'der von [[ provider_name ]] für Sie gebuchter Service storniert wurde. Infolgedessen wurde eine Rückerstattung in Höhe von [[ refund_amount ]] Ihrem Wallet gutgeschrieben.'));

SET @paid_admin = CONCAT('<p>Hallo [[ admin_name ]],</p>
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
  <li>Betrag: [[ amount ]]</li>', @manage, '
</ul>', @footer);
CALL upsert_mail_template_content('paid_with_wallet', 'admin', 'de', 'Wallet-Zahlung erfolgreich', @paid_admin);
CALL upsert_mail_template_content('paid_with_wallet', 'provider', 'de', 'Wallet-Zahlung erfolgreich', REPLACE(REPLACE(@paid_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ provider_name ]]'), '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.', 'Die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet bezahlt. Bitte prüfen Sie die untenstehenden Buchungsdetails und verwalten Sie die Buchung entsprechend.'));
CALL upsert_mail_template_content('paid_with_wallet', 'handyman', 'de', 'Wallet-Zahlung erfolgreich', REPLACE(REPLACE(REPLACE(@paid_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ handyman_name ]]'), '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.', 'Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über das Wallet durchgeführt. Bitte prüfen Sie die untenstehenden Buchungsdetails und verwalten Sie die Buchung entsprechend.'), '<li>Betrag: [[ amount ]]</li>', '<li>Angestellte(r): [[ assignee_name ]]</li>'));
CALL upsert_mail_template_content('paid_with_wallet', 'user', 'de', 'Wallet-Zahlung erfolgreich', REPLACE(REPLACE(REPLACE(@paid_admin, 'Hallo [[ admin_name ]]', 'Hallo [[ customer_name ]]'), '#[[ booking_id ]] – Die Zahlung in Höhe von [[ amount ]] über das Wallet wurde erfolgreich durchgeführt. Bitte prüfen Sie die folgenden Details.', 'Die Zahlung in Höhe von [[ amount ]] für die Buchung #[[ booking_id ]] wurde erfolgreich über Ihr Wallet durchgeführt.'), '<li>Betrag: [[ amount ]]</li>', '<li>Angestellte(r): [[ assignee_name ]]</li>'));

CALL upsert_mail_template_content('job_requested', 'admin', 'de', 'Neue Jobanfrage auf [[ company_name ]]', CONCAT('<p>Hallo [[ admin_name ]],</p>
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
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>', @footer));

CALL upsert_mail_template_content('job_requested', 'provider', 'de', 'Neue Jobanfrage auf [[ company_name ]]', CONCAT('<p>Hallo [[ provider_name ]],</p>
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
<p>Falls Sie Fragen haben oder weitere Unterstützung benötigen, kontaktieren Sie bitte jederzeit unser Support-Team unter: info@persotel.de.</p>', @footer));

DROP PROCEDURE IF EXISTS upsert_mail_template_content;

SET NAMES utf8mb4;

DROP PROCEDURE IF EXISTS upsert_mail_template_content;
DROP PROCEDURE IF EXISTS upsert_constant;
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

CREATE PROCEDURE upsert_constant(
    IN p_type VARCHAR(255),
    IN p_value VARCHAR(255),
    IN p_name VARCHAR(255)
)
BEGIN
    IF EXISTS (
        SELECT 1 FROM constants WHERE type = p_type AND value = p_value LIMIT 1
    ) THEN
        UPDATE constants
        SET name = p_name,
            status = 1,
            updated_at = NOW()
        WHERE type = p_type
          AND value = p_value;
    ELSE
        INSERT INTO constants (type, value, name, status, created_at, updated_at)
        VALUES (p_type, p_value, p_name, 1, NOW(), NOW());
    END IF;
END$$
DELIMITER ;

SET @booking_received_en = '<p>Hello [[ admin_name ]],</p>
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

SET @booking_received_de = '<p>Hallo [[ admin_name ]],</p>
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

SET @booking_assigned_en = '<p>Hello [[ handyman_name ]],</p>
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

SET @booking_assigned_de = '<p>Hallo [[ handyman_name ]],</p>
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

CALL upsert_mail_template_content('add_booking', 'admin', 'en', 'New Booking Received', @booking_received_en);
CALL upsert_mail_template_content('add_booking', 'provider', 'en', 'New Booking Received', @booking_received_en);
CALL upsert_mail_template_content('add_booking', 'admin', 'de', 'Neue Buchung erhalten', @booking_received_de);
CALL upsert_mail_template_content('add_booking', 'provider', 'de', 'Neue Buchung erhalten', @booking_received_de);
CALL upsert_mail_template_content('assigned_booking', 'handyman', 'en', 'Booking Assigned!', @booking_assigned_en);
CALL upsert_mail_template_content('assigned_booking', 'provider', 'en', 'Booking Assigned!', REPLACE(@booking_assigned_en, 'Hello [[ handyman_name ]]', 'Hello [[ provider_name ]]'));
CALL upsert_mail_template_content('assigned_booking', 'handyman', 'de', 'Buchung zugewiesen!', @booking_assigned_de);
CALL upsert_mail_template_content('assigned_booking', 'provider', 'de', 'Buchung zugewiesen!', REPLACE(@booking_assigned_de, 'Hallo [[ handyman_name ]]', 'Hallo [[ provider_name ]]'));

CALL upsert_constant('notification_param_button', 'city_id', 'City');
CALL upsert_constant('notification_param_button', 'country_id', 'Country');
CALL upsert_constant('notification_param_button', 'total_amount', 'Total Amount');

DROP PROCEDURE IF EXISTS upsert_mail_template_content;
DROP PROCEDURE IF EXISTS upsert_constant;

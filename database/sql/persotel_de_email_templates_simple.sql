SET NAMES utf8mb4;

UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
    m.subject = 'Neue Buchung erhalten',
    m.template_detail = '<p>Hallo [[ provider_name ]],</p>
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
    m.updated_at = NOW()
WHERE t.type = 'add_booking'
  AND m.language = 'de'
  AND m.user_type IN ('admin', 'provider');

UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
    m.subject = 'Buchung zugewiesen!',
    m.template_detail = '<p>Hallo [[ handyman_name ]],</p>
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
    m.updated_at = NOW()
WHERE t.type = 'assigned_booking'
  AND m.language = 'de'
  AND m.user_type = 'handyman';

UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
    m.subject = 'Buchung zugewiesen!',
    m.template_detail = '<p>Hallo [[ customer_name ]],</p>
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
    m.updated_at = NOW()
WHERE t.type = 'assigned_booking'
  AND m.language = 'de'
  AND m.user_type = 'user';

UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
    m.subject = 'Buchung zugewiesen!',
    m.template_detail = '<p>Hallo [[ provider_name ]],</p>
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
    m.updated_at = NOW()
WHERE t.type = 'assigned_booking'
  AND m.language = 'de'
  AND m.user_type = 'provider';


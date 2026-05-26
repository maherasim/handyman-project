SET NAMES utf8mb4;

UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
    m.subject = 'New Booking Received',
    m.template_detail = '<p>Hello [[ admin_name ]],</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
</ul>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
    m.updated_at = NOW()
WHERE t.type = 'add_booking'
  AND m.language = 'en'
  AND m.user_type IN ('admin', 'provider');

UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
    m.subject = 'Booking Assigned!',
    m.template_detail = '<p>Hello [[ handyman_name ]],</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
</ul>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
    m.updated_at = NOW()
WHERE t.type = 'assigned_booking'
  AND m.language = 'en'
  AND m.user_type = 'handyman';

UPDATE constants
SET name = 'City', status = 1, updated_at = NOW()
WHERE type = 'notification_param_button' AND value = 'city_id';

UPDATE constants
SET name = 'Country', status = 1, updated_at = NOW()
WHERE type = 'notification_param_button' AND value = 'country_id';

UPDATE constants
SET name = 'Total Amount', status = 1, updated_at = NOW()
WHERE type = 'notification_param_button' AND value = 'total_amount';


-- =============================================================================
-- frobster.com (EN database) — restore exact plain-text template format
-- Updates all EN rows to the correct format using the exact template content.
-- Safe to re-run: uses UPDATE by template_id + user_type + language.
-- =============================================================================
SET NAMES utf8mb4;
SET SQL_MODE = '';

-- ---------------------------------------------------------------------------
-- template_id 22 = add_booking (New Booking Received)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'New Booking Received',
template_detail = '<p>Hello [[ admin_name ]],</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 22 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'New Booking Received',
template_detail = '<p>Hello [[ provider_name ]],</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 22 AND user_type = 'provider' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 23 = assigned_booking (Booking Assigned!)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Booking Assigned!',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>You have been assigned to manage a booking. Please be prepared to provide service for [[ booking_services_name ]].</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 23 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Assigned!',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that your booking #[[ booking_id ]] has been assigned to [[ assignee_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Assigned Staff: [[ assignee_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 23 AND user_type = 'user' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Assigned!',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] has been assigned to [[ assignee_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Assigned Staff: [[ assignee_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 23 AND user_type = 'provider' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 24 = update_booking_status (Booking Status Update)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Booking Status Update',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Assigned Staff: [[ assignee_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 24 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Status Update',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Assigned Staff: [[ assignee_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 24 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Status Update',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>We would like to inform you that the status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 24 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Status Update',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that the status of your booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to [[ booking_status ]].</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 24 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 25 = cancel_booking (Booking Cancelled)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Booking Cancelled',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] for [[ booking_services_name ]] has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_name ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 25 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Cancelled',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] for [[ booking_services_name ]] has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_name ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 25 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Cancelled',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] for [[ booking_services_name ]] has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_name ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Employer: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 25 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Booking Cancelled',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that your booking #[[ booking_id ]] for [[ booking_services_name ]] has been cancelled by [[ cancelled_user_name ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_name ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 25 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 26 = payment_message_status (Payment Status Update)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Payment Status Update',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that the payment status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to: "[[ payment_status ]]".</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 26 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payment Status Update',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that the payment status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to: "[[ payment_status ]]".</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 26 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payment Status Update',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>We would like to inform you that the payment status of booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to: "[[ payment_status ]]".</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 26 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payment Status Update',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that the payment status of your booking #[[ booking_id ]] for [[ booking_services_name ]] has been changed to: "[[ payment_status ]]".</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 26 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 27 = wallet_payout_transfer
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Wallet Payout',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that a payout of [[ pay_amount ]] has been successfully processed to [[ user_name ]].</p>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 27 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payout Received',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that a payout of [[ pay_amount ]] has been successfully processed.</p>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 27 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payout Received',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>We are pleased to inform you that a payout of [[ pay_amount ]] has been successfully processed.</p>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 27 AND user_type = 'handyman' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 28 = wallet_top_up
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Wallet Top-Up',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that [[ customer_name ]] has topped up their wallet with [[ credit_debit_amount ]].</p>
<p><strong>Transaction Details:</strong></p>
<ul>
<li>Customer: [[ customer_name ]]</li>
<li>Transaction ID: [[ wallet_transaction_id ]]</li>
<li>Transaction Type: [[ wallet_transaction_type ]]</li>
<li>Amount: [[ wallet_amount ]]</li>
<li>Transaction Date: [[ wallet_transaction_date ]]</li>
<li>Transaction Time: [[ wallet_transaction_time ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 28 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Wallet Top-Up',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that [[ credit_debit_amount ]] has been credited to your wallet.</p>
<p><strong>Transaction Details:</strong></p>
<ul>
<li>Customer: [[ provider_name ]]</li>
<li>Transaction ID: [[ wallet_transaction_id ]]</li>
<li>Transaction Type: [[ wallet_transaction_type ]]</li>
<li>Amount: [[ wallet_amount ]]</li>
<li>Transaction Date: [[ wallet_transaction_date ]]</li>
<li>Transaction Time: [[ wallet_transaction_time ]]</li>
</ul>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 28 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Wallet Top-Up',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We are pleased to inform you that [[ credit_debit_amount ]] has been credited to your wallet.</p>
<p><strong>Transaction Details:</strong></p>
<ul>
<li>Customer: [[ customer_name ]]</li>
<li>Transaction ID: [[ wallet_transaction_id ]]</li>
<li>Transaction Type: [[ wallet_transaction_type ]]</li>
<li>Amount: [[ wallet_amount ]]</li>
<li>Transaction Date: [[ wallet_transaction_date ]]</li>
<li>Transaction Time: [[ wallet_transaction_time ]]</li>
</ul>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 28 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 29 = wallet_refund
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Wallet Refund',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that the service provided for [[ customer_name ]] has been cancelled. As a result, a refund of [[ refund_amount ]] has been initiated to the customer.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_names ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 29 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Wallet Refund',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that the service you provided for [[ customer_name ]] has been cancelled. As a result, a refund of [[ refund_amount ]] has been initiated to the customer.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_names ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ total_amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 29 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Wallet Refund',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that the service booked for you by [[ provider_name ]] has been cancelled. As a result, a refund of [[ refund_amount ]] has been credited to your wallet.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_names ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ refund_amount ]]</li>
</ul>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 29 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 30 = paid_with_wallet
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Payment Paid For Booking',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that the payment of [[ amount ]] for booking #[[ booking_id ]] was successfully completed using the wallet.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 30 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payment Paid For Booking',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] has been successfully paid through the wallet.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ amount ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 30 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payment Paid For Booking',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>We would like to inform you that booking #[[ booking_id ]] has been successfully paid through the wallet.</p>
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
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 30 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payment Paid For Booking',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that your payment of [[ amount ]] for booking #[[ booking_id ]] has been successfully processed through the wallet.</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Service Booked: [[ booking_services_name ]]</li>
<li>Service Location: [[ city_id ]] - [[ country_id ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Amount: [[ amount ]]</li>
</ul>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 30 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 31 = job_requested (fix {{ $var }} and format)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'New job request on [[ company_name ]]',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that a new job request has been posted on [[ company_name ]].</p>
<p><strong>Job Request Details:</strong></p>
<p><strong>Job #[[ job_id ]]</strong></p>
<ul>
<li>Job Request: [[ job_request_name ]]</li>
<li>Customer: [[ customer_name ]]</li>
<li>Start Date: [[ job_request_start_date ]]</li>
<li>End Date: [[ job_request_end_date ]]</li>
<li>Location: [[ job_request_city ]] - [[ job_country ]]</li>
<li>Budget: [[ job_request_amount ]]</li>
<li>Created On: [[ job_request_created_at ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 31 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'New job on [[ company_name ]] – submit your bid',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>A new job request has been posted on [[ company_name ]] that may match your services.</p>
<p><strong>Job Request Details:</strong></p>
<p><strong>Job #[[ job_id ]]</strong></p>
<ul>
<li>Job Request: [[ job_request_name ]]</li>
<li>Customer: [[ customer_name ]]</li>
<li>Start Date: [[ job_request_start_date ]]</li>
<li>End Date: [[ job_request_end_date ]]</li>
<li>Location: [[ job_request_city ]] - [[ job_country ]]</li>
<li>Budget: [[ job_request_amount ]]</li>
<li>Created On: [[ job_request_created_at ]]</li>
</ul>
<li>If this matches your skills, log in to [[ company_name ]], view the full details, and submit your bid.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 31 AND user_type = 'provider' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 32 = provider_bid_placed (New Bid Received)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'New Bid Received',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that you have received a new bid of [[ bid_amount ]] from [[ provider_name ]] on your job request.</p>
<p><strong>Job Request Details:</strong></p>
<p><strong>Job #[[ job_id ]]</strong></p>
<ul>
<li>Job Request: [[ job_request_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Start Date: [[ job_request_start_date ]]</li>
<li>End Date: [[ job_request_end_date ]]</li>
<li>Location: [[ job_request_city ]] - [[ job_country ]]</li>
<li>Budget: [[ job_request_amount ]]</li>
<li>Created On: [[ job_request_created_at ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 32 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 33 = bid_accepted (Bid Accepted)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Bid Accepted',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that your bid of [[ job_price ]] for the job request has been accepted by [[ customer_name ]].</p>
<p><strong>Job Request Details:</strong></p>
<p><strong>Job #[[ job_id ]]</strong></p>
<ul>
<li>Job Request: [[ job_request_name ]]</li>
<li>Customer: [[ customer_name ]]</li>
<li>Start Date: [[ job_request_start_date ]]</li>
<li>End Date: [[ job_request_end_date ]]</li>
<li>Location: [[ job_request_city ]] - [[ job_country ]]</li>
<li>Budget: [[ job_request_amount ]]</li>
<li>Created On: [[ job_request_created_at ]]</li>
</ul>
<li>You can view and manage this request in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 33 AND user_type = 'provider' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 34 = provider_payout
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Payout Received',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that your payout of [[ amount ]] has been successfully processed.</p>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 34 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payout Processed',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that a payout of [[ amount ]] has been processed to [[ provider_name ]].</p>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 34 AND user_type = 'admin' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 35 = subscription
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'New Subscription Plan Activated',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that [[ provider_name ]] has subscribed to a new plan: [[ plan_name ]].</p>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 35 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'New Subscription Plan Activated',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that your new plan — [[ plan_name ]] — has been activated.</p>
<p><strong>Plan Details:</strong></p>
<ul>
<li>New Plan: [[ plan_name ]]</li>
<li>Booking Date: [[ plan_booking_date ]]</li>
<li>Start Date: [[ plan_start_date ]]</li>
<li>End Date: [[ plan_end_date ]]</li>
<li>Plan Fees: [[ plan_amount_fees ]]</li>
</ul>
<li>You can view and manage your plan in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 35 AND user_type = 'provider' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 36 = new_user / registration
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'New User Registration',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that [[ user_name ]] is now registered with us.</p>
<p><strong>Registration Details:</strong></p>
<ul>
<li>User: [[ user_name ]]</li>
<li>Registration Date: [[ registration_date ]]</li>
<li>Location: [[ city_id ]] - [[ country_id ]]</li>
<li>User Type: [[ user_type ]]</li>
<li>User Occupation: [[ user_occupation ]]</li>
</ul>
<li>You can view and manage this in your admin panel.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 36 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Welcome to Frobster!',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>Welcome to the Frobster Network! We are delighted to have you join our community of trusted agencies, providers, freelancers and handymen.</p>
<p>Your expertise and services will play an important role in helping our customers achieve their goals, and we are excited to support your success on our platform.</p>
<p>You can view and manage your activities, service offers, bookings and job requests at any time through your admin panel.</p>
<li>If you have any questions or need assistance getting started, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 36 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Welcome to Frobster!',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>Welcome to the Frobster Network! We are delighted to have you join our community of trusted agencies, providers, freelancers and handymen.</p>
<p>Your expertise and services will play an important role in helping our customers achieve their goals, and we are excited to support your success on our platform.</p>
<p>You can view and manage your activities at any time through your admin panel.</p>
<li>If you have any questions or need assistance getting started, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 36 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Welcome to Frobster!',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>Welcome to Frobster! We are excited to have you join our community.</p>
<p>Our platform is designed to connect you with trusted professionals and services, making it easier to achieve your goals. We look forward to helping you get the most out of your experience with us.</p>
<p>You can view and manage your activities, bookings and job requests at any time through your account.</p>
<li>If you have any questions or need assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 36 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 37 = money_withdrawn (wallet withdrawal)
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Money Withdrawn',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>This is to notify you that [[ user_name ]] has successfully submitted a wallet withdrawal request for [[ amount ]].</p>
<p>Please review the transaction details in the admin panel if any further action is required.</p>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 37 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Money Withdrawn',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>This is to confirm that your wallet withdrawal request for [[ amount ]] has been successfully submitted.</p>
<p>You can review the transaction details and monitor the status of your withdrawal through your account dashboard.</p>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 37 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Money Withdrawn',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>This is to confirm that your wallet withdrawal request for [[ amount ]] has been successfully submitted.</p>
<p>You can review the transaction details and monitor the status of your withdrawal through your account dashboard.</p>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 37 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 38 = handyman_payout
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Payout Received',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>We are pleased to inform you that you have received a payout of [[ amount ]] from [[ provider_name ]].</p>
<p>The payment has been successfully processed and credited to your account. You can review the transaction details in your dashboard at any time.</p>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 38 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Payout Processed',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We are pleased to inform you that your payout of [[ amount ]] has been successfully processed and transferred to [[ handyman_name ]].</p>
<p>You can view the transaction details and track the status of this payout at any time through your account dashboard.</p>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 38 AND user_type = 'provider' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 39/40/41 = helpdesk
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'New Query Received',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>We would like to inform you that a new inquiry has been submitted.</p>
<p><strong>Sender:</strong> [[ sender_name ]]<br>
<strong>Subject:</strong> [[ subject ]]</p>
<li>Please log in to your admin dashboard to review the full message and take any necessary action.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 39 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Query Closed',
template_detail = '<p>Hello [[ admin_name ]],</p>
<p>This is to notify you that inquiry #[[ helpdesk_id ]] has been closed by [[ sender_name ]].</p>
<p><strong>Sender:</strong> [[ sender_name ]]<br>
<strong>Subject:</strong> [[ subject ]]</p>
<li>You may log in to your admin dashboard to review the inquiry details and closure history.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 40 AND user_type = 'admin' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Query Closed',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>This is to notify you that inquiry #[[ helpdesk_id ]] has been closed by [[ sender_name ]].</p>
<p><strong>Sender:</strong> [[ sender_name ]]<br>
<strong>Subject:</strong> [[ subject ]]</p>
<li>You may log in to your dashboard to review the inquiry details.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 40 AND user_type = 'provider' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Query Closed',
template_detail = '<p>Hello [[ handyman_name ]],</p>
<p>This is to notify you that inquiry #[[ helpdesk_id ]] has been closed by [[ sender_name ]].</p>
<p><strong>Sender:</strong> [[ sender_name ]]<br>
<strong>Subject:</strong> [[ subject ]]</p>
<li>You may log in to your dashboard to review the inquiry details.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 40 AND user_type = 'handyman' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Query Closed',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>This is to notify you that inquiry #[[ helpdesk_id ]] has been closed by [[ sender_name ]].</p>
<p><strong>Sender:</strong> [[ sender_name ]]<br>
<strong>Subject:</strong> [[ subject ]]</p>
<li>You may log in to your dashboard to review the inquiry details.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 40 AND user_type = 'user' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Query Replied',
template_detail = '<p>Hello [[ receiver_name ]],</p>
<p>This is to notify you that a new reply has been received for inquiry #[[ helpdesk_id ]] from [[ sender_name ]].</p>
<p><strong>Sender:</strong> [[ sender_name ]]<br>
<strong>Subject:</strong> [[ subject ]]</p>
<li>Please log in to your dashboard to review the latest response and continue the conversation if necessary.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 41 AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 42 = cancellation_charges
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Cancellation Charges',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>This is to inform you that a cancellation charge of [[ paid_amount ]] has been deducted from your wallet for booking #[[ booking_id ]].</p>
<p><strong>Booking Details:</strong></p>
<ul>
<li>Booking ID: #[[ booking_id ]]</li>
<li>Cancelled Service: [[ booking_services_name ]]</li>
<li>Customer Name: [[ customer_name ]]</li>
<li>Provider: [[ provider_name ]]</li>
<li>Booking Date: [[ booking_date ]]</li>
<li>Booking Time: [[ booking_time ]]</li>
<li>Service Location: [[ city_id ]], [[ country_id ]]</li>
<li>Booking Amount: [[ amount ]]</li>
<li>Cancellation Charge: [[ paid_amount ]]</li>
</ul>
<li>You can view your wallet transaction history and booking details by logging in to your account dashboard.</li>
<li>If you believe this charge was applied in error or have any questions, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 42 AND user_type = 'user' AND language = 'en';

-- ---------------------------------------------------------------------------
-- template_id 43 = post_job_bid_status
-- ---------------------------------------------------------------------------
UPDATE mail_template_content_mappings SET
subject = 'Job Request #[[ job_id ]] – Status updated to [[ bid_status ]]',
template_detail = '<p>Hello [[ customer_name ]],</p>
<p>We would like to inform you that [[ provider_name ]] has updated the status of your job request #[[ job_id ]] - [[ job_name ]] to [[ bid_status ]].</p>
<p><strong>Job Request Details:</strong></p>
<ul>
<li>Job ID: #[[ job_id ]]</li>
<li>Job Request: [[ job_request_name ]]</li>
<li>Customer: [[ customer_name ]]</li>
<li>Start Date: [[ job_request_start_date ]]</li>
<li>End Date: [[ job_request_end_date ]]</li>
<li>Location: [[ job_request_city ]] - [[ job_country ]]</li>
<li>Budget: [[ job_request_amount ]]</li>
<li>Created On: [[ job_request_created_at ]]</li>
<li>Current Status: [[ bid_status ]]</li>
</ul>
<li>You can view and manage this job request through your account dashboard.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 43 AND user_type = 'user' AND language = 'en';

UPDATE mail_template_content_mappings SET
subject = 'Job Request #[[ job_id ]] – Status updated to [[ bid_status ]]',
template_detail = '<p>Hello [[ provider_name ]],</p>
<p>We would like to inform you that [[ customer_name ]] has updated the status of your job request #[[ job_id ]] - [[ job_name ]] to [[ bid_status ]].</p>
<p><strong>Job Request Details:</strong></p>
<ul>
<li>Job ID: #[[ job_id ]]</li>
<li>Job Request: [[ job_request_name ]]</li>
<li>Customer: [[ customer_name ]]</li>
<li>Start Date: [[ job_request_start_date ]]</li>
<li>End Date: [[ job_request_end_date ]]</li>
<li>Location: [[ job_request_city ]] - [[ job_country ]]</li>
<li>Budget: [[ job_request_amount ]]</li>
<li>Created On: [[ job_request_created_at ]]</li>
<li>Current Status: [[ bid_status ]]</li>
</ul>
<li>You can view and manage this job request through your account dashboard.</li>
<li>If you have any questions or need further assistance, please feel free to contact our support team at: info@frobster.com</li>
<p>Best regards,</p>
<p>Your Frobster-Team</p>
<p>Website: frobster.com</p>
<p>Email: info@frobster.com</p>',
updated_at = NOW()
WHERE template_id = 43 AND user_type = 'provider' AND language = 'en';

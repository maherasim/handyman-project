-- Add provider mapping for "Job Bid Status Update" so when CUSTOMER updates status, PROVIDER gets notification + email.
-- Run on existing DB if you already have post_job_bid_status_update templates.

-- 1) Notification: add provider content mapping (Customer has updated...)
INSERT INTO notification_template_content_mapping (template_id, template_detail, notification_message, notification_link, language, subject, status, user_type, created_at, updated_at)
SELECT id,
  '<p>Hello [[ provider_name ]],</p><p>Customer [[ customer_name ]] has updated the status of your job request #[[ job_id ]] - [[ job_name ]] to <strong>[[ bid_status ]]</strong>.</p><p>Check the bid page for details.</p>',
  'Customer has updated the job status to [[ bid_status ]] for job request #[[ job_id ]].',
  '', 'en', 'Job Status Updated', 1, 'provider', NOW(), NOW()
FROM notification_templates WHERE type = 'post_job_bid_status_update' LIMIT 1;

-- 2) Mail: add provider content mapping
INSERT INTO mail_template_content_mappings (template_id, template_detail, notification_message, notification_link, language, subject, status, user_type, created_at, updated_at)
SELECT id,
  '<p>Hello [[ provider_name ]],</p><p>Customer [[ customer_name ]] has updated the status of your job request #[[ job_id ]] - [[ job_name ]] to <strong>[[ bid_status ]]</strong>.</p><p>Check the bid page for details.</p><p>&nbsp;</p><p>Best regards,<br />[[ company_name ]]</p>',
  '', '', 'en', 'Job Status Updated', 1, 'provider', NOW(), NOW()
FROM mail_templates WHERE type = 'post_job_bid_status_update' LIMIT 1;

-- ============================================================
-- 1) VERIFICATION: Run these SELECTs to see current state
-- ============================================================

-- Who gets "Accept bid" notification? Should be PROVIDER only.
SELECT id, type, name, `to`, channels
FROM notification_templates
WHERE type = 'user_accept_bid';

SELECT template_id, user_type, subject, LEFT(notification_message, 60) AS msg_preview
FROM notification_template_content_mapping m
JOIN notification_templates t ON t.id = m.template_id
WHERE t.type = 'user_accept_bid';

-- Who gets "Status update" notification? Need BOTH user and provider mappings.
SELECT id, type, name, `to`, channels
FROM notification_templates
WHERE type = 'post_job_bid_status_update';

SELECT template_id, user_type, subject, LEFT(notification_message, 60) AS msg_preview
FROM notification_template_content_mapping m
JOIN notification_templates t ON t.id = m.template_id
WHERE t.type = 'post_job_bid_status_update';

-- Same for MAIL templates
SELECT id, type, `to` FROM mail_templates WHERE type IN ('user_accept_bid', 'post_job_bid_status_update');
SELECT template_id, user_type, subject
FROM mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
WHERE t.type IN ('user_accept_bid', 'post_job_bid_status_update');


-- ============================================================
-- 2) FIX: Accept bid → PROVIDER gets notification/email
-- ============================================================

UPDATE notification_templates
SET `to` = '["provider"]'
WHERE type = 'user_accept_bid';

UPDATE mail_templates
SET `to` = '["provider"]'
WHERE type = 'user_accept_bid';


-- ============================================================
-- 3) FIX: Status update → ensure PROVIDER mapping exists
--    (So when CUSTOMER updates status, PROVIDER gets notified)
-- ============================================================

-- Notification: add provider mapping if missing
INSERT INTO notification_template_content_mapping (template_id, template_detail, notification_message, notification_link, language, subject, status, user_type, created_at, updated_at)
SELECT t.id,
  '<p>Hello [[ provider_name ]],</p><p>Customer [[ customer_name ]] has updated the status of your job request #[[ job_id ]] - [[ job_name ]] to <strong>[[ bid_status ]]</strong>.</p><p>Check the bid page for details.</p>',
  'Customer has updated the job status to [[ bid_status ]] for job request #[[ job_id ]].',
  '', 'en', 'Job Status Updated', 1, 'provider', NOW(), NOW()
FROM notification_templates t
WHERE t.type = 'post_job_bid_status_update'
  AND NOT EXISTS (
    SELECT 1 FROM notification_template_content_mapping m
    WHERE m.template_id = t.id AND m.user_type = 'provider'
  )
LIMIT 1;

-- Mail: add provider mapping if missing
INSERT INTO mail_template_content_mappings (template_id, template_detail, notification_message, notification_link, language, subject, status, user_type, created_at, updated_at)
SELECT t.id,
  '<p>Hello [[ provider_name ]],</p><p>Customer [[ customer_name ]] has updated the status of your job request #[[ job_id ]] - [[ job_name ]] to <strong>[[ bid_status ]]</strong>.</p><p>Check the bid page for details.</p><p>&nbsp;</p><p>Best regards,<br />[[ company_name ]]</p>',
  '', '', 'en', 'Job Status Updated', 1, 'provider', NOW(), NOW()
FROM mail_templates t
WHERE t.type = 'post_job_bid_status_update'
  AND NOT EXISTS (
    SELECT 1 FROM mail_template_content_mappings m
    WHERE m.template_id = t.id AND m.user_type = 'provider'
  )
LIMIT 1;


-- ============================================================
-- 4) VERIFY AGAIN after fix
-- ============================================================

SELECT type, `to` FROM notification_templates WHERE type IN ('user_accept_bid', 'post_job_bid_status_update');
SELECT t.type, m.user_type FROM notification_template_content_mapping m JOIN notification_templates t ON t.id = m.template_id WHERE t.type IN ('user_accept_bid', 'post_job_bid_status_update');

SELECT type, `to` FROM mail_templates WHERE type IN ('user_accept_bid', 'post_job_bid_status_update');
SELECT t.type, m.user_type FROM mail_template_content_mappings m JOIN mail_templates t ON t.id = m.template_id WHERE t.type IN ('user_accept_bid', 'post_job_bid_status_update');

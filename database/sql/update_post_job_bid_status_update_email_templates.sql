-- Update email templates for "Job Bid Status Update" (post_job_bid_status_update)
-- Run this to apply professional wording, humanized status labels, and bid page link in emails.
-- Replace [[ job_id ]] and [[ bid_status ]] at send time; [[ link ]] is set by the app.

-- Provider (when customer updates status)
UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
  m.subject = 'Job Request #[[ job_id ]] – Status updated to [[ bid_status ]]',
  m.template_detail = '<p>Hello [[ provider_name ]],</p>
<p>The Customer has updated the status of your job request <strong>#[[ job_id ]] – [[ job_name ]]</strong> to <strong>[[ bid_status ]]</strong>.</p>
<p>You can view the latest details and take any required action in the app or via the link below.</p>
<p><a href="[[ link ]]" style="color: #2563eb;">View job and bid details</a></p>
<p>If the link does not work, copy and paste this URL into your browser:<br /><span style="word-break: break-all;">[[ link ]]</span></p>
<p>Best regards,<br />[[ company_name ]]</p>'
WHERE t.type = 'post_job_bid_status_update'
  AND m.user_type = 'provider'
  AND m.language = 'en';

-- User/Customer (when provider/employer updates status)
UPDATE mail_template_content_mappings m
JOIN mail_templates t ON t.id = m.template_id
SET
  m.subject = 'Job Request #[[ job_id ]] – Status updated to [[ bid_status ]]',
  m.template_detail = '<p>Hello [[ customer_name ]],</p>
<p>The Employer has updated the status of your job request <strong>#[[ job_id ]] – [[ job_name ]]</strong> to <strong>[[ bid_status ]]</strong>.</p>
<p>You can view the latest details and take any required action in the app or via the link below.</p>
<p><a href="[[ link ]]" style="color: #2563eb;">View job and bid details</a></p>
<p>If the link does not work, copy and paste this URL into your browser:<br /><span style="word-break: break-all;">[[ link ]]</span></p>
<p>Best regards,<br />[[ company_name ]]</p>'
WHERE t.type = 'post_job_bid_status_update'
  AND m.user_type = 'user'
  AND m.language = 'en';

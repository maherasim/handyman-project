-- Add "How it Works" page for admin Content Pages (http://127.0.0.1:8000/pages/content).
-- Run this once. After running, the page appears in Admin → Content Pages; edit content at pages/content/edit/{id}.
-- Frontend URL will be: /pages/how-it-works

INSERT INTO `pages` (`slug`, `title`, `content`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('how-it-works', 'How it Works', '<p><strong>How it works</strong></p><p>Post your job or find a service — we connect you with trusted handymen and professionals.</p><p><strong>For customers:</strong> Post a job request, compare providers, and book in minutes.</p><p><strong>For employers/service providers:</strong> Register, list your services, and get matched with jobs.</p><p>Edit this content in Admin → Content Pages.</p>', 1, 15, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `content` = VALUES(`content`),
  `is_active` = VALUES(`is_active`),
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = NOW();

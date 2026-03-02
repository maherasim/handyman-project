-- 1) Rename existing "How it Works" to "How it works for Employer"
-- 2) Add new page "How it works for Customer"
-- Run in MySQL/MariaDB. No migration. Edit content at http://127.0.0.1:8000/pages/content

-- Rename: How it Works → How it works for Employer (same slug how-it-works)
UPDATE `pages`
SET
  `title` = 'How it works for Employer',
  `content` = '<p><strong>How it works for Employer</strong></p><p>Register as a service provider, list your services, and get matched with jobs.</p><p>Post your services, receive job requests, and grow your business.</p><p>Edit this content in Admin → Content Pages.</p>',
  `updated_at` = NOW()
WHERE `slug` = 'how-it-works';

-- Add: How it works for Customer (new slug, same logic — editable in admin)
INSERT INTO `pages` (`slug`, `title`, `content`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('how-it-works-for-customer', 'How it works for Customer', '<p><strong>How it works for Customer</strong></p><p>Post your job request, compare providers, and book in minutes.</p><p>Find trusted handymen and professionals for repairs, maintenance, and home improvement.</p><p>Edit this content in Admin → Content Pages.</p>', 1, 16, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `content` = VALUES(`content`),
  `is_active` = VALUES(`is_active`),
  `sort_order` = VALUES(`sort_order`),
  `updated_at` = NOW();

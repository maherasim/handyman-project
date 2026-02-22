-- Run this SQL in your MySQL/MariaDB backend database to create the pages table and insert default pages.
-- Table: pages (Discover, About us, Investors, Careers, Partnership)

-- Create table (skip if you already ran the Laravel migration)
CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pages (edit content as needed)
INSERT INTO `pages` (`slug`, `title`, `content`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('discover', 'Discover', '<p>Discover our platform. Find trusted handymen and service providers for repairs, maintenance, and home improvement.</p><p>Browse by category, compare reviews, and book in minutes.</p>', 1, 10, NOW(), NOW()),
('about-us', 'About us', '<p>We connect homeowners with skilled professionals for repairs, maintenance, and improvements.</p><p>Our mission is to make booking a pro simple, transparent, and reliable.</p>', 1, 20, NOW(), NOW()),
('investors', 'Investors', '<p>Interested in partnering with us? We are building the future of local home services.</p><p>Contact us for investor relations and opportunities.</p>', 1, 30, NOW(), NOW()),
('careers', 'Careers', '<p>Join our team. We are always looking for talented people to help us grow.</p><p>Open roles in engineering, operations, and customer success.</p>', 1, 40, NOW(), NOW()),
('partnership', 'Partnership', '<p>Partner with us to reach more customers and grow your business.</p><p>We work with service providers, brands, and platforms.</p>', 1, 50, NOW(), NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `content` = VALUES(`content`), `updated_at` = NOW();

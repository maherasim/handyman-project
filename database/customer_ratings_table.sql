-- SQL Table Creation Query for customer_ratings table
-- This allows providers to rate customers after completing a booking

CREATE TABLE IF NOT EXISTS `customer_ratings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL COMMENT 'The booking ID for which provider is rating',
  `customer_id` BIGINT UNSIGNED NOT NULL COMMENT 'The customer/user ID being rated',
  `provider_id` BIGINT UNSIGNED NOT NULL COMMENT 'The provider ID who is giving the rating',
  `rating` DOUBLE(8,2) NULL DEFAULT NULL COMMENT 'Rating value (1.0 to 5.0, can be decimal like 4.5)',
  `review` LONGTEXT NULL DEFAULT NULL COMMENT 'Text review/feedback from provider about customer',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_customer_rating` (`booking_id`, `customer_id`, `provider_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_provider_id` (`provider_id`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_customer_ratings_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_ratings_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_customer_ratings_provider_id` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


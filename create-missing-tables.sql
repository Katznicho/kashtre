-- SQL Script to create missing tables on staging server
-- Run this on your server database

-- 1. Create service_charges table
CREATE TABLE IF NOT EXISTS `service_charges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `upper_bound` decimal(10,2) DEFAULT NULL,
  `lower_bound` decimal(10,2) DEFAULT NULL,
  `type` enum('fixed','percentage') NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_charges_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `service_charges_business_id_index` (`business_id`),
  KEY `service_charges_created_by_index` (`created_by`),
  CONSTRAINT `service_charges_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_charges_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create contractor_service_charges table
CREATE TABLE IF NOT EXISTS `contractor_service_charges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `contractor_profile_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `upper_bound` decimal(10,2) DEFAULT NULL,
  `lower_bound` decimal(10,2) DEFAULT NULL,
  `type` enum('fixed','percentage') NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contractor_service_charges_uuid_unique` (`uuid`),
  KEY `contractor_service_charges_uuid_index` (`uuid`),
  KEY `contractor_service_charges_contractor_profile_id_index` (`contractor_profile_id`),
  KEY `contractor_service_charges_business_id_index` (`business_id`),
  KEY `contractor_service_charges_created_by_index` (`created_by`),
  CONSTRAINT `contractor_service_charges_contractor_profile_id_foreign` FOREIGN KEY (`contractor_profile_id`) REFERENCES `contractor_profiles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contractor_service_charges_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contractor_service_charges_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Check what tables exist (for verification)
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = DATABASE();

-- 4. List all tables to verify
SHOW TABLES;

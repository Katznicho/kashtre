-- SQL Script to create clients table with indexed foreign key columns
-- This creates the table with indexed columns for business_id and branch_id
-- but without actual foreign key constraints to avoid constraint errors
CREATE TABLE IF NOT EXISTS `clients` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('Out Patient','In Patient') NOT NULL DEFAULT 'Out Patient',
  `client_id` varchar(255) NOT NULL,
  `visit_id` varchar(255) NOT NULL,
  `nin` varchar(255) DEFAULT NULL,
  `tin_number` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `other_names` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` enum('male','female','other') DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `payment_phone_number` varchar(255) DEFAULT NULL,
  `village` varchar(255) DEFAULT NULL,
  `county` varchar(255) DEFAULT NULL,
  `services_category` enum('dental','optical','outpatient','inpatient','maternity','funeral') DEFAULT NULL,
  `balance` varchar(255) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email` varchar(255) NOT NULL,
  `next_of_kin` varchar(255) DEFAULT NULL,
  `preferred_payment_method` enum('cash','bank_transfer','credit_card','insurance','postpaid','mobile_money') DEFAULT NULL,
  `payment_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_methods`)),
  `nok_surname` varchar(255) DEFAULT NULL,
  `nok_first_name` varchar(255) DEFAULT NULL,
  `nok_other_names` varchar(255) DEFAULT NULL,
  `nok_sex` varchar(255) DEFAULT NULL,
  `nok_marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `nok_occupation` varchar(255) DEFAULT NULL,
  `nok_phone_number` varchar(255) DEFAULT NULL,
  `nok_village` varchar(255) DEFAULT NULL,
  `nok_county` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_uuid_unique` (`uuid`),
  UNIQUE KEY `clients_client_id_unique` (`client_id`),
  KEY `clients_business_id_foreign` (`business_id`),
  KEY `clients_branch_id_foreign` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify the table was created
SELECT COUNT(*) as client_count FROM `clients`;

-- Show all tables to verify
SHOW TABLES;

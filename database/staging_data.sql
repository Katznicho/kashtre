-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 05, 2025 at 07:00 AM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u242329769_staging`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `action_type` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` date NOT NULL DEFAULT '2025-07-20',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `uuid`, `user_id`, `business_id`, `branch_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `action_type`, `description`, `date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '3f78fb6e-0992-49f7-b3ba-33882e78d341', 1, 1, 1, 'App\\Models\\User', 2, 'created', NULL, '\"{\\\"name\\\":\\\"Code Artisan Nicholas\\\",\\\"email\\\":\\\"codeartisan256@gmail.com\\\",\\\"phone\\\":\\\"0759983853\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RF\\\",\\\"gender\\\":\\\"male\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"status\\\":\\\"active\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"View Dashboard Tables\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"View Entities\\\\\\\",\\\\\\\"Edit Entities\\\\\\\",\\\\\\\"Add Entities\\\\\\\",\\\\\\\"Delete Entities\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Report\\\\\\\",\\\\\\\"Edit Report\\\\\\\",\\\\\\\"Add Report\\\\\\\",\\\\\\\"Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"View Logs\\\\\\\",\\\\\\\"Contractor\\\\\\\",\\\\\\\"Contractor\\\\\\\",\\\\\\\"View Contractor\\\\\\\",\\\\\\\"Edit Contractor\\\\\\\",\\\\\\\"Add Contractor\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"service_points\\\":\\\"[]\\\",\\\"uuid\\\":\\\"1ea1668c-cc3b-41a1-be8f-f81ba3f3cfea\\\",\\\"updated_at\\\":\\\"2025-07-23 10:23:22\\\",\\\"created_at\\\":\\\"2025-07-23 10:23:22\\\",\\\"id\\\":2}\"', '197.239.9.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-23 10:23:22', '2025-07-23 10:23:22', NULL),
(2, '5cb31e40-e356-4ba7-9353-f179a9bf69eb', 1, 1, 1, 'App\\Models\\User', 3, 'created', NULL, '\"{\\\"name\\\":\\\"Nicholas Katende\\\",\\\"email\\\":\\\"katznicho+124@gmail.com\\\",\\\"phone\\\":\\\"0759983853\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RF\\\",\\\"gender\\\":\\\"male\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"status\\\":\\\"active\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"View Dashboard Tables\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"View Entities\\\\\\\",\\\\\\\"Edit Entities\\\\\\\",\\\\\\\"Add Entities\\\\\\\",\\\\\\\"Delete Entities\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"View Products\\\\\\\",\\\\\\\"Edit Products\\\\\\\",\\\\\\\"Add Products\\\\\\\",\\\\\\\"Delete Products\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"service_points\\\":\\\"[]\\\",\\\"uuid\\\":\\\"5a785375-53e7-4bad-85e8-15af3e97b77f\\\",\\\"updated_at\\\":\\\"2025-07-23 10:27:29\\\",\\\"created_at\\\":\\\"2025-07-23 10:27:29\\\",\\\"id\\\":3}\"', '197.239.9.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-23 10:27:29', '2025-07-23 10:27:29', NULL),
(3, 'ff7788a6-7e29-43ee-979f-5cd836c08a5c', 1, 1, 1, 'App\\Models\\Business', 3, 'created', NULL, '\"{\\\"name\\\":\\\"Demo Hospital Medical Center\\\",\\\"email\\\":\\\"codeartisan256@gmail.com\\\",\\\"phone\\\":\\\"0759983853\\\",\\\"address\\\":\\\"Kawempe\\\",\\\"logo\\\":\\\"logos\\\\\\/e97FB9mngw49JKGXuzj1gu7WkxSCs70v9zCmW46u.png\\\",\\\"account_number\\\":\\\"KS1753266570\\\",\\\"uuid\\\":\\\"ae0557ce-abd1-458f-ad43-13808f8579ff\\\",\\\"updated_at\\\":\\\"2025-07-23 10:29:30\\\",\\\"created_at\\\":\\\"2025-07-23 10:29:30\\\",\\\"id\\\":3}\"', '197.239.9.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-23 10:29:30', '2025-07-23 10:29:30', NULL),
(4, '5634aa2a-8fd2-40b3-acde-451d96cafe65', 1, 1, 1, 'App\\Models\\User', 4, 'created', NULL, '\"{\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"gender\\\":\\\"male\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"status\\\":\\\"active\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"View Dashboard Tables\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"View Logs\\\\\\\",\\\\\\\"Contractor\\\\\\\",\\\\\\\"Contractor\\\\\\\",\\\\\\\"View Contractor\\\\\\\",\\\\\\\"Edit Contractor\\\\\\\",\\\\\\\"Add Contractor\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"View Modules\\\\\\\",\\\\\\\"Edit Modules\\\\\\\",\\\\\\\"Add Modules\\\\\\\",\\\\\\\"Delete Modules\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"service_points\\\":\\\"[]\\\",\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"updated_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"created_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"id\\\":4}\"', '102.86.2.4', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-23 18:33:21', '2025-07-23 18:33:21', NULL),
(5, '3ac39e00-c6bf-43cb-a2e1-9dc00cb4a5d5', 1, 1, 1, 'App\\Models\\Business', 4, 'created', NULL, '\"{\\\"name\\\":\\\"Wakiso Medical Center\\\",\\\"email\\\":\\\"whysemedical@gmail.com\\\",\\\"phone\\\":\\\"0772093837\\\",\\\"address\\\":\\\"Kawempe\\\",\\\"logo\\\":\\\"logos\\\\\\/UkymxTu6JGP0RGeqPCoiMJ5RrqPxgwnt4pAT0cGo.png\\\",\\\"account_number\\\":\\\"KS1753295827\\\",\\\"uuid\\\":\\\"82b16873-4b07-41fd-abf0-b8f7395d7eb4\\\",\\\"updated_at\\\":\\\"2025-07-23 18:37:07\\\",\\\"created_at\\\":\\\"2025-07-23 18:37:07\\\",\\\"id\\\":4}\"', '102.86.2.4', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-23 18:37:07', '2025-07-23 18:37:07', NULL),
(6, '527cf8b9-22ec-4a43-8cc8-397314d09c6b', 1, 1, 1, 'App\\Models\\User', 1, 'updated', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"View Dashboard\\\",\\\"View Dashboard Cards\\\",\\\"View Dashboard Charts\\\",\\\"View Dashboard Tables\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"View Entities\\\",\\\"Edit Entities\\\",\\\"Add Entities\\\",\\\"Delete Entities\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"View Staff\\\",\\\"Edit Staff\\\",\\\"Add Staff\\\",\\\"Delete Staff\\\",\\\"Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"View Report\\\",\\\"Edit Report\\\",\\\"Add Report\\\",\\\"Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"View Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"View Products\\\",\\\"Edit Products\\\",\\\"Add Products\\\",\\\"Delete Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"View Sales\\\",\\\"Edit Sales\\\",\\\"Add Sales\\\",\\\"Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"View Clients\\\",\\\"Edit Clients\\\",\\\"Add Clients\\\",\\\"Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"View Queues\\\",\\\"Withdrawals\\\",\\\"Withdrawals\\\",\\\"View Withdrawals\\\",\\\"Edit Withdrawals\\\",\\\"Add Withdrawals\\\",\\\"Delete Withdrawals\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"View Modules\\\",\\\"Edit Modules\\\",\\\"Add Modules\\\",\\\"Delete Modules\\\",\\\"Stock\\\",\\\"Stock\\\",\\\"View Stock\\\",\\\"Edit Stock\\\",\\\"Add Stock\\\",\\\"Delete Stock\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"View Service Points\\\",\\\"Edit Service Points\\\",\\\"Add Service Points\\\",\\\"Delete Service Points\\\",\\\"Bulky Update Service Points\\\",\\\"Departments\\\",\\\"View Departments\\\",\\\"Edit Departments\\\",\\\"Add Departments\\\",\\\"Delete Departments\\\",\\\"Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"View Qualifications\\\",\\\"Edit Qualifications\\\",\\\"Add Qualifications\\\",\\\"Delete Qualifications\\\",\\\"Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"View Titles\\\",\\\"Edit Titles\\\",\\\"Add Titles\\\",\\\"Delete Titles\\\",\\\"Bulky Update Titles\\\",\\\"Rooms\\\",\\\"View Rooms\\\",\\\"Edit Rooms\\\",\\\"Add Rooms\\\",\\\"Delete Rooms\\\",\\\"Bulky Update Rooms\\\",\\\"Sections\\\",\\\"View Sections\\\",\\\"Edit Sections\\\",\\\"Add Sections\\\",\\\"Delete Sections\\\",\\\"Bulky Update Sections\\\",\\\"Item Units\\\",\\\"View Item Units\\\",\\\"Edit Item Units\\\",\\\"Add Item Units\\\",\\\"Delete Item Units\\\",\\\"Bulky Update Item Units\\\",\\\"Groups\\\",\\\"View Groups\\\",\\\"Edit Groups\\\",\\\"Add Groups\\\",\\\"Delete Groups\\\",\\\"Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"View Patient Categories\\\",\\\"Edit Patient Categories\\\",\\\"Add Patient Categories\\\",\\\"Delete Patient Categories\\\",\\\"Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"View Suppliers\\\",\\\"Edit Suppliers\\\",\\\"Add Suppliers\\\",\\\"Delete Suppliers\\\",\\\"Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"View Stores\\\",\\\"Edit Stores\\\",\\\"Add Stores\\\",\\\"Delete Stores\\\",\\\"Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"View Insurance Companies\\\",\\\"Edit Insurance Companies\\\",\\\"Add Insurance Companies\\\",\\\"Delete Insurance Companies\\\",\\\"Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"View Sub Groups\\\",\\\"Edit Sub Groups\\\",\\\"Add Sub Groups\\\",\\\"Delete Sub Groups\\\",\\\"Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"View Admin Users\\\",\\\"Edit Admin Users\\\",\\\"Add Admin Users\\\",\\\"Delete Admin Users\\\",\\\"Assign Roles\\\",\\\"Audit Logs\\\",\\\"View Audit Logs\\\",\\\"System Settings\\\",\\\"View System Settings\\\",\\\"Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"View Business\\\",\\\"Edit Business\\\",\\\"Add Business\\\",\\\"Delete Business\\\",\\\"Branches\\\",\\\"View Branches\\\",\\\"Edit Branches\\\",\\\"Add Branches\\\",\\\"Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"View Clients\\\",\\\"Edit Clients\\\",\\\"Add Clients\\\",\\\"Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"View Staff\\\",\\\"Edit Staff\\\",\\\"Add Staff\\\",\\\"Delete Staff\\\",\\\"Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"View Reports\\\",\\\"Export Reports\\\",\\\"Filter Reports\\\"],\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20T20:05:52.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-20T20:05:52.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"\\\\\\\"[]\\\\\\\"\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"\\\\\\\"[1]\\\\\\\"\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20 20:05:52\\\",\\\"updated_at\\\":\\\"2025-07-25 19:00:14\\\",\\\"deleted_at\\\":null}\"', '102.86.4.73', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-25 19:00:14', '2025-07-25 19:00:14', NULL),
(7, '0a77a552-8e86-4999-a5a3-af7c26090b66', 1, 1, 1, 'App\\Models\\User', 1, 'updated', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"Departments\\\",\\\"Departments\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"Withdrawals\\\",\\\"Withdrawals\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"Stock\\\",\\\"Stock\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"Departments\\\",\\\"Qualifications\\\",\\\"Titles\\\",\\\"Rooms\\\",\\\"Sections\\\",\\\"Item Units\\\",\\\"Groups\\\",\\\"Patient Categories\\\",\\\"Suppliers\\\",\\\"Stores\\\",\\\"Insurance Companies\\\",\\\"Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"Audit Logs\\\",\\\"System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload:Bulk Validations Upload\\\"],\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20T20:05:52.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-25T19:00:14.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"\\\\\\\"[]\\\\\\\"\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Service Points:View Service Points\\\\\\\",\\\\\\\"Service Points:Edit Service Points\\\\\\\",\\\\\\\"Service Points:Add Service Points\\\\\\\",\\\\\\\"Service Points:Delete Service Points\\\\\\\",\\\\\\\"Service Points:Bulky Update Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Departments:Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Qualifications:View Qualifications\\\\\\\",\\\\\\\"Qualifications:Edit Qualifications\\\\\\\",\\\\\\\"Qualifications:Add Qualifications\\\\\\\",\\\\\\\"Qualifications:Delete Qualifications\\\\\\\",\\\\\\\"Qualifications:Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Titles:View Titles\\\\\\\",\\\\\\\"Titles:Edit Titles\\\\\\\",\\\\\\\"Titles:Add Titles\\\\\\\",\\\\\\\"Titles:Delete Titles\\\\\\\",\\\\\\\"Titles:Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Rooms:View Rooms\\\\\\\",\\\\\\\"Rooms:Edit Rooms\\\\\\\",\\\\\\\"Rooms:Add Rooms\\\\\\\",\\\\\\\"Rooms:Delete Rooms\\\\\\\",\\\\\\\"Rooms:Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Sections:View Sections\\\\\\\",\\\\\\\"Sections:Edit Sections\\\\\\\",\\\\\\\"Sections:Add Sections\\\\\\\",\\\\\\\"Sections:Delete Sections\\\\\\\",\\\\\\\"Sections:Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Item Units:View Item Units\\\\\\\",\\\\\\\"Item Units:Edit Item Units\\\\\\\",\\\\\\\"Item Units:Add Item Units\\\\\\\",\\\\\\\"Item Units:Delete Item Units\\\\\\\",\\\\\\\"Item Units:Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Groups:View Groups\\\\\\\",\\\\\\\"Groups:Edit Groups\\\\\\\",\\\\\\\"Groups:Add Groups\\\\\\\",\\\\\\\"Groups:Delete Groups\\\\\\\",\\\\\\\"Groups:Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Patient Categories:View Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Edit Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Add Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Delete Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Suppliers:View Suppliers\\\\\\\",\\\\\\\"Suppliers:Edit Suppliers\\\\\\\",\\\\\\\"Suppliers:Add Suppliers\\\\\\\",\\\\\\\"Suppliers:Delete Suppliers\\\\\\\",\\\\\\\"Suppliers:Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Stores:View Stores\\\\\\\",\\\\\\\"Stores:Edit Stores\\\\\\\",\\\\\\\"Stores:Add Stores\\\\\\\",\\\\\\\"Stores:Delete Stores\\\\\\\",\\\\\\\"Stores:Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:View Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Edit Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Add Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Delete Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Sub Groups:View Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Edit Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Add Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Delete Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Admin Users:View Admin Users\\\\\\\",\\\\\\\"Admin Users:Edit Admin Users\\\\\\\",\\\\\\\"Admin Users:Add Admin Users\\\\\\\",\\\\\\\"Admin Users:Delete Admin Users\\\\\\\",\\\\\\\"Admin Users:Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"Audit Logs:View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"System Settings:View System Settings\\\\\\\",\\\\\\\"System Settings:Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"\\\\\\\"[1]\\\\\\\"\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20 20:05:52\\\",\\\"updated_at\\\":\\\"2025-07-25 19:00:49\\\",\\\"deleted_at\\\":null}\"', '102.86.4.73', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-25 19:00:49', '2025-07-25 19:00:49', NULL),
(8, '5f13d712-eb81-4d48-9f77-fbfbce9f55e8', 3, 1, 1, 'App\\Models\\User', 4, 'updated', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"ROHSA15QTGDwMy4lEjEjVGJIRggwAKnjzrCQcjpUbDFrQtRiydIjmcdstX9y\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":[],\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"View Dashboard\\\",\\\"View Dashboard Cards\\\",\\\"View Dashboard Charts\\\",\\\"View Dashboard Tables\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"View Logs\\\",\\\"Contractor\\\",\\\"Contractor\\\",\\\"View Contractor\\\",\\\"Edit Contractor\\\",\\\"Add Contractor\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"View Modules\\\",\\\"Edit Modules\\\",\\\"Add Modules\\\",\\\"Delete Modules\\\"],\\\"allowed_branches\\\":[1],\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23T18:33:21.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-27T07:27:36.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"ROHSA15QTGDwMy4lEjEjVGJIRggwAKnjzrCQcjpUbDFrQtRiydIjmcdstX9y\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities:View Entities\\\\\\\",\\\\\\\"Entities:Edit Entities\\\\\\\",\\\\\\\"Entities:Add Entities\\\\\\\",\\\\\\\"Entities:Delete Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Report\\\\\\\",\\\\\\\"Reports:Edit Report\\\\\\\",\\\\\\\"Reports:Add Report\\\\\\\",\\\\\\\"Reports:Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products:View Products\\\\\\\",\\\\\\\"Products:Edit Products\\\\\\\",\\\\\\\"Products:Add Products\\\\\\\",\\\\\\\"Products:Delete Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales:View Sales\\\\\\\",\\\\\\\"Sales:Edit Sales\\\\\\\",\\\\\\\"Sales:Add Sales\\\\\\\",\\\\\\\"Sales:Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Customers:View Queues\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock:View Stock\\\\\\\",\\\\\\\"Stock:Edit Stock\\\\\\\",\\\\\\\"Stock:Add Stock\\\\\\\",\\\\\\\"Stock:Delete Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Service Points:View Service Points\\\\\\\",\\\\\\\"Service Points:Edit Service Points\\\\\\\",\\\\\\\"Service Points:Add Service Points\\\\\\\",\\\\\\\"Service Points:Delete Service Points\\\\\\\",\\\\\\\"Service Points:Bulky Update Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Departments:Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Qualifications:View Qualifications\\\\\\\",\\\\\\\"Qualifications:Edit Qualifications\\\\\\\",\\\\\\\"Qualifications:Add Qualifications\\\\\\\",\\\\\\\"Qualifications:Delete Qualifications\\\\\\\",\\\\\\\"Qualifications:Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Titles:View Titles\\\\\\\",\\\\\\\"Titles:Edit Titles\\\\\\\",\\\\\\\"Titles:Add Titles\\\\\\\",\\\\\\\"Titles:Delete Titles\\\\\\\",\\\\\\\"Titles:Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Rooms:View Rooms\\\\\\\",\\\\\\\"Rooms:Edit Rooms\\\\\\\",\\\\\\\"Rooms:Add Rooms\\\\\\\",\\\\\\\"Rooms:Delete Rooms\\\\\\\",\\\\\\\"Rooms:Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Sections:View Sections\\\\\\\",\\\\\\\"Sections:Edit Sections\\\\\\\",\\\\\\\"Sections:Add Sections\\\\\\\",\\\\\\\"Sections:Delete Sections\\\\\\\",\\\\\\\"Sections:Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Item Units:View Item Units\\\\\\\",\\\\\\\"Item Units:Edit Item Units\\\\\\\",\\\\\\\"Item Units:Add Item Units\\\\\\\",\\\\\\\"Item Units:Delete Item Units\\\\\\\",\\\\\\\"Item Units:Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Groups:View Groups\\\\\\\",\\\\\\\"Groups:Edit Groups\\\\\\\",\\\\\\\"Groups:Add Groups\\\\\\\",\\\\\\\"Groups:Delete Groups\\\\\\\",\\\\\\\"Groups:Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Patient Categories:View Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Edit Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Add Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Delete Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Suppliers:View Suppliers\\\\\\\",\\\\\\\"Suppliers:Edit Suppliers\\\\\\\",\\\\\\\"Suppliers:Add Suppliers\\\\\\\",\\\\\\\"Suppliers:Delete Suppliers\\\\\\\",\\\\\\\"Suppliers:Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Stores:View Stores\\\\\\\",\\\\\\\"Stores:Edit Stores\\\\\\\",\\\\\\\"Stores:Add Stores\\\\\\\",\\\\\\\"Stores:Delete Stores\\\\\\\",\\\\\\\"Stores:Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:View Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Edit Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Add Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Delete Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Sub Groups:View Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Edit Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Add Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Delete Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Admin Users:View Admin Users\\\\\\\",\\\\\\\"Admin Users:Edit Admin Users\\\\\\\",\\\\\\\"Admin Users:Add Admin Users\\\\\\\",\\\\\\\"Admin Users:Delete Admin Users\\\\\\\",\\\\\\\"Admin Users:Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"Audit Logs:View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"System Settings:View System Settings\\\\\\\",\\\\\\\"System Settings:Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business:View Business\\\\\\\",\\\\\\\"Business:Edit Business\\\\\\\",\\\\\\\"Business:Add Business\\\\\\\",\\\\\\\"Business:Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Branches:View Branches\\\\\\\",\\\\\\\"Branches:Edit Branches\\\\\\\",\\\\\\\"Branches:Add Branches\\\\\\\",\\\\\\\"Branches:Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Reports\\\\\\\",\\\\\\\"Reports:Export Reports\\\\\\\",\\\\\\\"Reports:Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"updated_at\\\":\\\"2025-07-27 08:01:09\\\",\\\"deleted_at\\\":null}\"', '2a0d:5600:140:5002:1119:f61a:cd6:cc0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', NULL, '', '2025-07-20', '2025-07-27 08:01:09', '2025-07-27 08:01:09', NULL),
(9, '10ae8147-3277-425a-ba8e-adc096fe7b8a', 4, 1, 1, 'App\\Models\\User', 4, 'updated', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"ROHSA15QTGDwMy4lEjEjVGJIRggwAKnjzrCQcjpUbDFrQtRiydIjmcdstX9y\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":[],\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"Entities:View Entities\\\",\\\"Entities:Edit Entities\\\",\\\"Entities:Add Entities\\\",\\\"Entities:Delete Entities\\\",\\\"Departments\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"Reports:View Report\\\",\\\"Reports:Edit Report\\\",\\\"Reports:Add Report\\\",\\\"Reports:Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"Products:View Products\\\",\\\"Products:Edit Products\\\",\\\"Products:Add Products\\\",\\\"Products:Delete Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"Sales:View Sales\\\",\\\"Sales:Edit Sales\\\",\\\"Sales:Add Sales\\\",\\\"Sales:Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"Customers:View Queues\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"Stock\\\",\\\"Stock\\\",\\\"Stock:View Stock\\\",\\\"Stock:Edit Stock\\\",\\\"Stock:Add Stock\\\",\\\"Stock:Delete Stock\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"Service Points:View Service Points\\\",\\\"Service Points:Edit Service Points\\\",\\\"Service Points:Add Service Points\\\",\\\"Service Points:Delete Service Points\\\",\\\"Service Points:Bulky Update Service Points\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Departments:Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"Qualifications:View Qualifications\\\",\\\"Qualifications:Edit Qualifications\\\",\\\"Qualifications:Add Qualifications\\\",\\\"Qualifications:Delete Qualifications\\\",\\\"Qualifications:Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"Titles:View Titles\\\",\\\"Titles:Edit Titles\\\",\\\"Titles:Add Titles\\\",\\\"Titles:Delete Titles\\\",\\\"Titles:Bulky Update Titles\\\",\\\"Rooms\\\",\\\"Rooms:View Rooms\\\",\\\"Rooms:Edit Rooms\\\",\\\"Rooms:Add Rooms\\\",\\\"Rooms:Delete Rooms\\\",\\\"Rooms:Bulky Update Rooms\\\",\\\"Sections\\\",\\\"Sections:View Sections\\\",\\\"Sections:Edit Sections\\\",\\\"Sections:Add Sections\\\",\\\"Sections:Delete Sections\\\",\\\"Sections:Bulky Update Sections\\\",\\\"Item Units\\\",\\\"Item Units:View Item Units\\\",\\\"Item Units:Edit Item Units\\\",\\\"Item Units:Add Item Units\\\",\\\"Item Units:Delete Item Units\\\",\\\"Item Units:Bulky Update Item Units\\\",\\\"Groups\\\",\\\"Groups:View Groups\\\",\\\"Groups:Edit Groups\\\",\\\"Groups:Add Groups\\\",\\\"Groups:Delete Groups\\\",\\\"Groups:Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"Patient Categories:View Patient Categories\\\",\\\"Patient Categories:Edit Patient Categories\\\",\\\"Patient Categories:Add Patient Categories\\\",\\\"Patient Categories:Delete Patient Categories\\\",\\\"Patient Categories:Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"Suppliers:View Suppliers\\\",\\\"Suppliers:Edit Suppliers\\\",\\\"Suppliers:Add Suppliers\\\",\\\"Suppliers:Delete Suppliers\\\",\\\"Suppliers:Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"Stores:View Stores\\\",\\\"Stores:Edit Stores\\\",\\\"Stores:Add Stores\\\",\\\"Stores:Delete Stores\\\",\\\"Stores:Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"Insurance Companies:View Insurance Companies\\\",\\\"Insurance Companies:Edit Insurance Companies\\\",\\\"Insurance Companies:Add Insurance Companies\\\",\\\"Insurance Companies:Delete Insurance Companies\\\",\\\"Insurance Companies:Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"Sub Groups:View Sub Groups\\\",\\\"Sub Groups:Edit Sub Groups\\\",\\\"Sub Groups:Add Sub Groups\\\",\\\"Sub Groups:Delete Sub Groups\\\",\\\"Sub Groups:Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"Admin Users:View Admin Users\\\",\\\"Admin Users:Edit Admin Users\\\",\\\"Admin Users:Add Admin Users\\\",\\\"Admin Users:Delete Admin Users\\\",\\\"Admin Users:Assign Roles\\\",\\\"Audit Logs\\\",\\\"Audit Logs:View Audit Logs\\\",\\\"System Settings\\\",\\\"System Settings:View System Settings\\\",\\\"System Settings:Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"Business:View Business\\\",\\\"Business:Edit Business\\\",\\\"Business:Add Business\\\",\\\"Business:Delete Business\\\",\\\"Branches\\\",\\\"Branches:View Branches\\\",\\\"Branches:Edit Branches\\\",\\\"Branches:Add Branches\\\",\\\"Branches:Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"Reports:View Reports\\\",\\\"Reports:Export Reports\\\",\\\"Reports:Filter Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload:Bulk Validations Upload\\\"],\\\"allowed_branches\\\":[1],\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23T18:33:21.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-27T08:01:09.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"Sr5yBotxNRa9XPz0ekbgfNtJTzFyAylio6gPpUFcHQWK5zXeSiqRK2wMVBRG\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities:View Entities\\\\\\\",\\\\\\\"Entities:Edit Entities\\\\\\\",\\\\\\\"Entities:Add Entities\\\\\\\",\\\\\\\"Entities:Delete Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Report\\\\\\\",\\\\\\\"Reports:Edit Report\\\\\\\",\\\\\\\"Reports:Add Report\\\\\\\",\\\\\\\"Reports:Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products:View Products\\\\\\\",\\\\\\\"Products:Edit Products\\\\\\\",\\\\\\\"Products:Add Products\\\\\\\",\\\\\\\"Products:Delete Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales:View Sales\\\\\\\",\\\\\\\"Sales:Edit Sales\\\\\\\",\\\\\\\"Sales:Add Sales\\\\\\\",\\\\\\\"Sales:Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Customers:View Queues\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock:View Stock\\\\\\\",\\\\\\\"Stock:Edit Stock\\\\\\\",\\\\\\\"Stock:Add Stock\\\\\\\",\\\\\\\"Stock:Delete Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Service Points:View Service Points\\\\\\\",\\\\\\\"Service Points:Edit Service Points\\\\\\\",\\\\\\\"Service Points:Add Service Points\\\\\\\",\\\\\\\"Service Points:Delete Service Points\\\\\\\",\\\\\\\"Service Points:Bulky Update Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Departments:Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Qualifications:View Qualifications\\\\\\\",\\\\\\\"Qualifications:Edit Qualifications\\\\\\\",\\\\\\\"Qualifications:Add Qualifications\\\\\\\",\\\\\\\"Qualifications:Delete Qualifications\\\\\\\",\\\\\\\"Qualifications:Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Titles:View Titles\\\\\\\",\\\\\\\"Titles:Edit Titles\\\\\\\",\\\\\\\"Titles:Add Titles\\\\\\\",\\\\\\\"Titles:Delete Titles\\\\\\\",\\\\\\\"Titles:Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Rooms:View Rooms\\\\\\\",\\\\\\\"Rooms:Edit Rooms\\\\\\\",\\\\\\\"Rooms:Add Rooms\\\\\\\",\\\\\\\"Rooms:Delete Rooms\\\\\\\",\\\\\\\"Rooms:Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Sections:View Sections\\\\\\\",\\\\\\\"Sections:Edit Sections\\\\\\\",\\\\\\\"Sections:Add Sections\\\\\\\",\\\\\\\"Sections:Delete Sections\\\\\\\",\\\\\\\"Sections:Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Item Units:View Item Units\\\\\\\",\\\\\\\"Item Units:Edit Item Units\\\\\\\",\\\\\\\"Item Units:Add Item Units\\\\\\\",\\\\\\\"Item Units:Delete Item Units\\\\\\\",\\\\\\\"Item Units:Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Groups:View Groups\\\\\\\",\\\\\\\"Groups:Edit Groups\\\\\\\",\\\\\\\"Groups:Add Groups\\\\\\\",\\\\\\\"Groups:Delete Groups\\\\\\\",\\\\\\\"Groups:Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Patient Categories:View Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Edit Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Add Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Delete Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Suppliers:View Suppliers\\\\\\\",\\\\\\\"Suppliers:Edit Suppliers\\\\\\\",\\\\\\\"Suppliers:Add Suppliers\\\\\\\",\\\\\\\"Suppliers:Delete Suppliers\\\\\\\",\\\\\\\"Suppliers:Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Stores:View Stores\\\\\\\",\\\\\\\"Stores:Edit Stores\\\\\\\",\\\\\\\"Stores:Add Stores\\\\\\\",\\\\\\\"Stores:Delete Stores\\\\\\\",\\\\\\\"Stores:Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:View Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Edit Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Add Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Delete Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Sub Groups:View Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Edit Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Add Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Delete Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Admin Users:View Admin Users\\\\\\\",\\\\\\\"Admin Users:Edit Admin Users\\\\\\\",\\\\\\\"Admin Users:Add Admin Users\\\\\\\",\\\\\\\"Admin Users:Delete Admin Users\\\\\\\",\\\\\\\"Admin Users:Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"Audit Logs:View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"System Settings:View System Settings\\\\\\\",\\\\\\\"System Settings:Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business:View Business\\\\\\\",\\\\\\\"Business:Edit Business\\\\\\\",\\\\\\\"Business:Add Business\\\\\\\",\\\\\\\"Business:Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Branches:View Branches\\\\\\\",\\\\\\\"Branches:Edit Branches\\\\\\\",\\\\\\\"Branches:Add Branches\\\\\\\",\\\\\\\"Branches:Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Reports\\\\\\\",\\\\\\\"Reports:Export Reports\\\\\\\",\\\\\\\"Reports:Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"updated_at\\\":\\\"2025-07-27 08:01:09\\\",\\\"deleted_at\\\":null}\"', '2a0d:5600:140:5002:1119:f61a:cd6:cc0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', NULL, '', '2025-07-20', '2025-07-27 08:02:25', '2025-07-27 08:02:25', NULL);
INSERT INTO `activity_logs` (`id`, `uuid`, `user_id`, `business_id`, `branch_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `action_type`, `description`, `date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(10, '22b41a6d-42be-4a9f-9503-bc57ece667d9', 4, 1, 1, 'App\\Models\\User', 4, 'updated', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"Sr5yBotxNRa9XPz0ekbgfNtJTzFyAylio6gPpUFcHQWK5zXeSiqRK2wMVBRG\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":[],\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"Entities:View Entities\\\",\\\"Entities:Edit Entities\\\",\\\"Entities:Add Entities\\\",\\\"Entities:Delete Entities\\\",\\\"Departments\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"Reports:View Report\\\",\\\"Reports:Edit Report\\\",\\\"Reports:Add Report\\\",\\\"Reports:Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"Products:View Products\\\",\\\"Products:Edit Products\\\",\\\"Products:Add Products\\\",\\\"Products:Delete Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"Sales:View Sales\\\",\\\"Sales:Edit Sales\\\",\\\"Sales:Add Sales\\\",\\\"Sales:Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"Customers:View Queues\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"Stock\\\",\\\"Stock\\\",\\\"Stock:View Stock\\\",\\\"Stock:Edit Stock\\\",\\\"Stock:Add Stock\\\",\\\"Stock:Delete Stock\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"Service Points:View Service Points\\\",\\\"Service Points:Edit Service Points\\\",\\\"Service Points:Add Service Points\\\",\\\"Service Points:Delete Service Points\\\",\\\"Service Points:Bulky Update Service Points\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Departments:Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"Qualifications:View Qualifications\\\",\\\"Qualifications:Edit Qualifications\\\",\\\"Qualifications:Add Qualifications\\\",\\\"Qualifications:Delete Qualifications\\\",\\\"Qualifications:Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"Titles:View Titles\\\",\\\"Titles:Edit Titles\\\",\\\"Titles:Add Titles\\\",\\\"Titles:Delete Titles\\\",\\\"Titles:Bulky Update Titles\\\",\\\"Rooms\\\",\\\"Rooms:View Rooms\\\",\\\"Rooms:Edit Rooms\\\",\\\"Rooms:Add Rooms\\\",\\\"Rooms:Delete Rooms\\\",\\\"Rooms:Bulky Update Rooms\\\",\\\"Sections\\\",\\\"Sections:View Sections\\\",\\\"Sections:Edit Sections\\\",\\\"Sections:Add Sections\\\",\\\"Sections:Delete Sections\\\",\\\"Sections:Bulky Update Sections\\\",\\\"Item Units\\\",\\\"Item Units:View Item Units\\\",\\\"Item Units:Edit Item Units\\\",\\\"Item Units:Add Item Units\\\",\\\"Item Units:Delete Item Units\\\",\\\"Item Units:Bulky Update Item Units\\\",\\\"Groups\\\",\\\"Groups:View Groups\\\",\\\"Groups:Edit Groups\\\",\\\"Groups:Add Groups\\\",\\\"Groups:Delete Groups\\\",\\\"Groups:Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"Patient Categories:View Patient Categories\\\",\\\"Patient Categories:Edit Patient Categories\\\",\\\"Patient Categories:Add Patient Categories\\\",\\\"Patient Categories:Delete Patient Categories\\\",\\\"Patient Categories:Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"Suppliers:View Suppliers\\\",\\\"Suppliers:Edit Suppliers\\\",\\\"Suppliers:Add Suppliers\\\",\\\"Suppliers:Delete Suppliers\\\",\\\"Suppliers:Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"Stores:View Stores\\\",\\\"Stores:Edit Stores\\\",\\\"Stores:Add Stores\\\",\\\"Stores:Delete Stores\\\",\\\"Stores:Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"Insurance Companies:View Insurance Companies\\\",\\\"Insurance Companies:Edit Insurance Companies\\\",\\\"Insurance Companies:Add Insurance Companies\\\",\\\"Insurance Companies:Delete Insurance Companies\\\",\\\"Insurance Companies:Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"Sub Groups:View Sub Groups\\\",\\\"Sub Groups:Edit Sub Groups\\\",\\\"Sub Groups:Add Sub Groups\\\",\\\"Sub Groups:Delete Sub Groups\\\",\\\"Sub Groups:Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"Admin Users:View Admin Users\\\",\\\"Admin Users:Edit Admin Users\\\",\\\"Admin Users:Add Admin Users\\\",\\\"Admin Users:Delete Admin Users\\\",\\\"Admin Users:Assign Roles\\\",\\\"Audit Logs\\\",\\\"Audit Logs:View Audit Logs\\\",\\\"System Settings\\\",\\\"System Settings:View System Settings\\\",\\\"System Settings:Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"Business:View Business\\\",\\\"Business:Edit Business\\\",\\\"Business:Add Business\\\",\\\"Business:Delete Business\\\",\\\"Branches\\\",\\\"Branches:View Branches\\\",\\\"Branches:Edit Branches\\\",\\\"Branches:Add Branches\\\",\\\"Branches:Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"Reports:View Reports\\\",\\\"Reports:Export Reports\\\",\\\"Reports:Filter Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload:Bulk Validations Upload\\\"],\\\"allowed_branches\\\":[1],\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23T18:33:21.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-27T08:01:09.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"Sr5yBotxNRa9XPz0ekbgfNtJTzFyAylio6gPpUFcHQWK5zXeSiqRK2wMVBRG\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard:View Dashboard\\\\\\\",\\\\\\\"Dashboard:View Dashboard Cards\\\\\\\",\\\\\\\"Dashboard:View Dashboard Charts\\\\\\\",\\\\\\\"Dashboard:View Dashboard Tables\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities:View Entities\\\\\\\",\\\\\\\"Entities:Edit Entities\\\\\\\",\\\\\\\"Entities:Add Entities\\\\\\\",\\\\\\\"Entities:Delete Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Report\\\\\\\",\\\\\\\"Reports:Edit Report\\\\\\\",\\\\\\\"Reports:Add Report\\\\\\\",\\\\\\\"Reports:Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products:View Products\\\\\\\",\\\\\\\"Products:Edit Products\\\\\\\",\\\\\\\"Products:Add Products\\\\\\\",\\\\\\\"Products:Delete Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales:View Sales\\\\\\\",\\\\\\\"Sales:Edit Sales\\\\\\\",\\\\\\\"Sales:Add Sales\\\\\\\",\\\\\\\"Sales:Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Customers:View Queues\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock:View Stock\\\\\\\",\\\\\\\"Stock:Edit Stock\\\\\\\",\\\\\\\"Stock:Add Stock\\\\\\\",\\\\\\\"Stock:Delete Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Service Points:View Service Points\\\\\\\",\\\\\\\"Service Points:Edit Service Points\\\\\\\",\\\\\\\"Service Points:Add Service Points\\\\\\\",\\\\\\\"Service Points:Delete Service Points\\\\\\\",\\\\\\\"Service Points:Bulky Update Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Departments:Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Qualifications:View Qualifications\\\\\\\",\\\\\\\"Qualifications:Edit Qualifications\\\\\\\",\\\\\\\"Qualifications:Add Qualifications\\\\\\\",\\\\\\\"Qualifications:Delete Qualifications\\\\\\\",\\\\\\\"Qualifications:Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Titles:View Titles\\\\\\\",\\\\\\\"Titles:Edit Titles\\\\\\\",\\\\\\\"Titles:Add Titles\\\\\\\",\\\\\\\"Titles:Delete Titles\\\\\\\",\\\\\\\"Titles:Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Rooms:View Rooms\\\\\\\",\\\\\\\"Rooms:Edit Rooms\\\\\\\",\\\\\\\"Rooms:Add Rooms\\\\\\\",\\\\\\\"Rooms:Delete Rooms\\\\\\\",\\\\\\\"Rooms:Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Sections:View Sections\\\\\\\",\\\\\\\"Sections:Edit Sections\\\\\\\",\\\\\\\"Sections:Add Sections\\\\\\\",\\\\\\\"Sections:Delete Sections\\\\\\\",\\\\\\\"Sections:Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Item Units:View Item Units\\\\\\\",\\\\\\\"Item Units:Edit Item Units\\\\\\\",\\\\\\\"Item Units:Add Item Units\\\\\\\",\\\\\\\"Item Units:Delete Item Units\\\\\\\",\\\\\\\"Item Units:Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Groups:View Groups\\\\\\\",\\\\\\\"Groups:Edit Groups\\\\\\\",\\\\\\\"Groups:Add Groups\\\\\\\",\\\\\\\"Groups:Delete Groups\\\\\\\",\\\\\\\"Groups:Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Patient Categories:View Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Edit Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Add Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Delete Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Suppliers:View Suppliers\\\\\\\",\\\\\\\"Suppliers:Edit Suppliers\\\\\\\",\\\\\\\"Suppliers:Add Suppliers\\\\\\\",\\\\\\\"Suppliers:Delete Suppliers\\\\\\\",\\\\\\\"Suppliers:Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Stores:View Stores\\\\\\\",\\\\\\\"Stores:Edit Stores\\\\\\\",\\\\\\\"Stores:Add Stores\\\\\\\",\\\\\\\"Stores:Delete Stores\\\\\\\",\\\\\\\"Stores:Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:View Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Edit Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Add Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Delete Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Sub Groups:View Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Edit Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Add Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Delete Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Admin Users:View Admin Users\\\\\\\",\\\\\\\"Admin Users:Edit Admin Users\\\\\\\",\\\\\\\"Admin Users:Add Admin Users\\\\\\\",\\\\\\\"Admin Users:Delete Admin Users\\\\\\\",\\\\\\\"Admin Users:Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"Audit Logs:View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"System Settings:View System Settings\\\\\\\",\\\\\\\"System Settings:Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business:View Business\\\\\\\",\\\\\\\"Business:Edit Business\\\\\\\",\\\\\\\"Business:Add Business\\\\\\\",\\\\\\\"Business:Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Branches:View Branches\\\\\\\",\\\\\\\"Branches:Edit Branches\\\\\\\",\\\\\\\"Branches:Add Branches\\\\\\\",\\\\\\\"Branches:Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Reports\\\\\\\",\\\\\\\"Reports:Export Reports\\\\\\\",\\\\\\\"Reports:Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"updated_at\\\":\\\"2025-07-27 08:13:47\\\",\\\"deleted_at\\\":null}\"', '2a0d:5600:140:5002:1119:f61a:cd6:cc0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', NULL, '', '2025-07-20', '2025-07-27 08:13:47', '2025-07-27 08:13:47', NULL),
(11, '3ef85795-9ce2-434a-bcee-cb5f2a7007ab', 4, 1, 1, 'App\\Models\\User', 4, 'updated', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"Sr5yBotxNRa9XPz0ekbgfNtJTzFyAylio6gPpUFcHQWK5zXeSiqRK2wMVBRG\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":[],\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"Dashboard:View Dashboard\\\",\\\"Dashboard:View Dashboard Cards\\\",\\\"Dashboard:View Dashboard Charts\\\",\\\"Dashboard:View Dashboard Tables\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"Entities:View Entities\\\",\\\"Entities:Edit Entities\\\",\\\"Entities:Add Entities\\\",\\\"Entities:Delete Entities\\\",\\\"Departments\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"Reports:View Report\\\",\\\"Reports:Edit Report\\\",\\\"Reports:Add Report\\\",\\\"Reports:Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"Products:View Products\\\",\\\"Products:Edit Products\\\",\\\"Products:Add Products\\\",\\\"Products:Delete Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"Sales:View Sales\\\",\\\"Sales:Edit Sales\\\",\\\"Sales:Add Sales\\\",\\\"Sales:Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"Customers:View Queues\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"Stock\\\",\\\"Stock\\\",\\\"Stock:View Stock\\\",\\\"Stock:Edit Stock\\\",\\\"Stock:Add Stock\\\",\\\"Stock:Delete Stock\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"Service Points:View Service Points\\\",\\\"Service Points:Edit Service Points\\\",\\\"Service Points:Add Service Points\\\",\\\"Service Points:Delete Service Points\\\",\\\"Service Points:Bulky Update Service Points\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Departments:Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"Qualifications:View Qualifications\\\",\\\"Qualifications:Edit Qualifications\\\",\\\"Qualifications:Add Qualifications\\\",\\\"Qualifications:Delete Qualifications\\\",\\\"Qualifications:Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"Titles:View Titles\\\",\\\"Titles:Edit Titles\\\",\\\"Titles:Add Titles\\\",\\\"Titles:Delete Titles\\\",\\\"Titles:Bulky Update Titles\\\",\\\"Rooms\\\",\\\"Rooms:View Rooms\\\",\\\"Rooms:Edit Rooms\\\",\\\"Rooms:Add Rooms\\\",\\\"Rooms:Delete Rooms\\\",\\\"Rooms:Bulky Update Rooms\\\",\\\"Sections\\\",\\\"Sections:View Sections\\\",\\\"Sections:Edit Sections\\\",\\\"Sections:Add Sections\\\",\\\"Sections:Delete Sections\\\",\\\"Sections:Bulky Update Sections\\\",\\\"Item Units\\\",\\\"Item Units:View Item Units\\\",\\\"Item Units:Edit Item Units\\\",\\\"Item Units:Add Item Units\\\",\\\"Item Units:Delete Item Units\\\",\\\"Item Units:Bulky Update Item Units\\\",\\\"Groups\\\",\\\"Groups:View Groups\\\",\\\"Groups:Edit Groups\\\",\\\"Groups:Add Groups\\\",\\\"Groups:Delete Groups\\\",\\\"Groups:Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"Patient Categories:View Patient Categories\\\",\\\"Patient Categories:Edit Patient Categories\\\",\\\"Patient Categories:Add Patient Categories\\\",\\\"Patient Categories:Delete Patient Categories\\\",\\\"Patient Categories:Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"Suppliers:View Suppliers\\\",\\\"Suppliers:Edit Suppliers\\\",\\\"Suppliers:Add Suppliers\\\",\\\"Suppliers:Delete Suppliers\\\",\\\"Suppliers:Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"Stores:View Stores\\\",\\\"Stores:Edit Stores\\\",\\\"Stores:Add Stores\\\",\\\"Stores:Delete Stores\\\",\\\"Stores:Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"Insurance Companies:View Insurance Companies\\\",\\\"Insurance Companies:Edit Insurance Companies\\\",\\\"Insurance Companies:Add Insurance Companies\\\",\\\"Insurance Companies:Delete Insurance Companies\\\",\\\"Insurance Companies:Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"Sub Groups:View Sub Groups\\\",\\\"Sub Groups:Edit Sub Groups\\\",\\\"Sub Groups:Add Sub Groups\\\",\\\"Sub Groups:Delete Sub Groups\\\",\\\"Sub Groups:Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"Admin Users:View Admin Users\\\",\\\"Admin Users:Edit Admin Users\\\",\\\"Admin Users:Add Admin Users\\\",\\\"Admin Users:Delete Admin Users\\\",\\\"Admin Users:Assign Roles\\\",\\\"Audit Logs\\\",\\\"Audit Logs:View Audit Logs\\\",\\\"System Settings\\\",\\\"System Settings:View System Settings\\\",\\\"System Settings:Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"Business:View Business\\\",\\\"Business:Edit Business\\\",\\\"Business:Add Business\\\",\\\"Business:Delete Business\\\",\\\"Branches\\\",\\\"Branches:View Branches\\\",\\\"Branches:Edit Branches\\\",\\\"Branches:Add Branches\\\",\\\"Branches:Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"Reports:View Reports\\\",\\\"Reports:Export Reports\\\",\\\"Reports:Filter Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload:Bulk Validations Upload\\\"],\\\"allowed_branches\\\":[1],\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23T18:33:21.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-27T08:13:47.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"1pkhMdGs1BXFtCIqZCkByRelB01kRoSpxtoMUyuCZXnsqHKj4nUXNjbpk55C\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard:View Dashboard\\\\\\\",\\\\\\\"Dashboard:View Dashboard Cards\\\\\\\",\\\\\\\"Dashboard:View Dashboard Charts\\\\\\\",\\\\\\\"Dashboard:View Dashboard Tables\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities:View Entities\\\\\\\",\\\\\\\"Entities:Edit Entities\\\\\\\",\\\\\\\"Entities:Add Entities\\\\\\\",\\\\\\\"Entities:Delete Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Report\\\\\\\",\\\\\\\"Reports:Edit Report\\\\\\\",\\\\\\\"Reports:Add Report\\\\\\\",\\\\\\\"Reports:Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products:View Products\\\\\\\",\\\\\\\"Products:Edit Products\\\\\\\",\\\\\\\"Products:Add Products\\\\\\\",\\\\\\\"Products:Delete Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales:View Sales\\\\\\\",\\\\\\\"Sales:Edit Sales\\\\\\\",\\\\\\\"Sales:Add Sales\\\\\\\",\\\\\\\"Sales:Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Customers:View Queues\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock:View Stock\\\\\\\",\\\\\\\"Stock:Edit Stock\\\\\\\",\\\\\\\"Stock:Add Stock\\\\\\\",\\\\\\\"Stock:Delete Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Service Points:View Service Points\\\\\\\",\\\\\\\"Service Points:Edit Service Points\\\\\\\",\\\\\\\"Service Points:Add Service Points\\\\\\\",\\\\\\\"Service Points:Delete Service Points\\\\\\\",\\\\\\\"Service Points:Bulky Update Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Departments:Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Qualifications:View Qualifications\\\\\\\",\\\\\\\"Qualifications:Edit Qualifications\\\\\\\",\\\\\\\"Qualifications:Add Qualifications\\\\\\\",\\\\\\\"Qualifications:Delete Qualifications\\\\\\\",\\\\\\\"Qualifications:Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Titles:View Titles\\\\\\\",\\\\\\\"Titles:Edit Titles\\\\\\\",\\\\\\\"Titles:Add Titles\\\\\\\",\\\\\\\"Titles:Delete Titles\\\\\\\",\\\\\\\"Titles:Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Rooms:View Rooms\\\\\\\",\\\\\\\"Rooms:Edit Rooms\\\\\\\",\\\\\\\"Rooms:Add Rooms\\\\\\\",\\\\\\\"Rooms:Delete Rooms\\\\\\\",\\\\\\\"Rooms:Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Sections:View Sections\\\\\\\",\\\\\\\"Sections:Edit Sections\\\\\\\",\\\\\\\"Sections:Add Sections\\\\\\\",\\\\\\\"Sections:Delete Sections\\\\\\\",\\\\\\\"Sections:Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Item Units:View Item Units\\\\\\\",\\\\\\\"Item Units:Edit Item Units\\\\\\\",\\\\\\\"Item Units:Add Item Units\\\\\\\",\\\\\\\"Item Units:Delete Item Units\\\\\\\",\\\\\\\"Item Units:Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Groups:View Groups\\\\\\\",\\\\\\\"Groups:Edit Groups\\\\\\\",\\\\\\\"Groups:Add Groups\\\\\\\",\\\\\\\"Groups:Delete Groups\\\\\\\",\\\\\\\"Groups:Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Patient Categories:View Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Edit Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Add Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Delete Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Suppliers:View Suppliers\\\\\\\",\\\\\\\"Suppliers:Edit Suppliers\\\\\\\",\\\\\\\"Suppliers:Add Suppliers\\\\\\\",\\\\\\\"Suppliers:Delete Suppliers\\\\\\\",\\\\\\\"Suppliers:Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Stores:View Stores\\\\\\\",\\\\\\\"Stores:Edit Stores\\\\\\\",\\\\\\\"Stores:Add Stores\\\\\\\",\\\\\\\"Stores:Delete Stores\\\\\\\",\\\\\\\"Stores:Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:View Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Edit Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Add Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Delete Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Sub Groups:View Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Edit Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Add Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Delete Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Admin Users:View Admin Users\\\\\\\",\\\\\\\"Admin Users:Edit Admin Users\\\\\\\",\\\\\\\"Admin Users:Add Admin Users\\\\\\\",\\\\\\\"Admin Users:Delete Admin Users\\\\\\\",\\\\\\\"Admin Users:Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"Audit Logs:View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"System Settings:View System Settings\\\\\\\",\\\\\\\"System Settings:Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business:View Business\\\\\\\",\\\\\\\"Business:Edit Business\\\\\\\",\\\\\\\"Business:Add Business\\\\\\\",\\\\\\\"Business:Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Branches:View Branches\\\\\\\",\\\\\\\"Branches:Edit Branches\\\\\\\",\\\\\\\"Branches:Add Branches\\\\\\\",\\\\\\\"Branches:Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Reports\\\\\\\",\\\\\\\"Reports:Export Reports\\\\\\\",\\\\\\\"Reports:Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"updated_at\\\":\\\"2025-07-27 08:13:47\\\",\\\"deleted_at\\\":null}\"', '2a0d:5600:140:5002:1119:f61a:cd6:cc0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', NULL, '', '2025-07-20', '2025-07-27 08:17:42', '2025-07-27 08:17:42', NULL),
(12, '9691ea28-9dd5-4ddd-96a7-599d3b522aad', 4, 1, 1, 'App\\Models\\User', 4, 'updated', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"1pkhMdGs1BXFtCIqZCkByRelB01kRoSpxtoMUyuCZXnsqHKj4nUXNjbpk55C\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":[],\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"Dashboard:View Dashboard\\\",\\\"Dashboard:View Dashboard Cards\\\",\\\"Dashboard:View Dashboard Charts\\\",\\\"Dashboard:View Dashboard Tables\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"Entities:View Entities\\\",\\\"Entities:Edit Entities\\\",\\\"Entities:Add Entities\\\",\\\"Entities:Delete Entities\\\",\\\"Departments\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"Reports:View Report\\\",\\\"Reports:Edit Report\\\",\\\"Reports:Add Report\\\",\\\"Reports:Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"Products:View Products\\\",\\\"Products:Edit Products\\\",\\\"Products:Add Products\\\",\\\"Products:Delete Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"Sales:View Sales\\\",\\\"Sales:Edit Sales\\\",\\\"Sales:Add Sales\\\",\\\"Sales:Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"Customers:View Queues\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"Stock\\\",\\\"Stock\\\",\\\"Stock:View Stock\\\",\\\"Stock:Edit Stock\\\",\\\"Stock:Add Stock\\\",\\\"Stock:Delete Stock\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"Service Points:View Service Points\\\",\\\"Service Points:Edit Service Points\\\",\\\"Service Points:Add Service Points\\\",\\\"Service Points:Delete Service Points\\\",\\\"Service Points:Bulky Update Service Points\\\",\\\"Departments\\\",\\\"Departments:View Departments\\\",\\\"Departments:Edit Departments\\\",\\\"Departments:Add Departments\\\",\\\"Departments:Delete Departments\\\",\\\"Departments:Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"Qualifications:View Qualifications\\\",\\\"Qualifications:Edit Qualifications\\\",\\\"Qualifications:Add Qualifications\\\",\\\"Qualifications:Delete Qualifications\\\",\\\"Qualifications:Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"Titles:View Titles\\\",\\\"Titles:Edit Titles\\\",\\\"Titles:Add Titles\\\",\\\"Titles:Delete Titles\\\",\\\"Titles:Bulky Update Titles\\\",\\\"Rooms\\\",\\\"Rooms:View Rooms\\\",\\\"Rooms:Edit Rooms\\\",\\\"Rooms:Add Rooms\\\",\\\"Rooms:Delete Rooms\\\",\\\"Rooms:Bulky Update Rooms\\\",\\\"Sections\\\",\\\"Sections:View Sections\\\",\\\"Sections:Edit Sections\\\",\\\"Sections:Add Sections\\\",\\\"Sections:Delete Sections\\\",\\\"Sections:Bulky Update Sections\\\",\\\"Item Units\\\",\\\"Item Units:View Item Units\\\",\\\"Item Units:Edit Item Units\\\",\\\"Item Units:Add Item Units\\\",\\\"Item Units:Delete Item Units\\\",\\\"Item Units:Bulky Update Item Units\\\",\\\"Groups\\\",\\\"Groups:View Groups\\\",\\\"Groups:Edit Groups\\\",\\\"Groups:Add Groups\\\",\\\"Groups:Delete Groups\\\",\\\"Groups:Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"Patient Categories:View Patient Categories\\\",\\\"Patient Categories:Edit Patient Categories\\\",\\\"Patient Categories:Add Patient Categories\\\",\\\"Patient Categories:Delete Patient Categories\\\",\\\"Patient Categories:Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"Suppliers:View Suppliers\\\",\\\"Suppliers:Edit Suppliers\\\",\\\"Suppliers:Add Suppliers\\\",\\\"Suppliers:Delete Suppliers\\\",\\\"Suppliers:Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"Stores:View Stores\\\",\\\"Stores:Edit Stores\\\",\\\"Stores:Add Stores\\\",\\\"Stores:Delete Stores\\\",\\\"Stores:Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"Insurance Companies:View Insurance Companies\\\",\\\"Insurance Companies:Edit Insurance Companies\\\",\\\"Insurance Companies:Add Insurance Companies\\\",\\\"Insurance Companies:Delete Insurance Companies\\\",\\\"Insurance Companies:Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"Sub Groups:View Sub Groups\\\",\\\"Sub Groups:Edit Sub Groups\\\",\\\"Sub Groups:Add Sub Groups\\\",\\\"Sub Groups:Delete Sub Groups\\\",\\\"Sub Groups:Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"Admin Users:View Admin Users\\\",\\\"Admin Users:Edit Admin Users\\\",\\\"Admin Users:Add Admin Users\\\",\\\"Admin Users:Delete Admin Users\\\",\\\"Admin Users:Assign Roles\\\",\\\"Audit Logs\\\",\\\"Audit Logs:View Audit Logs\\\",\\\"System Settings\\\",\\\"System Settings:View System Settings\\\",\\\"System Settings:Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"Business:View Business\\\",\\\"Business:Edit Business\\\",\\\"Business:Add Business\\\",\\\"Business:Delete Business\\\",\\\"Branches\\\",\\\"Branches:View Branches\\\",\\\"Branches:Edit Branches\\\",\\\"Branches:Add Branches\\\",\\\"Branches:Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"Clients:View Clients\\\",\\\"Clients:Edit Clients\\\",\\\"Clients:Add Clients\\\",\\\"Clients:Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"Staff:View Staff\\\",\\\"Staff:Edit Staff\\\",\\\"Staff:Add Staff\\\",\\\"Staff:Delete Staff\\\",\\\"Staff:Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"Reports:View Reports\\\",\\\"Reports:Export Reports\\\",\\\"Reports:Filter Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload:Bulk Validations Upload\\\"],\\\"allowed_branches\\\":[1],\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23T18:33:21.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-27T08:13:47.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":4,\\\"uuid\\\":\\\"332e1f47-dc52-44c1-88e0-84f631b73f25\\\",\\\"name\\\":\\\"Andrew Muleledhu\\\",\\\"email\\\":\\\"muleledhu@yahoo.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$l4T2FmtM2QBxi0\\\\\\/TW4f\\\\\\/TOIVPB3DiYVsONf1wenMVSF\\\\\\/haQSFOm8a\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"0751318504\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RFQ\\\",\\\"remember_token\\\":\\\"yazG5AcN5akG7ABRbHnS1xS6Z5Lq2sggHqLbVXTYGEVhncyQxSyMc0YLpuYv\\\",\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard:View Dashboard\\\\\\\",\\\\\\\"Dashboard:View Dashboard Cards\\\\\\\",\\\\\\\"Dashboard:View Dashboard Charts\\\\\\\",\\\\\\\"Dashboard:View Dashboard Tables\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities:View Entities\\\\\\\",\\\\\\\"Entities:Edit Entities\\\\\\\",\\\\\\\"Entities:Add Entities\\\\\\\",\\\\\\\"Entities:Delete Entities\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Report\\\\\\\",\\\\\\\"Reports:Edit Report\\\\\\\",\\\\\\\"Reports:Add Report\\\\\\\",\\\\\\\"Reports:Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products\\\\\\\",\\\\\\\"Products:View Products\\\\\\\",\\\\\\\"Products:Edit Products\\\\\\\",\\\\\\\"Products:Add Products\\\\\\\",\\\\\\\"Products:Delete Products\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales:View Sales\\\\\\\",\\\\\\\"Sales:Edit Sales\\\\\\\",\\\\\\\"Sales:Add Sales\\\\\\\",\\\\\\\"Sales:Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"Customers:View Queues\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock:View Stock\\\\\\\",\\\\\\\"Stock:Edit Stock\\\\\\\",\\\\\\\"Stock:Add Stock\\\\\\\",\\\\\\\"Stock:Delete Stock\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"Service Points:View Service Points\\\\\\\",\\\\\\\"Service Points:Edit Service Points\\\\\\\",\\\\\\\"Service Points:Add Service Points\\\\\\\",\\\\\\\"Service Points:Delete Service Points\\\\\\\",\\\\\\\"Service Points:Bulky Update Service Points\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"Departments:View Departments\\\\\\\",\\\\\\\"Departments:Edit Departments\\\\\\\",\\\\\\\"Departments:Add Departments\\\\\\\",\\\\\\\"Departments:Delete Departments\\\\\\\",\\\\\\\"Departments:Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"Qualifications:View Qualifications\\\\\\\",\\\\\\\"Qualifications:Edit Qualifications\\\\\\\",\\\\\\\"Qualifications:Add Qualifications\\\\\\\",\\\\\\\"Qualifications:Delete Qualifications\\\\\\\",\\\\\\\"Qualifications:Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"Titles:View Titles\\\\\\\",\\\\\\\"Titles:Edit Titles\\\\\\\",\\\\\\\"Titles:Add Titles\\\\\\\",\\\\\\\"Titles:Delete Titles\\\\\\\",\\\\\\\"Titles:Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"Rooms:View Rooms\\\\\\\",\\\\\\\"Rooms:Edit Rooms\\\\\\\",\\\\\\\"Rooms:Add Rooms\\\\\\\",\\\\\\\"Rooms:Delete Rooms\\\\\\\",\\\\\\\"Rooms:Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"Sections:View Sections\\\\\\\",\\\\\\\"Sections:Edit Sections\\\\\\\",\\\\\\\"Sections:Add Sections\\\\\\\",\\\\\\\"Sections:Delete Sections\\\\\\\",\\\\\\\"Sections:Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"Item Units:View Item Units\\\\\\\",\\\\\\\"Item Units:Edit Item Units\\\\\\\",\\\\\\\"Item Units:Add Item Units\\\\\\\",\\\\\\\"Item Units:Delete Item Units\\\\\\\",\\\\\\\"Item Units:Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"Groups:View Groups\\\\\\\",\\\\\\\"Groups:Edit Groups\\\\\\\",\\\\\\\"Groups:Add Groups\\\\\\\",\\\\\\\"Groups:Delete Groups\\\\\\\",\\\\\\\"Groups:Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"Patient Categories:View Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Edit Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Add Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Delete Patient Categories\\\\\\\",\\\\\\\"Patient Categories:Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"Suppliers:View Suppliers\\\\\\\",\\\\\\\"Suppliers:Edit Suppliers\\\\\\\",\\\\\\\"Suppliers:Add Suppliers\\\\\\\",\\\\\\\"Suppliers:Delete Suppliers\\\\\\\",\\\\\\\"Suppliers:Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"Stores:View Stores\\\\\\\",\\\\\\\"Stores:Edit Stores\\\\\\\",\\\\\\\"Stores:Add Stores\\\\\\\",\\\\\\\"Stores:Delete Stores\\\\\\\",\\\\\\\"Stores:Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:View Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Edit Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Add Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Delete Insurance Companies\\\\\\\",\\\\\\\"Insurance Companies:Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"Sub Groups:View Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Edit Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Add Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Delete Sub Groups\\\\\\\",\\\\\\\"Sub Groups:Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"Admin Users:View Admin Users\\\\\\\",\\\\\\\"Admin Users:Edit Admin Users\\\\\\\",\\\\\\\"Admin Users:Add Admin Users\\\\\\\",\\\\\\\"Admin Users:Delete Admin Users\\\\\\\",\\\\\\\"Admin Users:Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"Audit Logs:View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"System Settings:View System Settings\\\\\\\",\\\\\\\"System Settings:Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business:View Business\\\\\\\",\\\\\\\"Business:Edit Business\\\\\\\",\\\\\\\"Business:Add Business\\\\\\\",\\\\\\\"Business:Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"Branches:View Branches\\\\\\\",\\\\\\\"Branches:Edit Branches\\\\\\\",\\\\\\\"Branches:Add Branches\\\\\\\",\\\\\\\"Branches:Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients:View Clients\\\\\\\",\\\\\\\"Clients:Edit Clients\\\\\\\",\\\\\\\"Clients:Add Clients\\\\\\\",\\\\\\\"Clients:Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff:View Staff\\\\\\\",\\\\\\\"Staff:Edit Staff\\\\\\\",\\\\\\\"Staff:Add Staff\\\\\\\",\\\\\\\"Staff:Delete Staff\\\\\\\",\\\\\\\"Staff:Assign Roles\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports:View Reports\\\\\\\",\\\\\\\"Reports:Export Reports\\\\\\\",\\\\\\\"Reports:Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload:Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"title_id\\\":null,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-23 18:33:21\\\",\\\"updated_at\\\":\\\"2025-07-27 08:13:47\\\",\\\"deleted_at\\\":null}\"', '2a0d:5600:140:3002:534b:446:19d6:7fee', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:141.0) Gecko/20100101 Firefox/141.0', NULL, '', '2025-07-20', '2025-07-28 18:15:04', '2025-07-28 18:15:04', NULL),
(13, 'b5532df7-aba0-423e-ad5a-aceb89e36bc6', 1, 1, 1, 'App\\Models\\User', 5, 'created', NULL, '\"{\\\"name\\\":\\\"Test  Admin One\\\",\\\"email\\\":\\\"tracepeso@gmail.com\\\",\\\"phone\\\":\\\"\\\",\\\"nin\\\":\\\"CMQERTYTOYO\\\",\\\"gender\\\":\\\"male\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"status\\\":\\\"active\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"permissions\\\":\\\"[\\\\\\\"\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"service_points\\\":\\\"[]\\\",\\\"uuid\\\":\\\"884a5b39-16fb-4723-b800-75b839372bb5\\\",\\\"updated_at\\\":\\\"2025-07-29 20:35:27\\\",\\\"created_at\\\":\\\"2025-07-29 20:35:27\\\",\\\"id\\\":5}\"', '197.239.12.114', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-29 20:35:27', '2025-07-29 20:35:27', NULL),
(14, '24a9a395-22b3-4841-81f1-be9258cb84ef', 1, 1, 1, 'App\\Models\\User', 6, 'created', NULL, '\"{\\\"name\\\":\\\"Test  Admin Two\\\",\\\"email\\\":\\\"katznicho+9834@gmail.com\\\",\\\"phone\\\":\\\"\\\",\\\"nin\\\":\\\"CMQERTYTOYO1D\\\",\\\"gender\\\":\\\"male\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"status\\\":\\\"active\\\",\\\"allowed_branches\\\":\\\"[1]\\\",\\\"permissions\\\":\\\"[\\\\\\\"\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"service_points\\\":\\\"[]\\\",\\\"uuid\\\":\\\"3971435b-4524-444d-b389-b4bd3d9e9717\\\",\\\"updated_at\\\":\\\"2025-07-29 20:35:27\\\",\\\"created_at\\\":\\\"2025-07-29 20:35:27\\\",\\\"id\\\":6}\"', '197.239.12.114', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-29 20:35:27', '2025-07-29 20:35:27', NULL),
(15, '7baca6ef-8325-4ae7-b964-67efe6484627', 1, 1, 1, 'App\\Models\\User', 7, 'created', NULL, '\"{\\\"name\\\":\\\"Sample Staff Name\\\",\\\"email\\\":\\\"katznicho+734563@gmail.com\\\",\\\"phone\\\":\\\"1234567890\\\",\\\"nin\\\":\\\"CMRJRTJTRWQWER\\\",\\\"gender\\\":\\\"male\\\",\\\"status\\\":\\\"active\\\",\\\"business_id\\\":\\\"4\\\",\\\"branch_id\\\":\\\"2\\\",\\\"qualification_id\\\":null,\\\"title_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"service_points\\\":\\\"[]\\\",\\\"allowed_branches\\\":\\\"[\\\\\\\"2\\\\\\\"]\\\",\\\"permissions\\\":\\\"[\\\\\\\"View Dashboard\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"uuid\\\":\\\"634b6347-5c1b-4b26-b276-264f3a912395\\\",\\\"updated_at\\\":\\\"2025-07-29 20:57:40\\\",\\\"created_at\\\":\\\"2025-07-29 20:57:40\\\",\\\"id\\\":7}\"', '197.239.12.114', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-29 20:57:40', '2025-07-29 20:57:40', NULL),
(16, '51a6a288-e3e7-4e8c-bb74-08cd90873d9b', 1, 1, 1, 'App\\Models\\User', 8, 'created', NULL, '\"{\\\"name\\\":\\\"Sample Contractor Name\\\",\\\"email\\\":\\\"tracepeso+123@gmail.com\\\",\\\"phone\\\":\\\"0987654321\\\",\\\"nin\\\":\\\"CMRJRTJTRWQWERQE\\\",\\\"gender\\\":\\\"female\\\",\\\"status\\\":\\\"active\\\",\\\"business_id\\\":\\\"4\\\",\\\"branch_id\\\":\\\"2\\\",\\\"qualification_id\\\":null,\\\"title_id\\\":null,\\\"department_id\\\":null,\\\"section_id\\\":null,\\\"service_points\\\":\\\"[]\\\",\\\"allowed_branches\\\":\\\"[\\\\\\\"2\\\\\\\"]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Contractor\\\\\\\",\\\\\\\"View Contractor\\\\\\\",\\\\\\\"Edit Contractor\\\\\\\",\\\\\\\"Add Contractor\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"uuid\\\":\\\"335430e3-cf25-451d-b6b0-8330adecd1bc\\\",\\\"updated_at\\\":\\\"2025-07-29 20:57:40\\\",\\\"created_at\\\":\\\"2025-07-29 20:57:40\\\",\\\"id\\\":8}\"', '197.239.12.114', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-07-29 20:57:40', '2025-07-29 20:57:40', NULL);
INSERT INTO `activity_logs` (`id`, `uuid`, `user_id`, `business_id`, `branch_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `action_type`, `description`, `date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(17, 'da1a29fa-bd47-4c2f-be08-d34146b1c889', 1, 1, 1, 'App\\Models\\User', 9, 'created', NULL, '\"{\\\"name\\\":\\\"Kikomeko Huzairu\\\",\\\"email\\\":\\\"kikomekohudhairuh@gmail.com\\\",\\\"status\\\":\\\"active\\\",\\\"business_id\\\":\\\"3\\\",\\\"branch_id\\\":\\\"4\\\",\\\"profile_photo_path\\\":null,\\\"phone\\\":\\\"0759950503\\\",\\\"nin\\\":\\\"EHFJFGNGFG,RF\\\",\\\"gender\\\":\\\"male\\\",\\\"qualification_id\\\":\\\"7\\\",\\\"department_id\\\":\\\"7\\\",\\\"section_id\\\":\\\"6\\\",\\\"title_id\\\":\\\"7\\\",\\\"service_points\\\":\\\"[\\\\\\\"Pharmacy\\\\\\\",\\\\\\\"Endoscopy Procedure\\\\\\\",\\\\\\\"Consultation Dr KP\\\\\\\",\\\\\\\"Consultation Dr AV\\\\\\\",\\\\\\\"Consultation Dr KG\\\\\\\",\\\\\\\"Consultation Dr NV\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[\\\\\\\"3\\\\\\\",\\\\\\\"4\\\\\\\",\\\\\\\"5\\\\\\\"]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"View Entities\\\\\\\",\\\\\\\"Edit Entities\\\\\\\",\\\\\\\"Add Entities\\\\\\\",\\\\\\\"Delete Entities\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"View Items\\\\\\\",\\\\\\\"Edit Items\\\\\\\",\\\\\\\"Add Items\\\\\\\",\\\\\\\"Delete Items\\\\\\\",\\\\\\\"Bulk Upload Items\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Contractor\\\\\\\",\\\\\\\"Contractor\\\\\\\",\\\\\\\"View Contractor\\\\\\\",\\\\\\\"Edit Contractor\\\\\\\",\\\\\\\"Add Contractor Profile\\\\\\\",\\\\\\\"View Contractor Profile\\\\\\\",\\\\\\\"Edit Contractor Profile\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"uuid\\\":\\\"9947cb86-7181-4108-be23-61e515d28b0b\\\",\\\"updated_at\\\":\\\"2025-08-09 08:26:39\\\",\\\"created_at\\\":\\\"2025-08-09 08:26:39\\\",\\\"id\\\":9}\"', '102.86.7.246', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-09 08:26:39', '2025-08-09 08:26:39', NULL),
(18, '992f7518-93e1-496c-9958-3bc6a5dd334a', 1, 1, 1, 'App\\Models\\User', 1, 'updated', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"View Dashboard\\\",\\\"View Dashboard Cards\\\",\\\"View Dashboard Charts\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"View Entities\\\",\\\"Edit Entities\\\",\\\"Add Entities\\\",\\\"Delete Entities\\\",\\\"Items\\\",\\\"Items\\\",\\\"View Items\\\",\\\"Edit Items\\\",\\\"Add Items\\\",\\\"Delete Items\\\",\\\"Bulk Upload Items\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"View Staff\\\",\\\"Edit Staff\\\",\\\"Add Staff\\\",\\\"Delete Staff\\\",\\\"Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"View Report\\\",\\\"Edit Report\\\",\\\"Add Report\\\",\\\"Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"View Logs\\\",\\\"Products\\\",\\\"Products\\\",\\\"View Products\\\",\\\"Edit Products\\\",\\\"Add Products\\\",\\\"Delete Products\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"View Sales\\\",\\\"Edit Sales\\\",\\\"Add Sales\\\",\\\"Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"View Clients\\\",\\\"Edit Clients\\\",\\\"Add Clients\\\",\\\"Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"View Queues\\\",\\\"Withdrawals\\\",\\\"Withdrawals\\\",\\\"View Withdrawals\\\",\\\"Edit Withdrawals\\\",\\\"Add Withdrawals\\\",\\\"Delete Withdrawals\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"View Modules\\\",\\\"Edit Modules\\\",\\\"Add Modules\\\",\\\"Delete Modules\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"View Service Points\\\",\\\"Edit Service Points\\\",\\\"Add Service Points\\\",\\\"Delete Service Points\\\",\\\"Bulky Update Service Points\\\",\\\"Departments\\\",\\\"View Departments\\\",\\\"Edit Departments\\\",\\\"Add Departments\\\",\\\"Delete Departments\\\",\\\"Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"View Qualifications\\\",\\\"Edit Qualifications\\\",\\\"Add Qualifications\\\",\\\"Delete Qualifications\\\",\\\"Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"View Titles\\\",\\\"Edit Titles\\\",\\\"Add Titles\\\",\\\"Delete Titles\\\",\\\"Bulky Update Titles\\\",\\\"Rooms\\\",\\\"View Rooms\\\",\\\"Edit Rooms\\\",\\\"Add Rooms\\\",\\\"Delete Rooms\\\",\\\"Bulky Update Rooms\\\",\\\"Sections\\\",\\\"View Sections\\\",\\\"Edit Sections\\\",\\\"Add Sections\\\",\\\"Delete Sections\\\",\\\"Bulky Update Sections\\\",\\\"Item Units\\\",\\\"View Item Units\\\",\\\"Edit Item Units\\\",\\\"Add Item Units\\\",\\\"Delete Item Units\\\",\\\"Bulky Update Item Units\\\",\\\"Groups\\\",\\\"View Groups\\\",\\\"Edit Groups\\\",\\\"Add Groups\\\",\\\"Delete Groups\\\",\\\"Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"View Patient Categories\\\",\\\"Edit Patient Categories\\\",\\\"Add Patient Categories\\\",\\\"Delete Patient Categories\\\",\\\"Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"View Suppliers\\\",\\\"Edit Suppliers\\\",\\\"Add Suppliers\\\",\\\"Delete Suppliers\\\",\\\"Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"View Stores\\\",\\\"Edit Stores\\\",\\\"Add Stores\\\",\\\"Delete Stores\\\",\\\"Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"View Insurance Companies\\\",\\\"Edit Insurance Companies\\\",\\\"Add Insurance Companies\\\",\\\"Delete Insurance Companies\\\",\\\"Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"View Sub Groups\\\",\\\"Edit Sub Groups\\\",\\\"Add Sub Groups\\\",\\\"Delete Sub Groups\\\",\\\"Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"View Admin Users\\\",\\\"Edit Admin Users\\\",\\\"Add Admin Users\\\",\\\"Delete Admin Users\\\",\\\"Assign Roles\\\",\\\"Audit Logs\\\",\\\"View Audit Logs\\\",\\\"System Settings\\\",\\\"View System Settings\\\",\\\"Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"View Business\\\",\\\"Edit Business\\\",\\\"Add Business\\\",\\\"Delete Business\\\",\\\"Branches\\\",\\\"View Branches\\\",\\\"Edit Branches\\\",\\\"Add Branches\\\",\\\"Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"View Clients\\\",\\\"Edit Clients\\\",\\\"Add Clients\\\",\\\"Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"View Staff\\\",\\\"Edit Staff\\\",\\\"Add Staff\\\",\\\"Delete Staff\\\",\\\"Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"View Reports\\\",\\\"Export Reports\\\",\\\"Filter Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Validations Upload\\\"],\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20T20:05:52.000000Z\\\",\\\"updated_at\\\":\\\"2025-07-25T19:00:49.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"\\\\\\\"[]\\\\\\\"\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"View Entities\\\\\\\",\\\\\\\"Edit Entities\\\\\\\",\\\\\\\"Add Entities\\\\\\\",\\\\\\\"Delete Entities\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"View Items\\\\\\\",\\\\\\\"Edit Items\\\\\\\",\\\\\\\"Add Items\\\\\\\",\\\\\\\"Delete Items\\\\\\\",\\\\\\\"Bulk Upload Items\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Report\\\\\\\",\\\\\\\"Edit Report\\\\\\\",\\\\\\\"Add Report\\\\\\\",\\\\\\\"Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"View Logs\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"View Sales\\\\\\\",\\\\\\\"Edit Sales\\\\\\\",\\\\\\\"Add Sales\\\\\\\",\\\\\\\"Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"View Clients\\\\\\\",\\\\\\\"Edit Clients\\\\\\\",\\\\\\\"Add Clients\\\\\\\",\\\\\\\"Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"View Queues\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"View Withdrawals\\\\\\\",\\\\\\\"Edit Withdrawals\\\\\\\",\\\\\\\"Add Withdrawals\\\\\\\",\\\\\\\"Delete Withdrawals\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"View Modules\\\\\\\",\\\\\\\"Edit Modules\\\\\\\",\\\\\\\"Add Modules\\\\\\\",\\\\\\\"Delete Modules\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"View Service Points\\\\\\\",\\\\\\\"Edit Service Points\\\\\\\",\\\\\\\"Add Service Points\\\\\\\",\\\\\\\"Delete Service Points\\\\\\\",\\\\\\\"Bulky Update Service Points\\\\\\\",\\\\\\\"Service Charges\\\\\\\",\\\\\\\"Manage Service Charges\\\\\\\",\\\\\\\"Contractor Service Charges\\\\\\\",\\\\\\\"Manage Contractor Service Charges\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"View Departments\\\\\\\",\\\\\\\"Edit Departments\\\\\\\",\\\\\\\"Add Departments\\\\\\\",\\\\\\\"Delete Departments\\\\\\\",\\\\\\\"Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"View Qualifications\\\\\\\",\\\\\\\"Edit Qualifications\\\\\\\",\\\\\\\"Add Qualifications\\\\\\\",\\\\\\\"Delete Qualifications\\\\\\\",\\\\\\\"Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"View Titles\\\\\\\",\\\\\\\"Edit Titles\\\\\\\",\\\\\\\"Add Titles\\\\\\\",\\\\\\\"Delete Titles\\\\\\\",\\\\\\\"Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"View Rooms\\\\\\\",\\\\\\\"Edit Rooms\\\\\\\",\\\\\\\"Add Rooms\\\\\\\",\\\\\\\"Delete Rooms\\\\\\\",\\\\\\\"Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"View Sections\\\\\\\",\\\\\\\"Edit Sections\\\\\\\",\\\\\\\"Add Sections\\\\\\\",\\\\\\\"Delete Sections\\\\\\\",\\\\\\\"Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"View Item Units\\\\\\\",\\\\\\\"Edit Item Units\\\\\\\",\\\\\\\"Add Item Units\\\\\\\",\\\\\\\"Delete Item Units\\\\\\\",\\\\\\\"Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"View Groups\\\\\\\",\\\\\\\"Edit Groups\\\\\\\",\\\\\\\"Add Groups\\\\\\\",\\\\\\\"Delete Groups\\\\\\\",\\\\\\\"Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"View Patient Categories\\\\\\\",\\\\\\\"Edit Patient Categories\\\\\\\",\\\\\\\"Add Patient Categories\\\\\\\",\\\\\\\"Delete Patient Categories\\\\\\\",\\\\\\\"Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"View Suppliers\\\\\\\",\\\\\\\"Edit Suppliers\\\\\\\",\\\\\\\"Add Suppliers\\\\\\\",\\\\\\\"Delete Suppliers\\\\\\\",\\\\\\\"Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"View Stores\\\\\\\",\\\\\\\"Edit Stores\\\\\\\",\\\\\\\"Add Stores\\\\\\\",\\\\\\\"Delete Stores\\\\\\\",\\\\\\\"Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"View Insurance Companies\\\\\\\",\\\\\\\"Edit Insurance Companies\\\\\\\",\\\\\\\"Add Insurance Companies\\\\\\\",\\\\\\\"Delete Insurance Companies\\\\\\\",\\\\\\\"Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"View Sub Groups\\\\\\\",\\\\\\\"Edit Sub Groups\\\\\\\",\\\\\\\"Add Sub Groups\\\\\\\",\\\\\\\"Delete Sub Groups\\\\\\\",\\\\\\\"Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"View Admin Users\\\\\\\",\\\\\\\"Edit Admin Users\\\\\\\",\\\\\\\"Add Admin Users\\\\\\\",\\\\\\\"Delete Admin Users\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"View System Settings\\\\\\\",\\\\\\\"Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"View Business\\\\\\\",\\\\\\\"Edit Business\\\\\\\",\\\\\\\"Add Business\\\\\\\",\\\\\\\"Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"View Branches\\\\\\\",\\\\\\\"Edit Branches\\\\\\\",\\\\\\\"Add Branches\\\\\\\",\\\\\\\"Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"View Clients\\\\\\\",\\\\\\\"Edit Clients\\\\\\\",\\\\\\\"Add Clients\\\\\\\",\\\\\\\"Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Reports\\\\\\\",\\\\\\\"Export Reports\\\\\\\",\\\\\\\"Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"\\\\\\\"[1]\\\\\\\"\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20 20:05:52\\\",\\\"updated_at\\\":\\\"2025-08-22 17:32:30\\\",\\\"deleted_at\\\":null}\"', '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-22 17:32:30', '2025-08-22 17:32:30', NULL),
(19, '55e53fda-0b1c-4942-8a5d-dd17b132dbb8', 1, 1, 1, 'App\\Models\\User', 1, 'updated', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"[]\\\",\\\"permissions\\\":[\\\"Dashboard\\\",\\\"Dashboard\\\",\\\"View Dashboard\\\",\\\"View Dashboard Cards\\\",\\\"View Dashboard Charts\\\",\\\"Entities\\\",\\\"Entities\\\",\\\"View Entities\\\",\\\"Edit Entities\\\",\\\"Add Entities\\\",\\\"Delete Entities\\\",\\\"Items\\\",\\\"Items\\\",\\\"View Items\\\",\\\"Edit Items\\\",\\\"Add Items\\\",\\\"Delete Items\\\",\\\"Bulk Upload Items\\\",\\\"Staff\\\",\\\"Staff\\\",\\\"View Staff\\\",\\\"Edit Staff\\\",\\\"Add Staff\\\",\\\"Delete Staff\\\",\\\"Assign Roles\\\",\\\"Reports\\\",\\\"Reports\\\",\\\"View Report\\\",\\\"Edit Report\\\",\\\"Add Report\\\",\\\"Delete Report\\\",\\\"Logs\\\",\\\"Logs\\\",\\\"View Logs\\\",\\\"Sales\\\",\\\"Sales\\\",\\\"View Sales\\\",\\\"Edit Sales\\\",\\\"Add Sales\\\",\\\"Delete Sales\\\",\\\"Clients\\\",\\\"Clients\\\",\\\"View Clients\\\",\\\"Edit Clients\\\",\\\"Add Clients\\\",\\\"Delete Clients\\\",\\\"Queues\\\",\\\"Customers\\\",\\\"View Queues\\\",\\\"Withdrawals\\\",\\\"Withdrawals\\\",\\\"View Withdrawals\\\",\\\"Edit Withdrawals\\\",\\\"Add Withdrawals\\\",\\\"Delete Withdrawals\\\",\\\"Modules\\\",\\\"Modules\\\",\\\"View Modules\\\",\\\"Edit Modules\\\",\\\"Add Modules\\\",\\\"Delete Modules\\\",\\\"Masters\\\",\\\"Service Points\\\",\\\"View Service Points\\\",\\\"Edit Service Points\\\",\\\"Add Service Points\\\",\\\"Delete Service Points\\\",\\\"Bulky Update Service Points\\\",\\\"Service Charges\\\",\\\"Manage Service Charges\\\",\\\"Contractor Service Charges\\\",\\\"Manage Contractor Service Charges\\\",\\\"Departments\\\",\\\"View Departments\\\",\\\"Edit Departments\\\",\\\"Add Departments\\\",\\\"Delete Departments\\\",\\\"Bulky Update Departments\\\",\\\"Qualifications\\\",\\\"View Qualifications\\\",\\\"Edit Qualifications\\\",\\\"Add Qualifications\\\",\\\"Delete Qualifications\\\",\\\"Bulky Update Qualifications\\\",\\\"Titles\\\",\\\"View Titles\\\",\\\"Edit Titles\\\",\\\"Add Titles\\\",\\\"Delete Titles\\\",\\\"Bulky Update Titles\\\",\\\"Rooms\\\",\\\"View Rooms\\\",\\\"Edit Rooms\\\",\\\"Add Rooms\\\",\\\"Delete Rooms\\\",\\\"Bulky Update Rooms\\\",\\\"Sections\\\",\\\"View Sections\\\",\\\"Edit Sections\\\",\\\"Add Sections\\\",\\\"Delete Sections\\\",\\\"Bulky Update Sections\\\",\\\"Item Units\\\",\\\"View Item Units\\\",\\\"Edit Item Units\\\",\\\"Add Item Units\\\",\\\"Delete Item Units\\\",\\\"Bulky Update Item Units\\\",\\\"Groups\\\",\\\"View Groups\\\",\\\"Edit Groups\\\",\\\"Add Groups\\\",\\\"Delete Groups\\\",\\\"Bulky Update Groups\\\",\\\"Patient Categories\\\",\\\"View Patient Categories\\\",\\\"Edit Patient Categories\\\",\\\"Add Patient Categories\\\",\\\"Delete Patient Categories\\\",\\\"Bulky Update Patient Categories\\\",\\\"Suppliers\\\",\\\"View Suppliers\\\",\\\"Edit Suppliers\\\",\\\"Add Suppliers\\\",\\\"Delete Suppliers\\\",\\\"Bulky Update Suppliers\\\",\\\"Stores\\\",\\\"View Stores\\\",\\\"Edit Stores\\\",\\\"Add Stores\\\",\\\"Delete Stores\\\",\\\"Bulky Update Stores\\\",\\\"Insurance Companies\\\",\\\"View Insurance Companies\\\",\\\"Edit Insurance Companies\\\",\\\"Add Insurance Companies\\\",\\\"Delete Insurance Companies\\\",\\\"Bulky Update Insurance Companies\\\",\\\"Sub Groups\\\",\\\"View Sub Groups\\\",\\\"Edit Sub Groups\\\",\\\"Add Sub Groups\\\",\\\"Delete Sub Groups\\\",\\\"Bulky Update Sub Groups\\\",\\\"Admin\\\",\\\"Admin Users\\\",\\\"View Admin Users\\\",\\\"Edit Admin Users\\\",\\\"Add Admin Users\\\",\\\"Delete Admin Users\\\",\\\"Assign Roles\\\",\\\"Audit Logs\\\",\\\"View Audit Logs\\\",\\\"System Settings\\\",\\\"View System Settings\\\",\\\"Edit System Settings\\\",\\\"Business\\\",\\\"Business\\\",\\\"View Business\\\",\\\"Edit Business\\\",\\\"Add Business\\\",\\\"Delete Business\\\",\\\"Branches\\\",\\\"View Branches\\\",\\\"Edit Branches\\\",\\\"Add Branches\\\",\\\"Delete Branches\\\",\\\"Client\\\",\\\"Clients\\\",\\\"View Clients\\\",\\\"Edit Clients\\\",\\\"Add Clients\\\",\\\"Delete Clients\\\",\\\"Staff Access\\\",\\\"Staff\\\",\\\"View Staff\\\",\\\"Edit Staff\\\",\\\"Add Staff\\\",\\\"Delete Staff\\\",\\\"Assign Roles\\\",\\\"Report Access\\\",\\\"Reports\\\",\\\"View Reports\\\",\\\"Export Reports\\\",\\\"Filter Reports\\\",\\\"Bulk Upload\\\",\\\"Bulk Upload\\\",\\\"Bulk Validations Upload\\\"],\\\"allowed_branches\\\":\\\"[1]\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20T20:05:52.000000Z\\\",\\\"updated_at\\\":\\\"2025-08-22T17:32:30.000000Z\\\",\\\"deleted_at\\\":null}\"', '\"{\\\"id\\\":1,\\\"uuid\\\":\\\"66624542-a493-42d7-bccd-eca0acff9a95\\\",\\\"name\\\":\\\"Kashtre Admin\\\",\\\"email\\\":\\\"katznicho@gmail.com\\\",\\\"email_verified_at\\\":null,\\\"password\\\":\\\"$2y$12$nyT7XPxAj49V\\\\\\/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW\\\",\\\"two_factor_secret\\\":null,\\\"two_factor_recovery_codes\\\":null,\\\"two_factor_confirmed_at\\\":null,\\\"phone\\\":\\\"256700000003\\\",\\\"nin\\\":\\\"CF123456789012\\\",\\\"remember_token\\\":null,\\\"profile_photo_path\\\":null,\\\"status\\\":\\\"active\\\",\\\"business_id\\\":1,\\\"branch_id\\\":1,\\\"service_points\\\":\\\"\\\\\\\"[]\\\\\\\"\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"View Entities\\\\\\\",\\\\\\\"Edit Entities\\\\\\\",\\\\\\\"Add Entities\\\\\\\",\\\\\\\"Delete Entities\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"View Items\\\\\\\",\\\\\\\"Edit Items\\\\\\\",\\\\\\\"Add Items\\\\\\\",\\\\\\\"Delete Items\\\\\\\",\\\\\\\"Bulk Upload Items\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Report\\\\\\\",\\\\\\\"Edit Report\\\\\\\",\\\\\\\"Add Report\\\\\\\",\\\\\\\"Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"View Logs\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"View Sales\\\\\\\",\\\\\\\"Edit Sales\\\\\\\",\\\\\\\"Add Sales\\\\\\\",\\\\\\\"Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"View Clients\\\\\\\",\\\\\\\"Edit Clients\\\\\\\",\\\\\\\"Add Clients\\\\\\\",\\\\\\\"Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"View Queues\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"View Withdrawals\\\\\\\",\\\\\\\"Edit Withdrawals\\\\\\\",\\\\\\\"Add Withdrawals\\\\\\\",\\\\\\\"Delete Withdrawals\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"View Modules\\\\\\\",\\\\\\\"Edit Modules\\\\\\\",\\\\\\\"Add Modules\\\\\\\",\\\\\\\"Delete Modules\\\\\\\",\\\\\\\"Masters\\\\\\\",\\\\\\\"Service Points\\\\\\\",\\\\\\\"View Service Points\\\\\\\",\\\\\\\"Edit Service Points\\\\\\\",\\\\\\\"Add Service Points\\\\\\\",\\\\\\\"Delete Service Points\\\\\\\",\\\\\\\"Bulky Update Service Points\\\\\\\",\\\\\\\"Service Charges\\\\\\\",\\\\\\\"Manage Service Charges\\\\\\\",\\\\\\\"Contractor Service Charges\\\\\\\",\\\\\\\"Manage Contractor Service Charges\\\\\\\",\\\\\\\"Departments\\\\\\\",\\\\\\\"View Departments\\\\\\\",\\\\\\\"Edit Departments\\\\\\\",\\\\\\\"Add Departments\\\\\\\",\\\\\\\"Delete Departments\\\\\\\",\\\\\\\"Bulky Update Departments\\\\\\\",\\\\\\\"Qualifications\\\\\\\",\\\\\\\"View Qualifications\\\\\\\",\\\\\\\"Edit Qualifications\\\\\\\",\\\\\\\"Add Qualifications\\\\\\\",\\\\\\\"Delete Qualifications\\\\\\\",\\\\\\\"Bulky Update Qualifications\\\\\\\",\\\\\\\"Titles\\\\\\\",\\\\\\\"View Titles\\\\\\\",\\\\\\\"Edit Titles\\\\\\\",\\\\\\\"Add Titles\\\\\\\",\\\\\\\"Delete Titles\\\\\\\",\\\\\\\"Bulky Update Titles\\\\\\\",\\\\\\\"Rooms\\\\\\\",\\\\\\\"View Rooms\\\\\\\",\\\\\\\"Edit Rooms\\\\\\\",\\\\\\\"Add Rooms\\\\\\\",\\\\\\\"Delete Rooms\\\\\\\",\\\\\\\"Bulky Update Rooms\\\\\\\",\\\\\\\"Sections\\\\\\\",\\\\\\\"View Sections\\\\\\\",\\\\\\\"Edit Sections\\\\\\\",\\\\\\\"Add Sections\\\\\\\",\\\\\\\"Delete Sections\\\\\\\",\\\\\\\"Bulky Update Sections\\\\\\\",\\\\\\\"Item Units\\\\\\\",\\\\\\\"View Item Units\\\\\\\",\\\\\\\"Edit Item Units\\\\\\\",\\\\\\\"Add Item Units\\\\\\\",\\\\\\\"Delete Item Units\\\\\\\",\\\\\\\"Bulky Update Item Units\\\\\\\",\\\\\\\"Groups\\\\\\\",\\\\\\\"View Groups\\\\\\\",\\\\\\\"Edit Groups\\\\\\\",\\\\\\\"Add Groups\\\\\\\",\\\\\\\"Delete Groups\\\\\\\",\\\\\\\"Bulky Update Groups\\\\\\\",\\\\\\\"Patient Categories\\\\\\\",\\\\\\\"View Patient Categories\\\\\\\",\\\\\\\"Edit Patient Categories\\\\\\\",\\\\\\\"Add Patient Categories\\\\\\\",\\\\\\\"Delete Patient Categories\\\\\\\",\\\\\\\"Bulky Update Patient Categories\\\\\\\",\\\\\\\"Suppliers\\\\\\\",\\\\\\\"View Suppliers\\\\\\\",\\\\\\\"Edit Suppliers\\\\\\\",\\\\\\\"Add Suppliers\\\\\\\",\\\\\\\"Delete Suppliers\\\\\\\",\\\\\\\"Bulky Update Suppliers\\\\\\\",\\\\\\\"Stores\\\\\\\",\\\\\\\"View Stores\\\\\\\",\\\\\\\"Edit Stores\\\\\\\",\\\\\\\"Add Stores\\\\\\\",\\\\\\\"Delete Stores\\\\\\\",\\\\\\\"Bulky Update Stores\\\\\\\",\\\\\\\"Insurance Companies\\\\\\\",\\\\\\\"View Insurance Companies\\\\\\\",\\\\\\\"Edit Insurance Companies\\\\\\\",\\\\\\\"Add Insurance Companies\\\\\\\",\\\\\\\"Delete Insurance Companies\\\\\\\",\\\\\\\"Bulky Update Insurance Companies\\\\\\\",\\\\\\\"Sub Groups\\\\\\\",\\\\\\\"View Sub Groups\\\\\\\",\\\\\\\"Edit Sub Groups\\\\\\\",\\\\\\\"Add Sub Groups\\\\\\\",\\\\\\\"Delete Sub Groups\\\\\\\",\\\\\\\"Bulky Update Sub Groups\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"View Admin Users\\\\\\\",\\\\\\\"Edit Admin Users\\\\\\\",\\\\\\\"Add Admin Users\\\\\\\",\\\\\\\"Delete Admin Users\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"View System Settings\\\\\\\",\\\\\\\"Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"View Business\\\\\\\",\\\\\\\"Edit Business\\\\\\\",\\\\\\\"Add Business\\\\\\\",\\\\\\\"Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"View Branches\\\\\\\",\\\\\\\"Edit Branches\\\\\\\",\\\\\\\"Add Branches\\\\\\\",\\\\\\\"Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"View Clients\\\\\\\",\\\\\\\"Edit Clients\\\\\\\",\\\\\\\"Add Clients\\\\\\\",\\\\\\\"Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Edit Contractor\\\\\\\",\\\\\\\"Add Contractor Profile\\\\\\\",\\\\\\\"View Contractor Profile\\\\\\\",\\\\\\\"Edit Contractor Profile\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Reports\\\\\\\",\\\\\\\"Export Reports\\\\\\\",\\\\\\\"Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Validations Upload\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"\\\\\\\"[1]\\\\\\\"\\\",\\\"qualification_id\\\":1,\\\"department_id\\\":1,\\\"section_id\\\":1,\\\"title_id\\\":1,\\\"gender\\\":\\\"male\\\",\\\"created_at\\\":\\\"2025-07-20 20:05:52\\\",\\\"updated_at\\\":\\\"2025-08-22 18:00:15\\\",\\\"deleted_at\\\":null}\"', '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-22 18:00:15', '2025-08-22 18:00:15', NULL),
(20, 'ed754bde-9f5b-44f5-b11d-4c7dc97ed49a', 1, 1, 1, 'App\\Models\\User', 10, 'created', NULL, '\"{\\\"name\\\":\\\"Nicholas Katende\\\",\\\"email\\\":\\\"katznico1000@gmail.com\\\",\\\"status\\\":\\\"active\\\",\\\"business_id\\\":\\\"3\\\",\\\"branch_id\\\":\\\"4\\\",\\\"profile_photo_path\\\":null,\\\"phone\\\":\\\"0759983853\\\",\\\"nin\\\":\\\"CMW436567776767123\\\",\\\"gender\\\":\\\"male\\\",\\\"qualification_id\\\":\\\"7\\\",\\\"department_id\\\":\\\"6\\\",\\\"section_id\\\":\\\"6\\\",\\\"title_id\\\":\\\"8\\\",\\\"service_points\\\":\\\"[\\\\\\\"Admission\\\\\\\",\\\\\\\"Security\\\\\\\\\\\\\\/Gate\\\\\\\",\\\\\\\"Discharge\\\\\\\",\\\\\\\"Finance\\\\\\\"]\\\",\\\"allowed_branches\\\":\\\"[]\\\",\\\"permissions\\\":\\\"[\\\\\\\"Dashboard\\\\\\\",\\\\\\\"Dashboard\\\\\\\",\\\\\\\"View Dashboard\\\\\\\",\\\\\\\"View Dashboard Cards\\\\\\\",\\\\\\\"View Dashboard Charts\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"Entities\\\\\\\",\\\\\\\"View Entities\\\\\\\",\\\\\\\"Edit Entities\\\\\\\",\\\\\\\"Add Entities\\\\\\\",\\\\\\\"Delete Entities\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"Items\\\\\\\",\\\\\\\"View Items\\\\\\\",\\\\\\\"Edit Items\\\\\\\",\\\\\\\"Add Items\\\\\\\",\\\\\\\"Delete Items\\\\\\\",\\\\\\\"Bulk Upload Items\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Report\\\\\\\",\\\\\\\"Edit Report\\\\\\\",\\\\\\\"Add Report\\\\\\\",\\\\\\\"Delete Report\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"Logs\\\\\\\",\\\\\\\"View Logs\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"Sales\\\\\\\",\\\\\\\"View Sales\\\\\\\",\\\\\\\"Edit Sales\\\\\\\",\\\\\\\"Add Sales\\\\\\\",\\\\\\\"Delete Sales\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"View Clients\\\\\\\",\\\\\\\"Edit Clients\\\\\\\",\\\\\\\"Add Clients\\\\\\\",\\\\\\\"Delete Clients\\\\\\\",\\\\\\\"Queues\\\\\\\",\\\\\\\"Customers\\\\\\\",\\\\\\\"View Queues\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"Withdrawals\\\\\\\",\\\\\\\"View Withdrawals\\\\\\\",\\\\\\\"Edit Withdrawals\\\\\\\",\\\\\\\"Add Withdrawals\\\\\\\",\\\\\\\"Delete Withdrawals\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"Modules\\\\\\\",\\\\\\\"View Modules\\\\\\\",\\\\\\\"Edit Modules\\\\\\\",\\\\\\\"Add Modules\\\\\\\",\\\\\\\"Delete Modules\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"Stock\\\\\\\",\\\\\\\"View Stock\\\\\\\",\\\\\\\"Edit Stock\\\\\\\",\\\\\\\"Add Stock\\\\\\\",\\\\\\\"Delete Stock\\\\\\\",\\\\\\\"Admin\\\\\\\",\\\\\\\"Admin Users\\\\\\\",\\\\\\\"View Admin Users\\\\\\\",\\\\\\\"Edit Admin Users\\\\\\\",\\\\\\\"Add Admin Users\\\\\\\",\\\\\\\"Delete Admin Users\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Bulk Admin Upload\\\\\\\",\\\\\\\"Audit Logs\\\\\\\",\\\\\\\"View Audit Logs\\\\\\\",\\\\\\\"System Settings\\\\\\\",\\\\\\\"View System Settings\\\\\\\",\\\\\\\"Edit System Settings\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"Business\\\\\\\",\\\\\\\"View Business\\\\\\\",\\\\\\\"Edit Business\\\\\\\",\\\\\\\"Add Business\\\\\\\",\\\\\\\"Delete Business\\\\\\\",\\\\\\\"Branches\\\\\\\",\\\\\\\"View Branches\\\\\\\",\\\\\\\"Edit Branches\\\\\\\",\\\\\\\"Add Branches\\\\\\\",\\\\\\\"Delete Branches\\\\\\\",\\\\\\\"Client\\\\\\\",\\\\\\\"Clients\\\\\\\",\\\\\\\"View Clients\\\\\\\",\\\\\\\"Edit Clients\\\\\\\",\\\\\\\"Add Clients\\\\\\\",\\\\\\\"Delete Clients\\\\\\\",\\\\\\\"Staff Access\\\\\\\",\\\\\\\"Staff\\\\\\\",\\\\\\\"View Staff\\\\\\\",\\\\\\\"Edit Staff\\\\\\\",\\\\\\\"Add Staff\\\\\\\",\\\\\\\"Delete Staff\\\\\\\",\\\\\\\"Assign Roles\\\\\\\",\\\\\\\"Edit Contractor\\\\\\\",\\\\\\\"Add Contractor Profile\\\\\\\",\\\\\\\"View Contractor Profile\\\\\\\",\\\\\\\"Edit Contractor Profile\\\\\\\",\\\\\\\"Report Access\\\\\\\",\\\\\\\"Reports\\\\\\\",\\\\\\\"View Reports\\\\\\\",\\\\\\\"Export Reports\\\\\\\",\\\\\\\"Filter Reports\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Upload\\\\\\\",\\\\\\\"Bulk Validations Upload\\\\\\\"]\\\",\\\"password\\\":\\\"\\\",\\\"uuid\\\":\\\"5156fd8e-ead7-4069-a0fd-c448cf00ea2f\\\",\\\"updated_at\\\":\\\"2025-08-22 18:10:33\\\",\\\"created_at\\\":\\\"2025-08-22 18:10:33\\\",\\\"id\\\":10}\"', '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-22 18:10:33', '2025-08-22 18:10:33', NULL),
(21, '64e1336a-72d4-4b60-91e5-6b25c6391795', 10, 3, 4, 'App\\Models\\Transaction', 1, 'created', NULL, '\"{\\\"business_id\\\":3,\\\"amount\\\":1339,\\\"reference\\\":\\\"INV2025080001\\\",\\\"description\\\":\\\"Payment for invoice INV2025080001 - Nicholas Katende\\\",\\\"status\\\":\\\"completed\\\",\\\"type\\\":\\\"debit\\\",\\\"origin\\\":\\\"web\\\",\\\"phone_number\\\":\\\"+256759983851\\\",\\\"provider\\\":\\\"mtn\\\",\\\"service\\\":\\\"invoice_payment\\\",\\\"date\\\":\\\"2025-08-22T18:29:54.030782Z\\\",\\\"currency\\\":\\\"UGX\\\",\\\"names\\\":\\\"Nicholas Katende\\\",\\\"email\\\":null,\\\"ip_address\\\":\\\"197.239.10.26\\\",\\\"user_agent\\\":\\\"Mozilla\\\\\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\\\\\/537.36 (KHTML, like Gecko) Chrome\\\\\\/139.0.0.0 Safari\\\\\\/537.36\\\",\\\"method\\\":\\\"insurance\\\",\\\"transaction_for\\\":\\\"main\\\",\\\"uuid\\\":\\\"fbfe648b-9415-495c-9098-64afefd9c845\\\",\\\"updated_at\\\":\\\"2025-08-22 18:29:54\\\",\\\"created_at\\\":\\\"2025-08-22 18:29:54\\\",\\\"id\\\":1}\"', '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(22, 'd9718e63-e234-420b-ba97-f220a50fa997', 10, 3, 4, 'App\\Models\\Transaction', 2, 'created', NULL, '\"{\\\"business_id\\\":3,\\\"amount\\\":1572,\\\"reference\\\":\\\"INV2025080002\\\",\\\"description\\\":\\\"Payment for invoice INV2025080002 - Nicholas Katende\\\",\\\"status\\\":\\\"completed\\\",\\\"type\\\":\\\"debit\\\",\\\"origin\\\":\\\"web\\\",\\\"phone_number\\\":\\\"+2567599838534\\\",\\\"provider\\\":\\\"mtn\\\",\\\"service\\\":\\\"invoice_payment\\\",\\\"date\\\":\\\"2025-08-22T18:42:46.611045Z\\\",\\\"currency\\\":\\\"UGX\\\",\\\"names\\\":\\\"Nicholas Katende\\\",\\\"email\\\":null,\\\"ip_address\\\":\\\"197.239.10.26\\\",\\\"user_agent\\\":\\\"Mozilla\\\\\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\\\\\/537.36 (KHTML, like Gecko) Chrome\\\\\\/139.0.0.0 Safari\\\\\\/537.36\\\",\\\"method\\\":\\\"insurance\\\",\\\"transaction_for\\\":\\\"main\\\",\\\"uuid\\\":\\\"7473b1d6-6f77-48f6-965a-18a178c855d2\\\",\\\"updated_at\\\":\\\"2025-08-22 18:42:46\\\",\\\"created_at\\\":\\\"2025-08-22 18:42:46\\\",\\\"id\\\":2}\"', '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(23, '799a578a-f934-4c0b-82c4-a18f2f3e4c9b', 10, 3, 4, 'App\\Models\\Transaction', 3, 'created', NULL, '\"{\\\"business_id\\\":3,\\\"amount\\\":1341,\\\"reference\\\":\\\"INV2025080003\\\",\\\"description\\\":\\\"Payment for invoice INV2025080003 - Nicholas Katende\\\",\\\"status\\\":\\\"completed\\\",\\\"type\\\":\\\"debit\\\",\\\"origin\\\":\\\"web\\\",\\\"phone_number\\\":\\\"+2567599838534\\\",\\\"provider\\\":\\\"mtn\\\",\\\"service\\\":\\\"invoice_payment\\\",\\\"date\\\":\\\"2025-08-22T18:52:07.770066Z\\\",\\\"currency\\\":\\\"UGX\\\",\\\"names\\\":\\\"Nicholas Katende\\\",\\\"email\\\":null,\\\"ip_address\\\":\\\"197.239.10.26\\\",\\\"user_agent\\\":\\\"Mozilla\\\\\\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\\\\\\/537.36 (KHTML, like Gecko) Chrome\\\\\\/139.0.0.0 Safari\\\\\\/537.36\\\",\\\"method\\\":\\\"insurance\\\",\\\"transaction_for\\\":\\\"main\\\",\\\"uuid\\\":\\\"63272a37-3d6b-477b-b148-b1da0ac6fba9\\\",\\\"updated_at\\\":\\\"2025-08-22 18:52:07\\\",\\\"created_at\\\":\\\"2025-08-22 18:52:07\\\",\\\"id\\\":3}\"', '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', NULL, '', '2025-07-20', '2025-08-22 18:52:07', '2025-08-22 18:52:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `uuid`, `business_id`, `name`, `email`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, '32766ee3-1f3f-486b-8752-c836b9e1edee', 1, 'Main Branch', 'main@kashtre.com', '256700000002', 'Head Office, Kampala', '2025-07-20 20:05:52', '2025-07-20 20:05:52'),
(2, 'f154b1fd-c9cc-487d-9d11-f8363a858bb4', 4, 'Head Office', 'peetwellug@gmail.com', '0759983853', 'Kawempe', '2025-07-23 18:44:42', '2025-07-23 18:44:42'),
(3, 'abd7b3c9-f5e3-44fe-ab5f-5616e39ca62e', 3, 'Nansan Branch', 'nakimulifoundation256@gmail.com', '0774472592', 'Kampala', '2025-08-09 05:00:42', '2025-08-09 05:00:42'),
(4, 'e1afe96f-1d81-43d1-9820-a8310a7f8f84', 3, 'Head Office', 'katznicho+044@gmail.com', '0759983853', 'Kawempe', '2025-08-09 05:01:03', '2025-08-09 05:01:03'),
(5, 'f0b28324-537a-41a7-ac0d-e8140d816267', 3, 'Nalya', 'katznicho+733@gmail.com', '0759983853', 'Kawempe', '2025-08-09 05:01:32', '2025-08-09 05:01:32'),
(7, '5b426a1b-0c5a-4469-87c6-861c2b19c22b', 13, 'Main Branch', 'main@cityhealthclinic.com', '+256700000001', 'Kampala, Uganda', '2025-08-22 16:16:05', '2025-08-22 16:16:05');

-- --------------------------------------------------------

--
-- Table structure for table `branch_item_prices`
--

CREATE TABLE `branch_item_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `price` varchar(255) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_item_prices`
--

INSERT INTO `branch_item_prices` (`id`, `uuid`, `business_id`, `branch_id`, `item_id`, `price`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'dd743804-b64b-4725-b725-011c7454ce6e', 3, 3, 1, '300', '2025-08-09 08:12:48', '2025-08-09 08:12:48', NULL),
(2, '75ca9e00-65ee-4466-a289-f76f893f1dee', 3, 4, 1, '500', '2025-08-09 08:12:48', '2025-08-09 08:12:48', NULL),
(3, '39d0dc65-a43a-47fd-a7aa-2eb45af0457f', 3, 5, 1, '450', '2025-08-09 08:12:48', '2025-08-09 08:12:48', NULL),
(4, '536bf93c-6330-40a0-90ec-3594375c07fe', 3, 3, 2, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(5, 'd646dfcb-a6d7-411b-a788-856b75ed945b', 3, 4, 2, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(6, '74921579-eed3-463b-9774-d478c3b9e471', 3, 5, 2, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(7, '001d1b25-da0e-450f-a799-ab78c5f220ef', 3, 3, 3, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(8, '8a84e8f1-7df8-4049-a294-63be25de7870', 3, 4, 3, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(9, 'fb669b59-7f33-4efb-b6df-bbf75a1db164', 3, 5, 3, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(10, 'd1714942-eecf-49a5-beb4-2bd36d4b5d46', 3, 3, 4, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(11, 'd7374320-e3ae-4fc9-b1db-16e4fd01e9f9', 3, 4, 4, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(12, '6e0266da-8841-4259-93e2-2183e93404a4', 3, 5, 4, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(13, 'a111f100-8290-49e5-b585-6a46007ed20b', 3, 3, 5, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(14, '78b7dd3a-60db-4295-8c3f-22c18519e144', 3, 4, 5, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(15, 'aa64bc9d-9bf9-4ff0-a60f-2a043b01577f', 3, 5, 5, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(16, '6c0dd090-6b73-48dc-8eab-ca608daa3fc5', 3, 3, 6, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(17, '3ea4ce96-3c6d-4af9-819e-1da7c5d48017', 3, 4, 6, '115', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(18, 'd289197e-20db-4b02-8374-79d2b1e921b9', 3, 5, 6, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(19, '710df2f2-cac0-44cf-8e9f-5be7a8776d28', 3, 3, 7, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(20, '332e8178-23a9-4c11-8402-faf423916cd7', 3, 4, 7, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(21, '007025f5-f28c-4802-8659-5a897702507b', 3, 5, 7, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(22, 'dd41ea11-582f-4ffa-a015-08f378fd2367', 3, 3, 8, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(23, 'afee5a02-33e3-4980-bd8c-e3296e9d04dc', 3, 4, 8, '111', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(24, '8796e290-e517-47b0-96a0-31785b04642d', 3, 5, 8, '130', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(25, '41554436-9b2d-40c1-b2ad-0e2cd6ac1436', 3, 3, 9, '100', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(26, 'c80f9810-268b-4d5d-9acb-e3f061099e71', 3, 4, 9, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(27, 'd341cf99-b1c4-400f-899e-e1c1deb13ade', 3, 5, 9, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(28, 'd6f9ebbe-834c-4cf6-8cc3-f0a24e78e2a5', 3, 3, 10, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(29, 'd40380bc-5875-4fa4-b8f0-9d0b69c05a44', 3, 4, 10, '111', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(30, '518922bc-7141-48b2-bbf4-dfa783c41151', 3, 5, 10, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(31, '2e4bf9dc-5fc5-42e2-a7f0-2f0a222da8fa', 3, 3, 11, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(32, 'fe3215e4-da23-40aa-87f9-8f0dd9bccbcc', 3, 4, 11, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(33, 'fd6179fb-22ad-473c-94cf-479f637258bf', 3, 5, 11, '100', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(34, 'dd7d5d03-3fb4-4c1c-89d0-c0d354d0e8be', 3, 3, 12, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(35, '3a2f4139-8131-47c0-8ca3-9c38a18c080b', 3, 4, 12, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(36, 'c19717ac-1a1f-4968-a12e-9846c6cf3aff', 3, 5, 12, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(37, 'f472381d-1760-4eeb-9535-971432cf4ad2', 3, 3, 13, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(38, '4e0d38a2-3972-49ff-b511-ff4100fdad36', 3, 4, 13, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(39, '35d38e5a-92ba-469b-8570-7ee15a1413fc', 3, 5, 13, '140', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(40, '4668494d-7e23-44d5-a749-41542e158872', 3, 3, 14, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(41, 'e1df4e2a-c8a2-413b-9c75-a7f2ff73a0db', 3, 4, 14, '114', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(42, '45a077ab-7555-4d44-900b-f9acb2bb9f94', 3, 5, 14, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(43, 'cf45e3dd-a417-49a8-8aeb-ae9a7f3d1753', 3, 3, 15, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(44, 'b79f5060-c344-4ced-a7b8-3600b029b917', 3, 4, 15, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(45, '58e29967-0c68-4a27-8867-bdf6f3c2498c', 3, 5, 15, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(46, '8dcdb42a-2042-490d-80e1-d4832c113f13', 3, 3, 16, '135', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(47, '8bc8f511-f631-4ee1-a37c-0df1dadce237', 3, 4, 16, '113', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL),
(48, '83aacef2-4697-426e-aecc-dfe7e6006181', 3, 5, 16, '120', '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_service_points`
--

CREATE TABLE `branch_service_points` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `service_point_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_service_points`
--

INSERT INTO `branch_service_points` (`id`, `business_id`, `branch_id`, `service_point_id`, `item_id`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 37, 1, '2025-08-09 08:12:48', '2025-08-09 08:12:48'),
(2, 3, 4, 2, 1, '2025-08-09 08:12:48', '2025-08-09 08:12:48'),
(3, 3, 5, 36, 1, '2025-08-09 08:12:48', '2025-08-09 08:12:48'),
(4, 3, 3, 37, 2, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(5, 3, 4, 2, 2, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(6, 3, 5, 36, 2, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(7, 3, 3, 37, 3, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(8, 3, 4, 2, 3, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(9, 3, 5, 36, 3, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(10, 3, 3, 37, 4, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(11, 3, 4, 2, 4, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(12, 3, 5, 36, 4, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(13, 3, 3, 37, 5, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(14, 3, 4, 2, 5, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(15, 3, 5, 36, 5, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(16, 3, 3, 37, 6, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(17, 3, 4, 2, 6, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(18, 3, 5, 36, 6, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(19, 3, 3, 37, 7, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(20, 3, 4, 2, 7, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(21, 3, 5, 36, 7, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(22, 3, 3, 37, 8, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(23, 3, 4, 2, 8, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(24, 3, 5, 36, 8, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(25, 3, 3, 37, 9, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(26, 3, 4, 2, 9, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(27, 3, 5, 36, 9, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(28, 3, 3, 37, 10, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(29, 3, 4, 2, 10, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(30, 3, 5, 36, 10, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(31, 3, 3, 37, 11, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(32, 3, 4, 2, 11, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(33, 3, 5, 36, 11, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(34, 3, 3, 37, 12, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(35, 3, 4, 2, 12, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(36, 3, 5, 36, 12, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(37, 3, 3, 37, 13, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(38, 3, 4, 2, 13, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(39, 3, 5, 36, 13, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(40, 3, 3, 37, 14, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(41, 3, 4, 2, 14, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(42, 3, 5, 36, 14, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(43, 3, 3, 37, 15, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(44, 3, 4, 2, 15, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(45, 3, 5, 36, 15, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(46, 3, 3, 37, 16, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(47, 3, 4, 2, 16, '2025-08-09 08:51:04', '2025-08-09 08:51:04'),
(48, 3, 5, 36, 16, '2025-08-09 08:51:04', '2025-08-09 08:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `bulk_items`
--

CREATE TABLE `bulk_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `bulk_item_id` bigint(20) UNSIGNED NOT NULL,
  `included_item_id` bigint(20) UNSIGNED NOT NULL,
  `fixed_quantity` int(11) NOT NULL DEFAULT 1,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bulk_items`
--

INSERT INTO `bulk_items` (`id`, `uuid`, `bulk_item_id`, `included_item_id`, `fixed_quantity`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'c389bb59-54e4-45e9-9d80-49d04912d1c9', 19, 4, 1, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(2, 'faceb41d-b09d-40df-a8e5-60e2b822800a', 19, 2, 4, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(3, 'f91a59d3-97a8-4550-8242-ede7e05b6526', 20, 5, 3, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(4, 'b65051b7-3780-40a1-965b-90f1fdb4ecd5', 20, 3, 4, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(5, 'ee406527-20fe-417f-bb93-ddcf29e2e586', 23, 8, 2, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(6, '3f29c5a0-7731-4d90-b553-11f1bb4a80ae', 23, 9, 2, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(7, '217846b0-064b-4f6f-a3b8-fbe7fc07f95b', 24, 6, 1, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(8, 'b2f7b7ee-cb0e-4c93-ad05-1ed6c8b2b6b5', 24, 10, 2, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `date` date NOT NULL DEFAULT '2025-07-20',
  `account_number` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `uuid`, `name`, `email`, `phone`, `address`, `logo`, `date`, `account_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'f89a69e9-d345-4e54-b884-7003d02bb7fd', 'Kashtre', 'katznicho@gmail.com', '256700000001', 'Kampala, Uganda', 'logos/marzpay.png', '2025-07-20', 'KS12345678', '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(3, 'ae0557ce-abd1-458f-ad43-13808f8579ff', 'Demo Hospital Medical Center', 'codeartisan256@gmail.com', '0759983853', 'Kawempe', 'logos/e97FB9mngw49JKGXuzj1gu7WkxSCs70v9zCmW46u.png', '2025-07-20', 'KS1753266570', '2025-07-23 10:29:30', '2025-07-23 10:29:30', NULL),
(4, '82b16873-4b07-41fd-abf0-b8f7395d7eb4', 'Wakiso Medical Center', 'whysemedical@gmail.com', '0772093837', 'Kawempe', 'logos/UkymxTu6JGP0RGeqPCoiMJ5RrqPxgwnt4pAT0cGo.png', '2025-07-20', 'KS1753295827', '2025-07-23 18:37:07', '2025-07-23 18:37:07', NULL),
(13, '03451eef-d272-4b0b-86c6-7c139da1597f', 'City Health Clinic', 'info@cityhealthclinic.com', '+256700000000', 'Kampala, Uganda', NULL, '2025-07-20', 'KS1755879365', '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `uuid`, `business_id`, `branch_id`, `type`, `client_id`, `visit_id`, `nin`, `tin_number`, `surname`, `first_name`, `other_names`, `name`, `age`, `sex`, `marital_status`, `occupation`, `phone_number`, `date_of_birth`, `payment_phone_number`, `village`, `county`, `services_category`, `balance`, `status`, `email`, `next_of_kin`, `preferred_payment_method`, `payment_methods`, `nok_surname`, `nok_first_name`, `nok_other_names`, `nok_sex`, `nok_marital_status`, `nok_occupation`, `nok_phone_number`, `nok_village`, `nok_county`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'a38feb3f-5849-4c19-ba54-dccc15c85d05', 3, 4, 'Out Patient', 'DEH1213C', 'DH01H', 'CF12345678901213', 'CF12345678901213', 'Nicholas', 'Katende', NULL, 'Nicholas Katende', NULL, 'male', 'single', 'Teacher', '+256759983853', '2008-02-22', '+256759983851', 'Kirokole', 'Kawempe', 'optical', '0', 'active', 'katznicho@gmail.com', NULL, NULL, '[\"insurance\",\"mobile_money\"]', 'Nicholas', 'Katende', NULL, 'male', 'single', 'Teacher', '0759983853', 'Kirokole', 'Kawempe', '2025-08-22 18:26:20', '2025-08-22 18:26:31', NULL),
(2, '0ab04223-9387-4616-bf82-735ae40834ed', 3, 4, 'Out Patient', 'DEH2122C', 'DH02H', 'CF123456789012122', 'CF123456789012122', 'Nicholas', 'Katende', NULL, 'Nicholas Katende', NULL, 'male', 'single', 'Teacher', '+256759983853', '2025-08-07', '+2567599838534', 'Kirokole', 'Kawempe', 'dental', '0', 'active', 'katznicho@gmail.com', NULL, NULL, '[\"insurance\",\"mobile_money\"]', 'Nicholas', 'Katende', NULL, 'male', 'single', 'Teacher', '0759983853', 'Kirokole', 'Kawempe', '2025-08-22 18:32:35', '2025-08-22 18:33:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contractor_profiles`
--

CREATE TABLE `contractor_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `account_balance` varchar(255) NOT NULL DEFAULT '0',
  `kashtre_account_number` varchar(255) DEFAULT NULL,
  `signing_qualifications` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contractor_profiles`
--

INSERT INTO `contractor_profiles` (`id`, `business_id`, `uuid`, `bank_name`, `account_name`, `account_number`, `account_balance`, `kashtre_account_number`, `signing_qualifications`, `deleted_at`, `created_at`, `updated_at`, `user_id`) VALUES
(1, 4, 'e8f95c8b-c2f2-4fb0-9a98-de2c09ffe3e4', 'Sample Bank', 'Sample Account', '1234567890', '0', 'KS1355656', NULL, NULL, '2025-07-29 20:57:40', '2025-07-29 20:57:40', 8),
(2, 3, '119565b6-eb96-4b2c-aa1b-d260a71abbd3', 'Stanbic Bank', 'kikomeko Huzairuh', '9201235689', '0', 'KClrzmbV0Rdq', NULL, NULL, '2025-08-09 08:26:40', '2025-08-09 08:26:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contractor_service_charges`
--

CREATE TABLE `contractor_service_charges` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contractor_service_charges`
--

INSERT INTO `contractor_service_charges` (`id`, `uuid`, `contractor_profile_id`, `amount`, `upper_bound`, `lower_bound`, `type`, `description`, `is_active`, `business_id`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'f38c7c0a-652e-4601-987b-4ddbc9c4803e', 1, 100.00, 2000.00, 1000.00, 'fixed', NULL, 1, 4, 1, '2025-08-22 18:07:26', '2025-08-22 18:07:26', NULL),
(2, '2e477949-7201-42fa-94d7-05733e22c706', 1, 200.00, 3000.00, 2001.00, 'fixed', NULL, 1, 4, 1, '2025-08-22 18:07:26', '2025-08-22 18:07:26', NULL),
(3, 'ad0ad3a4-5350-43f7-9995-85cb0660946a', 1, 300.00, 4000.00, 3001.00, 'fixed', NULL, 1, 4, 1, '2025-08-22 18:07:26', '2025-08-22 18:07:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `uuid`, `business_id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1fcabeee-7958-49f2-b46e-9fc46a8cf37a', 1, 'Administration', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(2, '05056972-34eb-4959-881a-3f0992f8c82b', 1, 'Finance', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(3, '41d2ed8f-95d5-40e3-98e3-4828cf037166', 1, 'Outpatient Department', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(4, '8717731f-d26a-4b40-b974-aea5f98e361c', 1, 'Laboratory', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(5, '93682b0e-d21d-4d2c-bcf2-a209be2d9ee5', 1, 'Pharmacy', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(6, 'fdf5ea4e-3708-4959-b336-1b13692bb2aa', 3, 'Pharmacy', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(7, '6966e768-fa26-4f5e-9214-e50a13189dea', 3, 'OPD', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(8, 'e7ff38df-4c9b-4ac9-accd-43696941c8ce', 3, 'Surgery', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(9, 'f63ccae7-b27c-4979-b2c0-87c3cd22911e', 3, 'Anesthesia', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(10, '18b2a874-929c-44a1-b5af-05a51c768ad0', 3, 'Daycare', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(11, '54c1f220-3c3f-4423-a949-0c29340dca08', 3, 'Finance', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(12, '6ddc659e-4d47-41e8-a304-314e66bcd5da', 3, 'Theatre', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(13, '3c1614df-4756-4754-bd7e-262668451e38', 3, 'Inpatient', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(14, '5982b5d1-178e-435d-b310-adb1dfd179ca', 3, 'Admin', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(15, 'c2c0d553-6d81-4146-989c-efa3839b7ad7', 3, 'Company', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(16, '5e2b6789-444b-419d-82ac-7842d08571a4', 13, 'Pharmacy', 'Pharmacy for City Health Clinic', '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(17, '13cee670-36ad-4f42-aca5-5deba74884ad', 13, 'Laboratory', 'Laboratory for City Health Clinic', '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(18, '1da07716-6464-4a57-b6bc-f5d11a6fb02e', 13, 'Outpatient Department', 'Outpatient Department for City Health Clinic', '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(19, 'cd2149e5-0a77-44e4-95ed-20f8228e0c58', 13, 'Surgery', 'Surgery for City Health Clinic', '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '22f9024f-25d9-45a0-ba95-8c4bf2e27e8d', 'Drugs', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(2, 'ecc36381-e079-4a4f-b0bd-0589d0ef89f9', 'Sundries', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(3, 'e150ef3f-4dc0-46b1-a02d-36af5327ac6c', 'Devices', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(4, '79da0232-0ae4-4fb1-a0c5-a801e98ebb7b', 'Services', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(5, '9df4d3d0-d942-46f4-a951-39eb1572aee9', 'Finance', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(6, '044306df-a6db-4960-a0b4-946e2342c0f7', 'Inpatient', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(7, '3ab7593a-a1fd-4448-b985-b74c3b2e3233', 'Medicines', 'Medicines for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(8, 'ddc29b45-8178-41c9-a71a-b4ecfc05619b', 'Medical Supplies', 'Medical Supplies for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(9, '76cca506-325a-4573-b674-803d13c426b0', 'Laboratory Tests', 'Laboratory Tests for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(10, '4317abf4-8ded-4e5d-bc6e-50292d07d7dc', 'Consultations', 'Consultations for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(11, 'd715b593-e6fa-4a37-82ac-cbf103a16ed0', 'Procedures', 'Procedures for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(12, 'f04c61b1-3e54-4c89-9a3c-f996e9d2fb47', 'Services', 'Services for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `insurance_companies`
--

CREATE TABLE `insurance_companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_phone` varchar(255) NOT NULL,
  `payment_phone` varchar(255) DEFAULT NULL,
  `visit_id` varchar(255) NOT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `subtotal` decimal(15,2) NOT NULL,
  `package_adjustment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `account_balance_adjustment` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `amount_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(15,2) NOT NULL,
  `payment_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_methods`)),
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `printed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `business_id`, `branch_id`, `created_by`, `client_name`, `client_phone`, `payment_phone`, `visit_id`, `items`, `subtotal`, `package_adjustment`, `account_balance_adjustment`, `service_charge`, `total_amount`, `amount_paid`, `balance_due`, `payment_methods`, `payment_status`, `notes`, `status`, `confirmed_at`, `printed_at`, `created_at`, `updated_at`) VALUES
(1, 'INV2025080001', 1, 3, 4, 10, 'Nicholas Katende', '+256759983853', '+256759983851', 'DH01H', '[{\"id\":\"3\",\"name\":\"Abagyl 500mg Intravenous Infusion BP(0.5%w\\/v)\",\"price\":113,\"quantity\":1,\"total_amount\":113},{\"id\":\"16\",\"name\":\"Avarin cap\",\"price\":113,\"quantity\":2,\"total_amount\":226}]', 339.00, 0.00, 0.00, 1000.00, 1339.00, 1339.00, 0.00, '[\"insurance\",\"mobile_money\"]', 'paid', '', 'confirmed', '2025-08-22 18:29:54', NULL, '2025-08-22 18:29:54', '2025-08-22 18:29:54'),
(2, 'INV2025080002', 2, 3, 4, 10, 'Nicholas Katende', '+256759983853', '+2567599838534', 'DH02H', '[{\"id\":\"3\",\"name\":\"Abagyl 500mg Intravenous Infusion BP(0.5%w\\/v)\",\"price\":113,\"quantity\":3,\"total_amount\":339},{\"id\":\"16\",\"name\":\"Avarin cap\",\"price\":113,\"quantity\":1,\"total_amount\":113},{\"id\":\"17\",\"name\":\"Endoscopy (upper)-in house\",\"price\":120,\"quantity\":1,\"total_amount\":120}]', 572.00, 0.00, 0.00, 1000.00, 1572.00, 1572.00, 0.00, '[\"insurance\",\"mobile_money\"]', 'paid', '', 'confirmed', '2025-08-22 18:42:46', NULL, '2025-08-22 18:42:46', '2025-08-22 18:42:46'),
(3, 'INV2025080003', 2, 3, 4, 10, 'Nicholas Katende', '+256759983853', '+2567599838534', 'DH02H', '[{\"id\":\"3\",\"name\":\"Abagyl 500mg Intravenous Infusion BP(0.5%w\\/v)\",\"price\":113,\"quantity\":1,\"total_amount\":113},{\"id\":\"14\",\"name\":\"Charcovin 250mg\",\"price\":114,\"quantity\":2,\"total_amount\":228}]', 341.00, 0.00, 0.00, 1000.00, 1341.00, 1341.00, 0.00, '[\"insurance\",\"mobile_money\"]', 'paid', '', 'confirmed', '2025-08-22 18:52:07', '2025-08-22 18:56:25', '2025-08-22 18:52:07', '2025-08-22 18:56:25');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` enum('service','good','package','bulk') NOT NULL,
  `description` text DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subgroup_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_point_id` bigint(20) UNSIGNED DEFAULT NULL,
  `default_price` varchar(255) NOT NULL DEFAULT '0',
  `validity_days` int(11) DEFAULT NULL,
  `hospital_share` tinyint(3) UNSIGNED NOT NULL DEFAULT 100,
  `contractor_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `other_names` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `uuid`, `name`, `code`, `type`, `description`, `group_id`, `subgroup_id`, `department_id`, `uom_id`, `service_point_id`, `default_price`, `validity_days`, `hospital_share`, `contractor_account_id`, `business_id`, `created_at`, `updated_at`, `deleted_at`, `other_names`) VALUES
(1, 'b07a25eb-4a67-4493-bf84-8f9301c0f0bf', 'Axcel 400mg', 'ITM931152', 'good', 'Axcel 400mg', 1, 1, 6, 1, NULL, '100', 30, 100, NULL, 3, '2025-08-09 08:12:48', '2025-08-09 08:12:48', NULL, 'Axcel 400mg'),
(2, '1b7a79ac-7be6-4630-a049-5200f1e009f5', 'Metrogyl 200mg', 'ITM970337', 'good', 'test description one', 1, 1, 6, 1, NULL, '110', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Metronidazole,metro,flagyl'),
(3, '13eb0b22-f9ed-4001-8796-0694fab9bd1b', 'Abagyl 500mg Intravenous Infusion BP(0.5%w/v)', 'ITM298035', 'good', 'test description two', 1, 1, 6, 4, NULL, '150', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Metronidazole,metro,flagyl'),
(4, 'ed6006df-3059-4c44-b6c6-67e27c7ec58b', 'wellquine 500 mg', 'ITM653015', 'good', 'test description three', 1, 1, 6, 1, NULL, '130', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Levofloxacin,levo'),
(5, '93f6e9e8-0cc0-41d5-b528-311fbed771f6', 'Levobact 500mg', 'ITM907898', 'good', 'test description four', 1, 1, 6, 1, NULL, '120', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Levofloxacin,levo'),
(6, 'e010059c-2360-49c3-924e-ce9f3aa62b9a', 'Spamclox capsules 500', 'ITM433197', 'good', 'test description five', 1, 1, 6, 1, NULL, '140', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Ampiclox,ampicillin,cloxacillin,cloxa'),
(7, '8f5ac5d8-bb9a-4201-8713-558ba181b25e', 'Cefoperazone 1g', 'ITM858337', 'good', 'test description six', 1, 1, 6, 3, NULL, '105', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Cefazone,Cefeoperazone'),
(8, '9347d17c-5cae-43b4-8bf5-bb90f1b8f8db', 'Levofloxacin 500mg Inj', 'ITM521301', 'good', 'test description seven', 1, 1, 6, 4, NULL, '120', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Levofloxacin,levo'),
(9, '4be4fcb5-316b-427b-b34e-3734f0d48aad', 'Renechlor 250mg', 'ITM200867', 'good', 'test description eight', 1, 1, 6, 2, NULL, '108', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'chloramphenical,CAF'),
(10, '6dc9cee9-915a-4163-b3e6-4d26a3f99c81', 'Epicephine 1g', 'ITM056824', 'good', 'test description nine', 1, 1, 6, 3, NULL, '102', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Ceftriaxone IV,cef,'),
(11, '630d5901-d0e4-473c-87ca-a81c532997dd', 'ificipro Injection (200mg/ml)', 'ITM435852', 'good', 'test description ten', 1, 1, 6, 4, NULL, '100', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Ciprofloxacin,Cipro,'),
(12, '45b249cd-5a67-4cc8-a762-db4f7c4859de', 'Ciprobid 500', 'ITM720337', 'good', 'test description eleven', 1, 1, 6, 1, NULL, '100', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Ciprofloxacin,Cipro,'),
(13, '508cf673-f352-42e0-8970-ecfc9aa3b812', 'Rifacolon (rifaximin550mg)', 'ITM698486', 'good', 'test descriptiontwelve', 1, 1, 6, 2, NULL, '105', NULL, 100, NULL, 3, '2025-08-09 08:51:03', '2025-08-09 08:51:03', NULL, 'Rifacolon,Rifaximin'),
(14, 'f29e98b9-5efe-412e-b6d2-6d7f4805ec7a', 'Charcovin 250mg', 'ITM696289', 'good', 'test description thirteen', 1, 1, 6, 1, NULL, '125', NULL, 100, NULL, 3, '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL, 'Charcoal,Activated Charcoal'),
(15, '11ad4f35-6462-4fdf-a15b-f640e9f60290', 'Colospasmin forte', 'ITM656982', 'good', 'test description fourteen', 1, 1, 6, 1, NULL, '130', NULL, 100, NULL, 3, '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL, 'Mebeverine'),
(16, '149e75e7-fd42-4a40-9f12-6457186a75e6', 'Avarin cap', 'ITM862793', 'good', 'test description fifteen', 1, 1, 6, 2, NULL, '135', NULL, 100, NULL, 3, '2025-08-09 08:51:04', '2025-08-09 08:51:04', NULL, 'Alverine,Simethicone'),
(17, '05271281-3130-432f-b090-56bd2ef19845', 'Endoscopy (upper)-in house', 'ITM020081', 'package', 'testing package', NULL, NULL, NULL, NULL, NULL, '120', 2, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'OGD, esophagogatroduodenoscopy, upper GI,endoscopy'),
(18, '9bf13464-d067-439b-9136-ac1f41d88841', 'Endoscopy (upper)-external', 'ITM600769', 'package', 'testing package', NULL, NULL, NULL, NULL, NULL, '130', 2, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'OGD, esophagogatroduodenoscopy, upper GI,endoscopy'),
(19, '19b1b66f-e052-4576-ad99-bb7f80bd080c', 'Colonoscopy-in house', 'ITM670227', 'bulk', 'testing package', NULL, NULL, NULL, NULL, NULL, '120', NULL, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'Colonoscopy-in house'),
(20, '9f087ee6-efeb-4461-b19c-219a28130f38', 'Colonoscopy-external', 'ITM038269', 'bulk', 'testing package', NULL, NULL, NULL, NULL, NULL, '100', NULL, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'Colonoscopy-external'),
(21, '8af77e98-0f62-4073-aae3-d4fc1ac228f4', 'combined endoscopy & colonoscopy (in house)', 'ITM042511', 'package', 'testing package', NULL, NULL, NULL, NULL, NULL, '120', 2, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'combined endoscopy & colonoscopy (in house)'),
(22, 'ed78d21a-d0dd-470c-942a-c32c406af424', 'combined endoscopy & colonoscopy (external)', 'ITM589111', 'package', 'testing package', NULL, NULL, NULL, NULL, NULL, '120', 2, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'combined endoscopy & colonoscopy (external)'),
(23, 'fe0743aa-02b9-4440-917e-3b40c0186c22', 'High intermediate Procedure', 'ITM200439', 'bulk', 'testing package', NULL, NULL, NULL, NULL, NULL, '130', NULL, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'High intermediate Procedure'),
(24, 'a46fd7b7-d2b8-4cc1-8c4e-b09102f1c58d', 'Low intermediate Procedure', 'ITM963562', 'bulk', 'testing package', NULL, NULL, NULL, NULL, NULL, '140', NULL, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'Low intermediate Procedure'),
(25, '698d5fef-3856-4c70-ae0d-8868457b79ed', 'Minor Procedure', 'ITM085266', 'package', 'testing package', NULL, NULL, NULL, NULL, NULL, '120', 2, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'Minor Procedure'),
(26, '5134033c-24f7-4a15-b16c-149388d4d45f', 'Prostate biopsy', 'ITM644348', 'package', 'testing package', NULL, NULL, NULL, NULL, NULL, '120', 2, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'Prostate biopsy'),
(27, '51cea6a0-8a65-4545-8dcd-cff5110eb372', 'Biopsy-trucut other masses', 'ITM268677', 'bulk', 'testing package', NULL, NULL, NULL, NULL, NULL, '130', NULL, 100, NULL, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL, 'Biopsy-trucut other masses');

-- --------------------------------------------------------

--
-- Table structure for table `item_units`
--

CREATE TABLE `item_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_units`
--

INSERT INTO `item_units` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '5a18ff31-b437-4383-800e-6928fddca064', 'Tab', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(2, '43b04a00-415e-47ad-bd29-af0eedc87f65', 'Cap', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(3, '8a88b9e1-9a0a-40fe-96f7-a6f33010c818', 'Vial', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(4, 'f432d7c1-1823-44e2-952e-726791bd604b', 'Bottle', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(5, '2c090162-9b2d-4651-851b-df45464f1435', 'Suppository', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(6, 'fd05e135-77be-40e7-a847-1996d5023f8a', 'Kit', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(7, 'affbc38c-4c74-4a23-a0a8-b13832c7f150', 'Mg', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(8, '3f950534-4e2f-4cd4-964f-20e8d3f8f86d', 'mcg', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(9, '5214ac7d-d44d-4041-993f-4f42bd8c5f41', 'mls', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(10, '8c973323-f3cb-4b48-8d96-031c8eeeded7', 'Spray', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(11, '320c337d-d566-4adf-b437-ab5ac6c8f88a', 'Litre', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(12, '26702e70-dd07-4df3-93d2-24b584fd7201', 'Ampoule', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(13, 'a0c0a853-d0c7-4515-be1d-e96fdd00bc1a', 'Sachet', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(14, 'd4d19527-6af7-46e9-8c19-0c8254a4ae24', 'Pc', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(15, 'b1f797fa-a5be-4c25-b2e1-ecb35012d41f', 'Pair', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(16, '06da181f-7304-409c-9816-ca88ea607e46', 'PU', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(17, '3b342651-ba4d-4281-b079-5709306eb12d', 'Procedure', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(18, '4426d1d5-4f1b-4590-bdcc-c79180c7e6a1', 'Unit', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(19, '7cd64c6f-0fb9-4700-ba5f-775617eeb474', 'Hour', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(20, '910f2f48-9761-4daa-8570-348cb25bd8e9', 'Session', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(21, 'fbd4e899-21ac-482c-b86a-240ff6b830e6', 'Tablets', 'Tablets unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(22, '0ff6a62e-4f19-408e-a9e5-f173a1ffb7c7', 'Capsules', 'Capsules unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(23, '93468090-29aa-4edd-ab9b-67a02828b07c', 'Bottles', 'Bottles unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(24, '9bb135ca-1eb4-4f62-ac01-3f8f513c3860', 'Pieces', 'Pieces unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(25, '59f0724b-2d28-440d-85fd-df85f3cdcf61', 'Tests', 'Tests unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(26, '7a38fd18-1070-4931-a7ba-607641eebb1e', 'Consultations', 'Consultations unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL),
(27, 'cfc45bf7-efd8-43fe-9be8-41a5deb9c076', 'Procedures', 'Procedures unit for City Health Clinic', 13, '2025-08-22 16:16:05', '2025-08-22 16:16:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2013_06_23_132952_create_businesses_table', 1),
(2, '2013_07_13_141700_create_branches_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2022_03_23_163443_create_sessions_table', 1),
(6, '2025_07_14_043706_create_password_reset_tokens_table', 1),
(7, '2025_07_15_101754_create_titles_table', 1),
(8, '2025_07_15_101810_create_departments_table', 1),
(9, '2025_07_15_101858_create_roles_table', 1),
(10, '2025_07_15_101914_create_service_points_table', 1),
(11, '2025_07_15_102926_create_qualifications_table', 1),
(12, '2025_07_15_103013_create_rooms_table', 1),
(13, '2025_07_18_075630_create_groups_table', 1),
(14, '2025_07_18_075824_create_stores_table', 1),
(15, '2025_07_18_075903_create_insurance_companies_table', 1),
(16, '2025_07_18_075918_create_suppliers_table', 1),
(17, '2025_07_18_075932_create_sections_table', 1),
(18, '2025_07_18_075957_create_patient_categories_table', 1),
(19, '2025_07_18_080010_create_item_units_table', 1),
(20, '2025_07_18_080406_create_items_table', 1),
(21, '2025_07_18_080407_create_branch_item_prices_table', 1),
(22, '2025_07_18_081132_create_contractor_profiles_table', 1),
(23, '2025_07_18_084942_create_clients_table', 1),
(24, '2025_10_12_000000_create_users_table', 1),
(25, '2025_10_12_200000_add_two_factor_columns_to_users_table', 1),
(26, '2025_10_23_143816_create_transactions_table', 1),
(27, '2025_11_23_203441_create_activity_logs_table', 1),
(28, '2025_07_22_000000_add_other_items_to_items_table', 2),
(29, '2025_07_22_220039_create_sub_groups_table', 3),
(30, '2025_07_25_203920_remove_unique_constraints_from_name_columns', 4),
(31, '2025_07_29_073129_add_user_id_to_contractor_profiles_table', 5),
(32, '2025_08_01_100939_change_other_names_to_string_in_items_table', 6),
(33, '2025_08_05_112120_create_package_items_table', 7),
(34, '2025_08_05_112128_create_bulk_items_table', 7),
(35, '2025_08_05_113513_add_validity_days_to_items_table', 7),
(36, '2025_08_08_114443_add_branch_id_to_items_table', 8),
(37, '2025_08_08_120034_remove_branch_id_from_items_table', 8),
(38, '2025_08_08_120511_create_branch_service_points_table', 8),
(39, '2013_06_23_132952_create_businesses_table', 1),
(40, '2025_10_12_000000_create_users_table', 1),
(41, '2025_10_12_200000_add_two_factor_columns_to_users_table', 1),
(42, '2025_10_23_143816_create_transactions_table', 1),
(43, '2025_11_23_203441_create_activity_logs_table', 1),
(44, '2013_06_23_132952_create_businesses_table', 1),
(45, '2025_10_12_000000_create_users_table', 1),
(46, '2025_10_12_200000_add_two_factor_columns_to_users_table', 1),
(47, '2025_10_23_143816_create_transactions_table', 1),
(48, '2025_11_23_203441_create_activity_logs_table', 1),
(49, '2025_08_12_051620_add_missing_fields_to_clients_table', 9),
(50, '2013_06_23_132952_create_businesses_table', 1),
(51, '2025_10_12_000000_create_users_table', 1),
(52, '2025_10_12_200000_add_two_factor_columns_to_users_table', 1),
(53, '2025_10_23_143816_create_transactions_table', 1),
(54, '2025_11_23_203441_create_activity_logs_table', 1),
(55, '2025_08_22_150000_create_money_accounts_table', 10),
(56, '2025_08_19_141007_create_invoices_table', 11),
(57, '2025_08_22_150200_create_package_tracking_table', 12),
(58, '2025_08_22_151500_modify_money_transfers_allow_null_from_account', 13),
(59, '2025_08_22_152000_modify_money_transfers_allow_null_to_account', 14);

-- --------------------------------------------------------

--
-- Table structure for table `money_accounts`
--

CREATE TABLE `money_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('client_account','package_suspense_account','general_suspense_account','kashtre_suspense_account','business_account','contractor_account','kashtre_account') NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `contractor_profile_id` bigint(20) UNSIGNED DEFAULT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(255) NOT NULL DEFAULT 'UGX',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `money_accounts`
--

INSERT INTO `money_accounts` (`id`, `uuid`, `name`, `type`, `business_id`, `client_id`, `contractor_profile_id`, `balance`, `currency`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '07cd123b-3fae-4958-9d4d-a12479f5383f', 'Package Suspense Account', 'package_suspense_account', 1, NULL, NULL, 0.00, 'UGX', 'Holds funds for paid package items if nothing has been used', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(2, '67fc2acc-2fa9-49e1-b9de-bc7b359c6ba4', 'General Suspense Account', 'general_suspense_account', 1, NULL, NULL, 0.00, 'UGX', 'Holds funds for Ordered items not yet offered for all clients', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(3, '7400fa38-1a8a-484d-9a1d-8815e7b976a1', 'Kashtre Suspense Account', 'kashtre_suspense_account', 1, NULL, NULL, 0.00, 'UGX', 'Holds all service fees charged on the invoice', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(4, '2775cb52-0a52-49ae-8f55-9e3659171280', 'Business Account', 'business_account', 1, NULL, NULL, 0.00, 'UGX', 'Holds business funds for sales that have been made', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(5, 'eb6a47ae-d997-4456-986a-a1dbef18e491', 'Kashtre Account', 'kashtre_account', 1, NULL, NULL, 0.00, 'UGX', 'Holds KASHTRE funds', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(6, 'b857b0d2-db68-437c-9189-3ae4b7508166', 'Package Suspense Account', 'package_suspense_account', 3, NULL, NULL, 0.00, 'UGX', 'Holds funds for paid package items if nothing has been used', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(7, 'c2eba805-f25b-4378-9699-729046718edb', 'General Suspense Account', 'general_suspense_account', 3, NULL, NULL, 1252.00, 'UGX', 'Holds funds for Ordered items not yet offered for all clients', 1, '2025-08-22 16:10:44', '2025-08-22 18:52:07', NULL),
(8, '557c5e29-bdcf-4e2f-99e3-722e6438a11f', 'Kashtre Suspense Account', 'kashtre_suspense_account', 3, NULL, NULL, 3000.00, 'UGX', 'Holds all service fees charged on the invoice', 1, '2025-08-22 16:10:44', '2025-08-22 18:52:07', NULL),
(9, '0b79a5be-2f1a-4126-9df0-52d3a67d6e81', 'Business Account', 'business_account', 3, NULL, NULL, 0.00, 'UGX', 'Holds business funds for sales that have been made', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(10, '7e6705e4-534d-463f-8350-1e49ca5d3398', 'Kashtre Account', 'kashtre_account', 3, NULL, NULL, 0.00, 'UGX', 'Holds KASHTRE funds', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(11, '4facbf87-065d-4052-ae62-8807a6608c62', 'Package Suspense Account', 'package_suspense_account', 4, NULL, NULL, 0.00, 'UGX', 'Holds funds for paid package items if nothing has been used', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(12, '92698fe3-e113-41c7-aaff-8acc1a98b792', 'General Suspense Account', 'general_suspense_account', 4, NULL, NULL, 0.00, 'UGX', 'Holds funds for Ordered items not yet offered for all clients', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(13, '7585e31a-bd38-4190-adf4-7a7f4b157d64', 'Kashtre Suspense Account', 'kashtre_suspense_account', 4, NULL, NULL, 0.00, 'UGX', 'Holds all service fees charged on the invoice', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(14, 'ce78e530-ccc7-4321-8c23-dc92b5e5344f', 'Business Account', 'business_account', 4, NULL, NULL, 0.00, 'UGX', 'Holds business funds for sales that have been made', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(15, '4ee8aba9-5c3f-4258-bc46-7fb0d8f32fae', 'Kashtre Account', 'kashtre_account', 4, NULL, NULL, 0.00, 'UGX', 'Holds KASHTRE funds', 1, '2025-08-22 16:10:44', '2025-08-22 16:10:44', NULL),
(16, '4a8d085e-b27c-4629-bae4-5fe444965fd6', 'Client Account - Nicholas Katende', 'client_account', 3, 1, NULL, 0.00, 'UGX', 'Holds all money paid by client Nicholas Katende', 1, '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(17, 'd169b39e-b4fa-46e9-8bfa-3e73cecf0b6e', 'Client Account - Nicholas Katende', 'client_account', 3, 2, NULL, 0.00, 'UGX', 'Holds all money paid by client Nicholas Katende', 1, '2025-08-22 18:42:46', '2025-08-22 18:52:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `money_transfers`
--

CREATE TABLE `money_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `from_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'UGX',
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `transfer_type` enum('payment_received','order_confirmed','service_delivered','refund_approved','package_usage','service_charge','manual_transfer') NOT NULL,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `package_usage_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `money_transfers`
--

INSERT INTO `money_transfers` (`id`, `uuid`, `business_id`, `from_account_id`, `to_account_id`, `amount`, `currency`, `status`, `transfer_type`, `invoice_id`, `client_id`, `item_id`, `package_usage_id`, `reference`, `description`, `metadata`, `processed_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '7658d57f-4dca-47fc-a3f8-3e3ee8ec8c98', 3, NULL, 16, 1339.00, 'UGX', 'completed', 'payment_received', NULL, 1, NULL, NULL, 'INV2025080001', 'Payment received via insurance', '{\"invoice_id\":1,\"payment_methods\":[\"insurance\",\"mobile_money\"],\"payment_phone\":\"+256759983851\"}', '2025-08-22 18:29:54', '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(2, '6608eb6d-352e-456c-bc03-52ac70438be9', 3, 16, 7, 113.00, 'UGX', 'completed', 'order_confirmed', 1, 1, 3, NULL, NULL, 'Order confirmed: Abagyl 500mg Intravenous Infusion BP(0.5%w/v)', NULL, '2025-08-22 18:29:54', '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(3, '3ba2c6f5-9176-4aad-816d-c4f598ffc304', 3, 16, 7, 226.00, 'UGX', 'completed', 'order_confirmed', 1, 1, 16, NULL, NULL, 'Order confirmed: Avarin cap', NULL, '2025-08-22 18:29:54', '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(4, '413b847e-bb1b-4423-9f5b-fe3746e3c709', 3, 16, 8, 1000.00, 'UGX', 'completed', 'service_charge', 1, 1, NULL, NULL, NULL, 'Service charge for invoice INV2025080001', NULL, '2025-08-22 18:29:54', '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(5, '47151757-0b27-491e-bd81-5d3d2b44afec', 3, NULL, 17, 1572.00, 'UGX', 'completed', 'payment_received', NULL, 2, NULL, NULL, 'INV2025080002', 'Payment received via insurance', '{\"invoice_id\":2,\"payment_methods\":[\"insurance\",\"mobile_money\"],\"payment_phone\":\"+2567599838534\"}', '2025-08-22 18:42:46', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(6, '9c0c9823-8f54-474d-9243-6bd3afad2ac5', 3, 17, 7, 339.00, 'UGX', 'completed', 'order_confirmed', 2, 2, 3, NULL, NULL, 'Order confirmed: Abagyl 500mg Intravenous Infusion BP(0.5%w/v)', NULL, '2025-08-22 18:42:46', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(7, 'fed8c6d2-06cb-4cb4-b789-31d44a00044b', 3, 17, 7, 113.00, 'UGX', 'completed', 'order_confirmed', 2, 2, 16, NULL, NULL, 'Order confirmed: Avarin cap', NULL, '2025-08-22 18:42:46', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(8, '1a7b4549-5d3b-427d-80b5-bf8343b2dee6', 3, 17, 7, 120.00, 'UGX', 'completed', 'order_confirmed', 2, 2, 17, NULL, NULL, 'Order confirmed: Endoscopy (upper)-in house', NULL, '2025-08-22 18:42:46', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(9, 'f4d61025-ce44-4df5-b7bc-5d63f6d41af3', 3, 17, 8, 1000.00, 'UGX', 'completed', 'service_charge', 2, 2, NULL, NULL, NULL, 'Service charge for invoice INV2025080002', NULL, '2025-08-22 18:42:46', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(10, '57fca2a3-c996-4735-bbd6-ad1a5b302f82', 3, NULL, 17, 1341.00, 'UGX', 'completed', 'payment_received', NULL, 2, NULL, NULL, 'INV2025080003', 'Payment received via insurance', '{\"invoice_id\":3,\"payment_methods\":[\"insurance\",\"mobile_money\"],\"payment_phone\":\"+2567599838534\"}', '2025-08-22 18:52:07', '2025-08-22 18:52:07', '2025-08-22 18:52:07', NULL),
(11, 'd1d5138f-c61f-42b6-9265-89924a5b1c93', 3, 17, 7, 113.00, 'UGX', 'completed', 'order_confirmed', 3, 2, 3, NULL, NULL, 'Order confirmed: Abagyl 500mg Intravenous Infusion BP(0.5%w/v)', NULL, '2025-08-22 18:52:07', '2025-08-22 18:52:07', '2025-08-22 18:52:07', NULL),
(12, 'c6d667d8-1f17-427c-b6c1-de1412d13dd6', 3, 17, 7, 228.00, 'UGX', 'completed', 'order_confirmed', 3, 2, 14, NULL, NULL, 'Order confirmed: Charcovin 250mg', NULL, '2025-08-22 18:52:07', '2025-08-22 18:52:07', '2025-08-22 18:52:07', NULL),
(13, 'e7a0fcce-575c-414b-92b0-7414f01c5df0', 3, 17, 8, 1000.00, 'UGX', 'completed', 'service_charge', 3, 2, NULL, NULL, NULL, 'Service charge for invoice INV2025080003', NULL, '2025-08-22 18:52:07', '2025-08-22 18:52:07', '2025-08-22 18:52:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `package_items`
--

CREATE TABLE `package_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `package_item_id` bigint(20) UNSIGNED NOT NULL,
  `included_item_id` bigint(20) UNSIGNED NOT NULL,
  `max_quantity` int(11) NOT NULL DEFAULT 1,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `package_items`
--

INSERT INTO `package_items` (`id`, `uuid`, `package_item_id`, `included_item_id`, `max_quantity`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'a7b34c5d-f94a-4a63-b824-5f50a5fa544d', 17, 3, 2, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(2, 'e776c1ef-1759-4ce8-8d54-ea5ded0bdc72', 17, 2, 3, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(3, 'c042c9f2-3fb2-4e30-91d2-a984bd4698eb', 18, 2, 1, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(4, 'fc29381b-e96b-432d-8d56-311d540ddc5f', 18, 5, 2, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(5, '95c13d76-bac8-4e0d-8fef-cfe734a4dbe6', 21, 4, 4, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(6, 'bcf47389-f3a1-4fe1-ac29-6f4efff93f72', 21, 6, 5, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(7, 'b486b8b4-38a3-42da-a3cf-76f07088de3b', 22, 13, 4, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(8, 'd89c4b0c-0c09-43ab-bf45-148e848f2238', 22, 6, 3, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(9, 'adb9145d-5ddc-4531-989b-ea2a742695e9', 25, 8, 5, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(10, '5398eeb5-90d5-4e3c-b09a-7f2c27a44004', 25, 3, 3, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(11, '1a4fb12b-86aa-4a3e-ae51-4ddc4de32bc6', 26, 7, 5, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL),
(12, '1712aeed-0655-4002-9c14-89f87d23bdc4', 26, 15, 4, 3, '2025-08-09 09:18:13', '2025-08-09 09:18:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `package_tracking`
--

CREATE TABLE `package_tracking` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `package_item_id` bigint(20) UNSIGNED NOT NULL,
  `included_item_id` bigint(20) UNSIGNED NOT NULL,
  `total_quantity` int(11) NOT NULL,
  `used_quantity` int(11) NOT NULL DEFAULT 0,
  `remaining_quantity` int(11) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` enum('active','expired','fully_used','cancelled') NOT NULL DEFAULT 'active',
  `package_price` decimal(15,2) NOT NULL,
  `item_price` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('codeartisan256@gmail.com', '$2y$12$oXLs/iwKLqW56sP.JacIvOJMR78ahA2ZuNz4nL93siUI6ojLgtnIi', '2025-07-23 10:23:22'),
('katznicho+124@gmail.com', '$2y$12$eDOid9Cu/N9uxHh.rpawh.uFCKN5QDpI4haa7q8cLvp.GLRJtLUfi', '2025-07-29 20:35:27'),
('tracepeso@gmail.com', '$2y$12$awWezYUWFCWGHeeBw2wUnOfWxTms66f.W/ZAxympStQqSKuHuPHIO', '2025-07-29 20:35:28'),
('katznicho+9834@gmail.com', '$2y$12$ernsAjXhnthw5dOX.pmnmuF634FA1.UkXbgb8dLoldwpxLSSYIFnu', '2025-07-29 20:35:28'),
('katznicho+734563@gmail.com', '$2y$12$isZB82JSErTopZ6xMlsa0.gZDbZgfNJvhKU7cBC2hEsV1EiuDVXM6', '2025-07-29 20:57:40'),
('tracepeso+123@gmail.com', '$2y$12$NZnhFKbU9Vr1DWYjPcBMx.j44ahGqkwK9J/Z1UIDT4FuEN2ZyCHU.', '2025-07-29 20:57:41'),
('kikomekohudhairuh@gmail.com', '$2y$12$xF3QlJ9GxJX/tV4samMWnunmMZGGuF51QvCxqdTNmTsI9cJoWNFbG', '2025-08-09 08:26:39'),
('katznico1000@gmail.com', '$2y$12$CagZqSoBe7zTKH.RsZ3cH.EWdYYozXnwbjlEIID8qx6LG4Yn2Ocqe', '2025-08-22 18:13:26');

-- --------------------------------------------------------

--
-- Table structure for table `patient_categories`
--

CREATE TABLE `patient_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_categories`
--

INSERT INTO `patient_categories` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '56e1e57d-11ff-4115-b8aa-e72e18f60662', 'Out Patient General', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qualifications`
--

CREATE TABLE `qualifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qualifications`
--

INSERT INTO `qualifications` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '18cefcd9-bd17-418a-b2a9-48159251a546', 'MBChB', NULL, 1, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(2, '5de30b33-2e3e-44e3-bc19-70f83b33bc85', 'PhD', NULL, 1, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(3, '0185260e-d30c-4a53-9f70-d8878f4aa758', 'Diploma in Nursing', NULL, 1, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(4, '0b3f91af-89ce-41c7-961f-a1d650b7c86b', 'Bachelor of Dental Surgery', NULL, 1, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(5, 'ce3e8816-ec3d-467b-8015-bcf7a993e9db', 'Master of Public Health', NULL, 1, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(7, 'b067c55b-2b0b-480c-a267-63a982e86969', 'MBChB', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(8, 'c84c195b-8a8a-469a-9a5a-df5a82b4ba60', 'MMED', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(9, 'f0961ce4-d921-445c-8bf9-d9d3622d1f52', 'DCM', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(10, 'a92732a9-ce12-4f3d-b358-aaf455fe844c', 'DAN', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(11, '9346e0e5-60e4-424a-ad66-a8b7e9c4471b', 'Dip CM', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(12, '634db550-39d1-441b-b4a8-cdaaee6ecb88', 'Company', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '0b265817-949b-4f36-80f7-9a8dbea73056', 'Reception Desk', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(2, '965d3484-f7c7-4873-a629-1b6f5a8a26d7', 'Consultion Room 1', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(3, '10650049-a17c-45e0-80a3-11371d06fca9', 'Prep Room', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(4, '66bcbfaf-a202-46e6-99bb-bc3007bb90f4', 'Theatre', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'b2a5c3df-727c-413a-9fab-2f85baf6a204', 'General Medicine', NULL, 1, NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(2, 'b4f83c21-5955-4477-8176-4e1af9dfeddb', 'Pediatrics', NULL, 1, NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(3, '07cc2299-a1f0-46b5-8753-7cae25061bb4', 'Surgery', NULL, 1, NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(4, 'd3b0e321-b490-45b9-ac67-19a6fe546d3c', 'Radiology', NULL, 1, NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(5, '5643e0db-0f80-4c48-981f-1e6624081e1d', 'Obstetrics & Gynecology', NULL, 1, NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(6, 'dce349c8-2871-4e0c-88db-295600d9aa05', 'General OPD', NULL, 3, NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_charges`
--

CREATE TABLE `service_charges` (
  `id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_charges`
--

INSERT INTO `service_charges` (`id`, `entity_type`, `entity_id`, `amount`, `upper_bound`, `lower_bound`, `type`, `description`, `is_active`, `business_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'business', 3, 100.00, 2000.00, 1000.00, 'fixed', NULL, 1, 3, 1, '2025-08-22 17:51:40', '2025-08-22 17:51:40'),
(2, 'business', 3, 200.00, 3000.00, 2001.00, 'fixed', NULL, 1, 3, 1, '2025-08-22 17:51:40', '2025-08-22 17:51:40'),
(3, 'business', 3, 3000.00, 4000.00, 3001.00, 'fixed', NULL, 1, 3, 1, '2025-08-22 17:51:40', '2025-08-22 17:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `service_points`
--

CREATE TABLE `service_points` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_points`
--

INSERT INTO `service_points` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fa9c4ce7-e641-4ba6-9196-7e749bd28442', 'Pharmacy', 'pharmacy', 4, 2, '2025-07-23 18:54:29', '2025-07-23 18:54:29', NULL),
(2, '14c5f945-76d5-4962-b036-15787cd13431', 'Pharmacy', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(3, 'f2d1c120-31e6-40a7-99ff-bd1754d83423', 'Consultation MO', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(4, 'c5d82128-a081-46bc-82da-a599574e47ee', 'Reception', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(5, '7f943034-2f64-471f-8166-13879f3c8173', 'Surgical Procedure', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(6, 'dfc011b9-0bc6-47c6-8109-ba083e5cffc3', 'Endoscopy Procedure', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(7, '3ef2924d-9539-4756-b81e-3d823919a3ae', 'Consultation Surgeon', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(8, 'dd1994b5-31b0-4c4e-95e2-f1e84d0be8c7', 'Admission', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(9, '5eaa4fd2-e3ff-44eb-9062-f778d003af52', 'Security/Gate', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(10, 'bf07f586-f415-442b-a414-fc7448518ae8', 'Discharge', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(11, '9224eced-5470-430c-9c20-6f04da90b039', 'Finance', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(12, '17315c87-c4b5-4359-9331-c7e9f58e71d2', 'Nursing', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(13, 'd50d738b-8ecf-4c49-a9cc-f5b074291702', 'Consultation Dr ERB', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(14, 'fb4d03ba-e244-4588-85b6-1d4344a56b92', 'Consultation Dr KP', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(15, '0978d890-e75e-43b7-80b6-1233a143d99e', 'Consultation Dr AV', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(16, '2e9bac3f-54bb-4988-bc8e-1a36829037d7', 'Consultation Dr KG', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(17, '7a211fe6-894b-47ce-b976-a7e179f3038c', 'Consultation Dr NV', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(18, '9f80a90e-da65-408e-b40e-eb78d67ffa9f', 'Procedure Surgeon', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(19, '9938ba3b-e8f4-4480-8864-28c8ae74b39a', 'Procedure Dr ERB', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(20, '21854db4-6a66-4204-a492-dde90641461a', 'Procedure Dr KP', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(21, 'd266f928-d8ca-4215-b5b7-7fd32b569457', 'Procedure Dr AV', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(22, 'c41fb60a-2728-49d9-8d9f-4d8b9819f9b1', 'Procedure Dr KG', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(23, 'e90820db-e129-49ea-921e-a0e815ab7e10', 'Procedure Dr NV', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(24, 'f7311962-c283-4d07-91be-a46c4b78070e', 'Anesthetist', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(25, 'cb35d4d1-b419-4244-96d0-892d41c3dbd6', 'Anesthetist MB', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(26, '88775e20-34bc-4efb-8073-baa53c4e23b3', 'Anesthetist NA', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(27, '615a3775-7158-43e8-be84-c082043220e4', 'Anesthetist GJ', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(28, '081d6dfa-1987-4af9-b052-538aecf9726b', 'Anesthetist NS', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(29, '35cdfcdc-cd2e-4682-9044-128f1cda6f42', 'Anesthetist NF', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(30, 'cb0f89f0-2839-4d45-8d08-525093308a28', 'Anesthetist TN', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(31, '5897a4be-4e38-4aad-b0fe-d9f4f57b99c5', 'DHS', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(32, '32d1b658-3ab0-480e-92ed-84a2ec5a5b66', 'Biolitec', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(33, '7fb4757c-2782-4f81-a617-d5207f290d19', 'LxG', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(34, '968e690b-0afe-4e3f-bfbb-de9a565c4b30', 'Endosurg', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(35, '339a49ba-786f-4b1e-9116-620ed7e762eb', 'Endoscopy Tower', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(36, '1d156e96-6ac8-4a48-9844-cce5f91c711b', 'Consultation M00', 'Consultation moo', 3, 5, '2025-08-09 05:17:46', '2025-08-09 05:17:46', NULL),
(37, '61c7d3b3-2d05-467e-bb82-f7ee8cba7368', 'Consultation M000', NULL, 3, 3, '2025-08-09 05:18:13', '2025-08-09 05:18:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2BDZuX4fBcfr6yxZNoZSwWwThlbWoMQ8Kyzz7lqA', NULL, '102.222.234.66', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRGZpSTJPZmRPb2luZzlqbEplOE9qYTZvWm1Kb01rN2lJdTdnZk5YbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756470108),
('2CoTXnZEOyc1iizdVHonhzW6zhT8YYLhynAmoEpw', NULL, '206.168.34.201', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSDVVV1ZjQ2F1VEJ1MDRMZ1d4dWgxejJnNTU4c1R4aExLMU01UWtGOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vd3d3LnN0YWdpbmcua2FzaHRyZS5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1756016681),
('3aS1DhZjPZwpFrwRmFhNGbUR32bazI9zLWivHWPs', NULL, '2c0f:3d00:62f:6700:3ccc:c720:29b8:34c7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:142.0) Gecko/20100101 Firefox/142.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWDdSckNhZjZyMjJONjVmU1d2U1BpTmluWnpvT05qb3N0a295dVVsMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756033812),
('5EKzmRylNY0y47r85ZDvlQB8YGXAKg3T6RKDzLm5', NULL, '66.249.68.4', 'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicmJEdnM0STFkdnVQNmVuWENVRVBGdTVQNnZjRFI2d3ludUFrUW8yUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756882984),
('83MYxEXbHy7GnZcju5gdQtSYval7vVPspeMvTWVa', NULL, '206.168.34.201', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWjAxUThIbzUxNGM4bFdsYUlBZDF3UFdSTTdnU2ZGZnRMVVJqM1lmeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vd3d3LnN0YWdpbmcua2FzaHRyZS5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1756016654),
('9KmSnNJu2JO06APL3zbAxCtHn88W5hC3Zym6bIfq', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN0ExMGhQWU9mbWRUR2Z6QkxlUGs0anBNdVc0NkZyTDlNbmY3UGtXaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756859680),
('9thAPIht9qf38oRmK26HsfolAAgq3SStYKjWd8Pk', NULL, '2602:80d:1000::16', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieTdzeE1OVWpSMmRWZ2lXYkxzcXE0NlRPVjFwTmZybXpRYXU2MDlYWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vd3d3LnN0YWdpbmcua2FzaHRyZS5jb20vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1756523309),
('bx4XNlKmciAU0kYPq6UoonI4uLu72cjCty5zy6Wt', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaXF2SjZWUEZKbUMyTkdxSVJndk55OHBhZmFZbWYzbzVua1dDczNENSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756601050),
('d4lJ1ZhHZvfMCCuQPiRNxGGLVZztrt8IzktrmlwR', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQjUySGM5VHJ0dDlZNUdPUzE2MWVtd0NxRGJjdVVTeVcyZEN2YTdaSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756257348),
('Dvl2IYjAZPDyKxxJqL6ae9CRwmxfocJX7j5sSLOP', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0FNcWxJR0N2UnJwMGlXaGpUalB5c2htNkNFa1VLSnROdHI3aTFZbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756791372),
('fiT3OsMxqRlQtw02iQwBhMMhMqttL0u5iNDNX1XA', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia0FoTnVWb21TaFRkQVpnNXZsSGthMEhiNkpvbUpnQ3B4UWZJVTR3QyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756178682),
('H73vzGD7EvPH9EwhpKxFjS0iIpvkYkQjvVoDT7qx', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieW45N3p5S3JWeE1FSU1NeVF2REY0ZXBGNDE5bWRxVndBTUVmZHdLZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756447222),
('H7pjCdxG8a8Ru6AZLTs8K7URQhb5RIs8ijf887oo', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVUk1VVgyRExOM1diaVdoRUtaTWNKZmFHejZqYWg2OXM2SU5DamJkWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756106593),
('JIzP16axqecV6nCgRggPyvzZNNkU08bCrEhKYzP4', NULL, '206.168.34.201', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV1hFbTlmWklWZUlKeUMxbkU0blViMjliQ2hQOXFIRVA0OHpoRE01MiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnN0YWdpbmcua2FzaHRyZS5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1756016632),
('Jkwpj3yAeqF2TB3MbLBXkltPHaEuFUECeJCXO6eP', NULL, '34.1.31.38', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko; compatible; BW/1.3; rb.gy/qyzae5) Chrome/124.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ1RBTnJCQ21ZbW5STFVrelJnMXNkNGY2bU1KTEh5UTBEeFNTcDVybiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756332445),
('JMNg58zB2dGivZEnrY1ERl8iDxFpMHeU9JqJHi2U', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR1BSc2dYOWRBTHBBcVJuYVdYejBhSTJQNDhiZm0wWGY3cWlmZUVVeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756106593),
('kbVSW31NR3pZccRs3zaFad5PvbS1paouEeUijn01', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUW4yTlVJbnJkYktQbVZqU1JLbjVzTDVyc3JscVNQWjBjYkdVbTEybSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756954865),
('LFb2kgal395QRO0RuMqecyHGSRiUanVWx8TPdFOX', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR1l4Q3BKTXpjV1dSM2lkM0tVcHJpTFZ6Wm1iUVFFRGx3Q0JJY0VlSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756178681),
('lzhrTIroWXE4fNKAZoye5DQiOHCcjCPa5FCZA19Q', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR0lLVUQyUEQ3cVoyWjkydjZDbERsNVl2RFowMlZNbTVTWGFHazlUSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756257348),
('M8x7XhMSBavGebVqr5hE3xVQJzakqojwrBRuu1Mm', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYThhTVJJUHpnUVdwVVNjbjltZ1cybDQ5ZFRIYjFwcDlZeUhBeEJ5bCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756710911),
('NZaTcWRUrzfC65ObWBmHxOdESwDRMmQQurpiZyHx', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiejhxd3d0YkZCSGdxTXJYRkVWTmJlQzZidE5yYlhtak02MlRsWUhkNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756363134),
('oOaKncjDnpF9MLpH6evHm0Z018oPgtgGQJkCcbVk', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia0dIS09lWkNZSG9EWjV6OEtUdUtuUDZ2ZGhTTUxZenpzUzR0T1dCdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756710911),
('pPpq5wUCugWZC29PesAKSqCdV8YRYjs9A1V4orfK', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU3pQdVFEUmY2aEJadUlMVU1iY1hjYmhLcGhKbXl2NnJ3YXlpUnkxVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756528470),
('PyFslrsoeBx0lZmY1OT0S6rl2ySjLXrCO0MowUuV', NULL, '159.203.130.89', 'Mozilla/5.0 (X11; Linux x86_64; rv:139.0) Gecko/20100101 Firefox/139.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSW9wck9MOGRIeWNObjBtdnRZOWZqUEttNVkxcEFldE1kYWl2c081WSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756891976),
('qBmWc6ZTjt6MWLE5Z02QiNa1goSIDgsT1zQIbAO1', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM1VXSktTUGFXa01Qd2ZQbGFNNUs5VzhoSWUyRGhSQk1SaVB6VUROZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756447222),
('smvxKYpwXKPEC6OiIRJD0CDmeryD5cfjfCzs7s1j', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVGNPczVaQjFjZmxMYXRwZk50NnFBV1ViNzBYOXI0N09XNHdvbU52QiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1757045599),
('TPOvTiOMjKttO1AqZAfEIJrmRRNB0lpoq96kAOKm', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQTdkdnl4c25JV2RaU3hCbVhxTW9oSFdqZGZ6S2toM1ptVVA4OG13eiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756859680),
('UsNKdBd6AjUBuxRT3fal4T2l3zlPIfqE0gnJdpSi', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUndlTUQxSUhYczNHSHlOR3JsajBvU1VVQWNmNmhBSmd5OVNDMGdBMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756015667),
('v1pDKV2Aa2u3waYudkqpczAzzr8LGR4QV4LmKEme', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaVF4QjlKQko5YzRDU1VDb0ZpdTVkSUpseFlFUjVzVFNSVUNIOXdiVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756791372),
('VIpQecfWp7cWNwMn82p3m0pxxB63ZS1URYLc818X', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZzNxTnVhZndvRlVkdFJpQXc2QThnSk04UkNPZEpTTEkyUE0yZTlHYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756528470),
('voaHljdEhzBAc79ecq1yRcG0UAql9RzMR4E41OPi', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT3MwUlV2eWFvTUk4eXAxU05UQ2ZqTTlYQXdTeW01M1NybUhMRzFqVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756363134),
('W6hRXNTBkDxTtNGOJJcdxTlkpi4rYebRclhMpNpE', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid1JkZTNLVzJpdG1PT0t0RlBMYll0dTlKVlNUS0pheEpwNWRkdUFxNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756015667),
('WB3NtqoQb8sRm9tptVIr251aEdvzxLzplMuXyWPg', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUkpTcHU2WmZQR3ZwdE9BSTVCR09JZzd3dk9oVnZ6N1o0QjJ5WUYxbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756601050),
('WHOqaVCS6NyzIksARLacJKOaXe5uW1AqLnnj6VTK', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXpjQW5mOTQ1a3pjbG5Fa1NyU0FaRldkRElPb0JVdEZqY0xvcWp5MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756954865),
('X8O4kh8JbERQoBS8HIvmZsfBczqspslq22PqXdj1', NULL, '66.249.68.2', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSjBJVnFtWnEzVUtGR0hWbGlRWlhhNzRZTlp1UE5KbWxYTkxUTkdxaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1756882984),
('xwN59UnVJqxJk9U4zlk5UVhCOdjGYZTbFghZpi10', NULL, '2a02:4780:27:c0de::3cc', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmRxVThRYUZNMDZ0TFJpeFF6R1NINzRLZ0dGTlNNYnZTSEhYYlJTMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vc3RhZ2luZy5rYXNodHJlLmNvbS9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1757045599);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '9d40594f-c378-4729-8c98-a2917ec81fd1', 'Main Store', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(2, '72f3ecda-11fc-467d-9bc9-be1c79bea2a0', 'Surgical Store', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(3, 'b692035e-1be1-41c4-b3ae-8041405040d1', 'Gyne Store', NULL, 3, 4, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sub_groups`
--

CREATE TABLE `sub_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_groups`
--

INSERT INTO `sub_groups` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'd748e731-0c1f-4cf7-ad11-01d69e21fd17', 'Anaesthetics', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(2, '3d451a2c-bc84-4581-bdc5-d730e0b01194', 'Analgesia', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(3, '572ab96e-e33c-4620-8501-d04c4cfae731', 'Anesthesia Consumables', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(4, '65e64e53-0a28-4044-a69c-c32f3aa7712b', 'Anesthesia service', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(5, '22009ddb-4b1b-4d71-a386-535740ae0650', 'Antiacid', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(6, 'af5058b6-e338-47f2-87ee-ca06ebf0ae33', 'Antibiotics', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(7, '92786359-833f-4c4a-82dd-a37664956713', 'Antiemetics', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(8, '23702e21-33e2-4a0b-a08a-8470585f34eb', 'Consultation', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(9, '68132bb8-6056-4301-932c-74c5e6971a21', 'Daycare', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(10, '58778ac2-386d-43b7-b39a-71defa2b55c1', 'Disinfectant', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(11, '8257b6da-9102-45c9-9efa-a855bb1b2f9d', 'Dressings', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(12, '06fa1232-b857-4c1a-aed1-432a1e6b6f71', 'Endoscopy equipment', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(13, 'c2899b09-909e-47f0-83e3-1aff9eddc41e', 'Energy Sources', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(14, 'c9808f61-5dc0-4eb9-b1e2-c8e8eb12cfcf', 'Finance', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(15, '145868cf-d384-4ec4-87fe-2aa87125b6da', 'General Consumables', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(16, '8cdbc9aa-56c4-4012-ba14-ddde740e0256', 'GI Active', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(17, 'f285386b-622b-453d-9cd2-0909668844b6', 'Inpatient', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(18, '1875039b-a5cb-493b-915e-1bf8cfcaf5fd', 'IV Fluids', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(19, '3716577d-1099-40b5-a78c-b4cfe4010078', 'Laxatives', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(20, '257c81ac-9c23-4ddb-a48d-fc4356ed05af', 'Neuroactive', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(21, '45cb5b8d-4553-41ae-bbc2-33a644eee64f', 'Nursing Services', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(22, '6cf6ef23-c66f-4cba-aa2c-a16b6a66728b', 'Pro-coagulant', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(23, 'aaa464c9-f80e-4162-b01e-2cca28c6d534', 'Procedure', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(24, '8023f0e1-8372-4570-93f6-71f63b1830ef', 'Professional fees', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(25, '702cdf18-9003-4810-86d5-6ad5fdc87179', 'Staplers', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(26, 'd63ffbb4-7861-460c-8a9d-6a78be12e27e', 'Steroids', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(27, 'e859d444-16ec-4fd0-b699-e509df90716d', 'Sutures', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(28, '144b4bcd-70c9-452d-90dd-ad559b3d4313', 'Syringes', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(29, '7c88bcbf-bdd1-49a2-9a03-cb1b5cd2f065', 'Theatre Consumables', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(30, '8d75c916-3885-4eab-a65c-688b0a165cbf', 'Theatre Services', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(31, '0a4e002a-b9b6-444f-8acc-fa23d3f152eb', 'Vasoactive', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(32, '75661e4e-33d1-4874-9382-7ed09ff377b4', 'Wound healing', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ee87a0d1-57dd-4946-bba5-232b7f17d1ab', 'Friecca Pharmacy', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(2, '281adc73-4a04-4fa9-bd5d-6f0e10c7107d', 'First pharmacy', NULL, 3, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

CREATE TABLE `titles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `titles`
--

INSERT INTO `titles` (`id`, `uuid`, `business_id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '823b2bcf-c5b5-4819-80e5-f7c7ea3b13bf', 1, 'Dr.', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(2, '56ddeb36-979d-44f3-b579-a08816e539b5', 1, 'Mr.', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(3, '5490173c-6140-4322-9c57-506831b11702', 1, 'Ms.', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(4, '8a455212-d217-4f76-9007-dcbc63b06196', 1, 'Prof.', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(5, '1d2312ae-f325-437b-98a1-84e86df68ea1', 1, 'Eng.', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(6, 'da172a9f-770c-47a7-980e-9ba3bbeefd01', 1, 'Sr.', NULL, '2025-07-20 20:05:52', '2025-07-20 20:05:52', NULL),
(7, '14cce729-9cbb-4769-ab97-becc769c3848', 3, 'CEO', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(8, '744d1a0e-9b04-4724-b27f-76b4ddb1fbad', 3, 'Surgeon', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(9, '6c8b1de0-1d75-4d6c-9144-e9b45849b0e0', 3, 'Nurse', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(10, '0c0eab8e-57e5-4614-9b62-7a09394dbb79', 3, 'Anesthetist', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(11, 'ca7f6830-e40b-4008-b178-d918e4fde9e9', 3, 'Anesthesiologist', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(12, 'cc6a2d9f-7a57-4f5b-bc46-4f35c70c280b', 3, 'Receptionist', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(13, 'a7c902b6-0f7f-4238-8ebf-b452e761552b', 3, 'Finance Manager', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(14, 'a3a43802-248f-4d9a-89a1-d79030baad18', 3, 'Accountant', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(15, 'e90c8c1e-1448-4306-a7ca-88349cfa658e', 3, 'Administrator', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(16, '4751e67c-4d58-4f08-b1d6-3e10332e9518', 3, 'Auditor', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(17, '76bcd5ae-f767-4235-86a1-3c2feaa2494f', 3, 'Medical Officer', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(18, 'e1d4cced-e15b-4f29-b5ff-1bc59adb1e67', 3, 'Company', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL),
(19, '82a9d9eb-31fb-43a1-8fe6-98c8712be4de', 3, 'Clinical Officer', NULL, '2025-08-09 05:17:11', '2025-08-09 05:17:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `business_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `reference` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled','processing') DEFAULT NULL,
  `type` enum('credit','debit') NOT NULL,
  `origin` enum('api','mobile','web','payment_link') NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `provider` enum('mtn','airtel') NOT NULL,
  `service` varchar(255) NOT NULL,
  `date` date NOT NULL DEFAULT '2025-07-20',
  `currency` varchar(255) NOT NULL DEFAULT 'UGX',
  `names` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `method` varchar(255) NOT NULL DEFAULT 'card',
  `transaction_for` enum('main','charge') NOT NULL DEFAULT 'main',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `uuid`, `business_id`, `branch_id`, `amount`, `reference`, `description`, `status`, `type`, `origin`, `phone_number`, `provider`, `service`, `date`, `currency`, `names`, `email`, `ip_address`, `user_agent`, `method`, `transaction_for`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'fbfe648b-9415-495c-9098-64afefd9c845', 3, NULL, '1339', 'INV2025080001', 'Payment for invoice INV2025080001 - Nicholas Katende', 'completed', 'debit', 'web', '+256759983851', 'mtn', 'invoice_payment', '2025-08-22', 'UGX', 'Nicholas Katende', NULL, '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'insurance', 'main', '2025-08-22 18:29:54', '2025-08-22 18:29:54', NULL),
(2, '7473b1d6-6f77-48f6-965a-18a178c855d2', 3, NULL, '1572', 'INV2025080002', 'Payment for invoice INV2025080002 - Nicholas Katende', 'completed', 'debit', 'web', '+2567599838534', 'mtn', 'invoice_payment', '2025-08-22', 'UGX', 'Nicholas Katende', NULL, '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'insurance', 'main', '2025-08-22 18:42:46', '2025-08-22 18:42:46', NULL),
(3, '63272a37-3d6b-477b-b148-b1da0ac6fba9', 3, NULL, '1341', 'INV2025080003', 'Payment for invoice INV2025080003 - Nicholas Katende', 'completed', 'debit', 'web', '+2567599838534', 'mtn', 'invoice_payment', '2025-08-22', 'UGX', 'Nicholas Katende', NULL, '197.239.10.26', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'insurance', 'main', '2025-08-22 18:52:07', '2025-08-22 18:52:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `nin` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `business_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`service_points`)),
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permissions`)),
  `allowed_branches` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`allowed_branches`)),
  `qualification_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `phone`, `nin`, `remember_token`, `profile_photo_path`, `status`, `business_id`, `branch_id`, `service_points`, `permissions`, `allowed_branches`, `qualification_id`, `department_id`, `section_id`, `title_id`, `gender`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '66624542-a493-42d7-bccd-eca0acff9a95', 'Kashtre Admin', 'katznicho@gmail.com', NULL, '$2y$12$nyT7XPxAj49V/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW', NULL, NULL, NULL, '256700000003', 'CF123456789012', NULL, NULL, 'active', 1, 1, '\"[]\"', '[\"Dashboard\",\"Dashboard\",\"View Dashboard\",\"View Dashboard Cards\",\"View Dashboard Charts\",\"Entities\",\"Entities\",\"View Entities\",\"Edit Entities\",\"Add Entities\",\"Delete Entities\",\"Items\",\"Items\",\"View Items\",\"Edit Items\",\"Add Items\",\"Delete Items\",\"Bulk Upload Items\",\"Staff\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Reports\",\"Reports\",\"View Report\",\"Edit Report\",\"Add Report\",\"Delete Report\",\"Logs\",\"Logs\",\"View Logs\",\"Sales\",\"Sales\",\"View Sales\",\"Edit Sales\",\"Add Sales\",\"Delete Sales\",\"Clients\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Queues\",\"Customers\",\"View Queues\",\"Withdrawals\",\"Withdrawals\",\"View Withdrawals\",\"Edit Withdrawals\",\"Add Withdrawals\",\"Delete Withdrawals\",\"Modules\",\"Modules\",\"View Modules\",\"Edit Modules\",\"Add Modules\",\"Delete Modules\",\"Masters\",\"Service Points\",\"View Service Points\",\"Edit Service Points\",\"Add Service Points\",\"Delete Service Points\",\"Bulky Update Service Points\",\"Service Charges\",\"Manage Service Charges\",\"Contractor Service Charges\",\"Manage Contractor Service Charges\",\"Departments\",\"View Departments\",\"Edit Departments\",\"Add Departments\",\"Delete Departments\",\"Bulky Update Departments\",\"Qualifications\",\"View Qualifications\",\"Edit Qualifications\",\"Add Qualifications\",\"Delete Qualifications\",\"Bulky Update Qualifications\",\"Titles\",\"View Titles\",\"Edit Titles\",\"Add Titles\",\"Delete Titles\",\"Bulky Update Titles\",\"Rooms\",\"View Rooms\",\"Edit Rooms\",\"Add Rooms\",\"Delete Rooms\",\"Bulky Update Rooms\",\"Sections\",\"View Sections\",\"Edit Sections\",\"Add Sections\",\"Delete Sections\",\"Bulky Update Sections\",\"Item Units\",\"View Item Units\",\"Edit Item Units\",\"Add Item Units\",\"Delete Item Units\",\"Bulky Update Item Units\",\"Groups\",\"View Groups\",\"Edit Groups\",\"Add Groups\",\"Delete Groups\",\"Bulky Update Groups\",\"Patient Categories\",\"View Patient Categories\",\"Edit Patient Categories\",\"Add Patient Categories\",\"Delete Patient Categories\",\"Bulky Update Patient Categories\",\"Suppliers\",\"View Suppliers\",\"Edit Suppliers\",\"Add Suppliers\",\"Delete Suppliers\",\"Bulky Update Suppliers\",\"Stores\",\"View Stores\",\"Edit Stores\",\"Add Stores\",\"Delete Stores\",\"Bulky Update Stores\",\"Insurance Companies\",\"View Insurance Companies\",\"Edit Insurance Companies\",\"Add Insurance Companies\",\"Delete Insurance Companies\",\"Bulky Update Insurance Companies\",\"Sub Groups\",\"View Sub Groups\",\"Edit Sub Groups\",\"Add Sub Groups\",\"Delete Sub Groups\",\"Bulky Update Sub Groups\",\"Admin\",\"Admin Users\",\"View Admin Users\",\"Edit Admin Users\",\"Add Admin Users\",\"Delete Admin Users\",\"Assign Roles\",\"Audit Logs\",\"View Audit Logs\",\"System Settings\",\"View System Settings\",\"Edit System Settings\",\"Business\",\"Business\",\"View Business\",\"Edit Business\",\"Add Business\",\"Delete Business\",\"Branches\",\"View Branches\",\"Edit Branches\",\"Add Branches\",\"Delete Branches\",\"Client\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Staff Access\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Edit Contractor\",\"Add Contractor Profile\",\"View Contractor Profile\",\"Edit Contractor Profile\",\"Report Access\",\"Reports\",\"View Reports\",\"Export Reports\",\"Filter Reports\",\"Bulk Upload\",\"Bulk Upload\",\"Bulk Validations Upload\"]', '\"[1]\"', 1, 1, 1, 1, 'male', '2025-07-20 20:05:52', '2025-08-22 18:00:15', NULL),
(3, '5a785375-53e7-4bad-85e8-15af3e97b77f', 'Nicholas Katende', 'katznicho+124@gmail.com', NULL, '', NULL, NULL, NULL, '0759983853', 'EHFJFGNGFG,RF', NULL, NULL, 'active', 1, 1, '[]', '[\"Dashboard\",\"Dashboard\",\"View Dashboard\",\"View Dashboard Cards\",\"View Dashboard Charts\",\"Entities\",\"Entities\",\"View Entities\",\"Edit Entities\",\"Add Entities\",\"Delete Entities\",\"Items\",\"Items\",\"View Items\",\"Edit Items\",\"Add Items\",\"Delete Items\",\"Bulk Upload Items\",\"Staff\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Reports\",\"Reports\",\"View Report\",\"Edit Report\",\"Add Report\",\"Delete Report\",\"Logs\",\"Logs\",\"View Logs\",\"Products\",\"Products\",\"View Products\",\"Edit Products\",\"Add Products\",\"Delete Products\",\"Sales\",\"Sales\",\"View Sales\",\"Edit Sales\",\"Add Sales\",\"Delete Sales\",\"Clients\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Queues\",\"Customers\",\"View Queues\",\"Withdrawals\",\"Withdrawals\",\"View Withdrawals\",\"Edit Withdrawals\",\"Add Withdrawals\",\"Delete Withdrawals\",\"Modules\",\"Modules\",\"View Modules\",\"Edit Modules\",\"Add Modules\",\"Delete Modules\",\"Masters\",\"Service Points\",\"View Service Points\",\"Edit Service Points\",\"Add Service Points\",\"Delete Service Points\",\"Bulky Update Service Points\",\"Departments\",\"View Departments\",\"Edit Departments\",\"Add Departments\",\"Delete Departments\",\"Bulky Update Departments\",\"Qualifications\",\"View Qualifications\",\"Edit Qualifications\",\"Add Qualifications\",\"Delete Qualifications\",\"Bulky Update Qualifications\",\"Titles\",\"View Titles\",\"Edit Titles\",\"Add Titles\",\"Delete Titles\",\"Bulky Update Titles\",\"Rooms\",\"View Rooms\",\"Edit Rooms\",\"Add Rooms\",\"Delete Rooms\",\"Bulky Update Rooms\",\"Sections\",\"View Sections\",\"Edit Sections\",\"Add Sections\",\"Delete Sections\",\"Bulky Update Sections\",\"Item Units\",\"View Item Units\",\"Edit Item Units\",\"Add Item Units\",\"Delete Item Units\",\"Bulky Update Item Units\",\"Groups\",\"View Groups\",\"Edit Groups\",\"Add Groups\",\"Delete Groups\",\"Bulky Update Groups\",\"Patient Categories\",\"View Patient Categories\",\"Edit Patient Categories\",\"Add Patient Categories\",\"Delete Patient Categories\",\"Bulky Update Patient Categories\",\"Suppliers\",\"View Suppliers\",\"Edit Suppliers\",\"Add Suppliers\",\"Delete Suppliers\",\"Bulky Update Suppliers\",\"Stores\",\"View Stores\",\"Edit Stores\",\"Add Stores\",\"Delete Stores\",\"Bulky Update Stores\",\"Insurance Companies\",\"View Insurance Companies\",\"Edit Insurance Companies\",\"Add Insurance Companies\",\"Delete Insurance Companies\",\"Bulky Update Insurance Companies\",\"Sub Groups\",\"View Sub Groups\",\"Edit Sub Groups\",\"Add Sub Groups\",\"Delete Sub Groups\",\"Bulky Update Sub Groups\",\"Admin\",\"Admin Users\",\"View Admin Users\",\"Edit Admin Users\",\"Add Admin Users\",\"Delete Admin Users\",\"Assign Roles\",\"Audit Logs\",\"View Audit Logs\",\"System Settings\",\"View System Settings\",\"Edit System Settings\",\"Business\",\"Business\",\"View Business\",\"Edit Business\",\"Add Business\",\"Delete Business\",\"Branches\",\"View Branches\",\"Edit Branches\",\"Add Branches\",\"Delete Branches\",\"Client\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Staff Access\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Report Access\",\"Reports\",\"View Reports\",\"Export Reports\",\"Filter Reports\",\"Bulk Upload\",\"Bulk Upload\",\"Bulk Validations Upload\"]', '[1]', NULL, NULL, NULL, NULL, 'male', '2025-07-23 10:27:29', '2025-07-23 10:27:29', NULL),
(4, '332e1f47-dc52-44c1-88e0-84f631b73f25', 'Andrew Muleledhu', 'muleledhu@yahoo.com', NULL, '$2y$12$l4T2FmtM2QBxi0/TW4f/TOIVPB3DiYVsONf1wenMVSF/haQSFOm8a', NULL, NULL, NULL, '0751318504', 'EHFJFGNGFG,RFQ', 'yazG5AcN5akG7ABRbHnS1xS6Z5Lq2sggHqLbVXTYGEVhncyQxSyMc0YLpuYv', NULL, 'active', 1, 1, '[]', '[\"Dashboard\",\"Dashboard\",\"View Dashboard\",\"View Dashboard Cards\",\"View Dashboard Charts\",\"Entities\",\"Entities\",\"View Entities\",\"Edit Entities\",\"Add Entities\",\"Delete Entities\",\"Items\",\"Items\",\"View Items\",\"Edit Items\",\"Add Items\",\"Delete Items\",\"Bulk Upload Items\",\"Staff\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Reports\",\"Reports\",\"View Report\",\"Edit Report\",\"Add Report\",\"Delete Report\",\"Logs\",\"Logs\",\"View Logs\",\"Products\",\"Products\",\"View Products\",\"Edit Products\",\"Add Products\",\"Delete Products\",\"Sales\",\"Sales\",\"View Sales\",\"Edit Sales\",\"Add Sales\",\"Delete Sales\",\"Clients\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Queues\",\"Customers\",\"View Queues\",\"Withdrawals\",\"Withdrawals\",\"View Withdrawals\",\"Edit Withdrawals\",\"Add Withdrawals\",\"Delete Withdrawals\",\"Modules\",\"Modules\",\"View Modules\",\"Edit Modules\",\"Add Modules\",\"Delete Modules\",\"Masters\",\"Service Points\",\"View Service Points\",\"Edit Service Points\",\"Add Service Points\",\"Delete Service Points\",\"Bulky Update Service Points\",\"Departments\",\"View Departments\",\"Edit Departments\",\"Add Departments\",\"Delete Departments\",\"Bulky Update Departments\",\"Qualifications\",\"View Qualifications\",\"Edit Qualifications\",\"Add Qualifications\",\"Delete Qualifications\",\"Bulky Update Qualifications\",\"Titles\",\"View Titles\",\"Edit Titles\",\"Add Titles\",\"Delete Titles\",\"Bulky Update Titles\",\"Rooms\",\"View Rooms\",\"Edit Rooms\",\"Add Rooms\",\"Delete Rooms\",\"Bulky Update Rooms\",\"Sections\",\"View Sections\",\"Edit Sections\",\"Add Sections\",\"Delete Sections\",\"Bulky Update Sections\",\"Item Units\",\"View Item Units\",\"Edit Item Units\",\"Add Item Units\",\"Delete Item Units\",\"Bulky Update Item Units\",\"Groups\",\"View Groups\",\"Edit Groups\",\"Add Groups\",\"Delete Groups\",\"Bulky Update Groups\",\"Patient Categories\",\"View Patient Categories\",\"Edit Patient Categories\",\"Add Patient Categories\",\"Delete Patient Categories\",\"Bulky Update Patient Categories\",\"Suppliers\",\"View Suppliers\",\"Edit Suppliers\",\"Add Suppliers\",\"Delete Suppliers\",\"Bulky Update Suppliers\",\"Stores\",\"View Stores\",\"Edit Stores\",\"Add Stores\",\"Delete Stores\",\"Bulky Update Stores\",\"Insurance Companies\",\"View Insurance Companies\",\"Edit Insurance Companies\",\"Add Insurance Companies\",\"Delete Insurance Companies\",\"Bulky Update Insurance Companies\",\"Sub Groups\",\"View Sub Groups\",\"Edit Sub Groups\",\"Add Sub Groups\",\"Delete Sub Groups\",\"Bulky Update Sub Groups\",\"Admin\",\"Admin Users\",\"View Admin Users\",\"Edit Admin Users\",\"Add Admin Users\",\"Delete Admin Users\",\"Assign Roles\",\"Audit Logs\",\"View Audit Logs\",\"System Settings\",\"View System Settings\",\"Edit System Settings\",\"Business\",\"Business\",\"View Business\",\"Edit Business\",\"Add Business\",\"Delete Business\",\"Branches\",\"View Branches\",\"Edit Branches\",\"Add Branches\",\"Delete Branches\",\"Client\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Staff Access\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Report Access\",\"Reports\",\"View Reports\",\"Export Reports\",\"Filter Reports\",\"Bulk Upload\",\"Bulk Upload\",\"Bulk Validations Upload\"]', '[1]', NULL, NULL, NULL, NULL, 'male', '2025-07-23 18:33:21', '2025-07-27 08:13:47', NULL),
(5, '884a5b39-16fb-4723-b800-75b839372bb5', 'Test  Admin One', 'tracepeso@gmail.com', NULL, '', NULL, NULL, NULL, '', 'CMQERTYTOYO', NULL, NULL, 'active', 1, 1, '[]', '[\"\"]', '[1]', NULL, NULL, NULL, NULL, 'male', '2025-07-29 20:35:27', '2025-07-29 20:35:27', NULL),
(6, '3971435b-4524-444d-b389-b4bd3d9e9717', 'Test  Admin Two', 'katznicho+9834@gmail.com', NULL, '', NULL, NULL, NULL, '', 'CMQERTYTOYO1D', NULL, NULL, 'active', 1, 1, '[]', '[\"\"]', '[1]', NULL, NULL, NULL, NULL, 'male', '2025-07-29 20:35:27', '2025-07-29 20:35:27', NULL),
(7, '634b6347-5c1b-4b26-b276-264f3a912395', 'Sample Staff Name', 'katznicho+734563@gmail.com', NULL, '', NULL, NULL, NULL, '1234567890', 'CMRJRTJTRWQWER', NULL, NULL, 'active', 4, 2, '[]', '[\"View Dashboard\"]', '[\"2\"]', NULL, NULL, NULL, NULL, 'male', '2025-07-29 20:57:40', '2025-07-29 20:57:40', NULL),
(8, '335430e3-cf25-451d-b6b0-8330adecd1bc', 'Sample Contractor Name', 'tracepeso+123@gmail.com', NULL, '', NULL, NULL, NULL, '0987654321', 'CMRJRTJTRWQWERQE', NULL, NULL, 'active', 4, 2, '[]', '[\"Contractor\",\"View Contractor\",\"Edit Contractor\",\"Add Contractor\"]', '[\"2\"]', NULL, NULL, NULL, NULL, 'female', '2025-07-29 20:57:40', '2025-07-29 20:57:40', NULL),
(9, '9947cb86-7181-4108-be23-61e515d28b0b', 'Kikomeko Huzairu', 'kikomekohudhairuh@gmail.com', NULL, '', NULL, NULL, NULL, '0759950503', 'EHFJFGNGFG,RF', NULL, NULL, 'active', 3, 4, '[\"Pharmacy\",\"Endoscopy Procedure\",\"Consultation Dr KP\",\"Consultation Dr AV\",\"Consultation Dr KG\",\"Consultation Dr NV\"]', '[\"Dashboard\",\"Dashboard\",\"View Dashboard\",\"View Dashboard Cards\",\"View Dashboard Charts\",\"Entities\",\"Entities\",\"View Entities\",\"Edit Entities\",\"Add Entities\",\"Delete Entities\",\"Items\",\"Items\",\"View Items\",\"Edit Items\",\"Add Items\",\"Delete Items\",\"Bulk Upload Items\",\"Staff\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Contractor\",\"Contractor\",\"View Contractor\",\"Edit Contractor\",\"Add Contractor Profile\",\"View Contractor Profile\",\"Edit Contractor Profile\"]', '[\"3\",\"4\",\"5\"]', 7, 7, 6, 7, 'male', '2025-08-09 08:26:39', '2025-08-09 08:26:39', NULL),
(10, '5156fd8e-ead7-4069-a0fd-c448cf00ea2f', 'Nicholas Katende', 'katznico1000@gmail.com', NULL, '$2y$12$nyT7XPxAj49V/4H2qHqRPem91qnP0.xjnCZKiP3Pd8ZEZJ9xCK7qW', NULL, NULL, NULL, '0759983853', 'CMW436567776767123', NULL, NULL, 'active', 3, 4, '[\"Admission\",\"Security\\/Gate\",\"Discharge\",\"Finance\"]', '[\"Dashboard\",\"Dashboard\",\"View Dashboard\",\"View Dashboard Cards\",\"View Dashboard Charts\",\"Entities\",\"Entities\",\"View Entities\",\"Edit Entities\",\"Add Entities\",\"Delete Entities\",\"Items\",\"Items\",\"View Items\",\"Edit Items\",\"Add Items\",\"Delete Items\",\"Bulk Upload Items\",\"Staff\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Reports\",\"Reports\",\"View Report\",\"Edit Report\",\"Add Report\",\"Delete Report\",\"Logs\",\"Logs\",\"View Logs\",\"Sales\",\"Sales\",\"View Sales\",\"Edit Sales\",\"Add Sales\",\"Delete Sales\",\"Clients\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Queues\",\"Customers\",\"View Queues\",\"Withdrawals\",\"Withdrawals\",\"View Withdrawals\",\"Edit Withdrawals\",\"Add Withdrawals\",\"Delete Withdrawals\",\"Modules\",\"Modules\",\"View Modules\",\"Edit Modules\",\"Add Modules\",\"Delete Modules\",\"Stock\",\"Stock\",\"View Stock\",\"Edit Stock\",\"Add Stock\",\"Delete Stock\",\"Admin\",\"Admin Users\",\"View Admin Users\",\"Edit Admin Users\",\"Add Admin Users\",\"Delete Admin Users\",\"Assign Roles\",\"Bulk Admin Upload\",\"Audit Logs\",\"View Audit Logs\",\"System Settings\",\"View System Settings\",\"Edit System Settings\",\"Business\",\"Business\",\"View Business\",\"Edit Business\",\"Add Business\",\"Delete Business\",\"Branches\",\"View Branches\",\"Edit Branches\",\"Add Branches\",\"Delete Branches\",\"Client\",\"Clients\",\"View Clients\",\"Edit Clients\",\"Add Clients\",\"Delete Clients\",\"Staff Access\",\"Staff\",\"View Staff\",\"Edit Staff\",\"Add Staff\",\"Delete Staff\",\"Assign Roles\",\"Edit Contractor\",\"Add Contractor Profile\",\"View Contractor Profile\",\"Edit Contractor Profile\",\"Report Access\",\"Reports\",\"View Reports\",\"Export Reports\",\"Filter Reports\",\"Bulk Upload\",\"Bulk Upload\",\"Bulk Validations Upload\"]', '[]', 7, 6, 6, 8, 'male', '2025-08-22 18:10:33', '2025-08-22 18:10:33', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_logs_uuid_unique` (`uuid`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`),
  ADD KEY `activity_logs_business_id_foreign` (`business_id`),
  ADD KEY `activity_logs_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `branches_email_unique` (`email`),
  ADD KEY `branches_business_id_foreign` (`business_id`);

--
-- Indexes for table `branch_item_prices`
--
ALTER TABLE `branch_item_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_item_prices_uuid_unique` (`uuid`),
  ADD KEY `branch_item_prices_business_id_foreign` (`business_id`),
  ADD KEY `branch_item_prices_branch_id_foreign` (`branch_id`),
  ADD KEY `branch_item_prices_item_id_foreign` (`item_id`);

--
-- Indexes for table `branch_service_points`
--
ALTER TABLE `branch_service_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_service_points_branch_id_service_point_id_item_id_unique` (`branch_id`,`service_point_id`,`item_id`),
  ADD KEY `branch_service_points_business_id_foreign` (`business_id`),
  ADD KEY `branch_service_points_service_point_id_foreign` (`service_point_id`),
  ADD KEY `branch_service_points_item_id_foreign` (`item_id`);

--
-- Indexes for table `bulk_items`
--
ALTER TABLE `bulk_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bulk_items_uuid_unique` (`uuid`),
  ADD KEY `bulk_items_bulk_item_id_foreign` (`bulk_item_id`),
  ADD KEY `bulk_items_included_item_id_foreign` (`included_item_id`),
  ADD KEY `bulk_items_business_id_foreign` (`business_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `businesses_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `businesses_email_unique` (`email`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `clients_client_id_unique` (`client_id`),
  ADD KEY `clients_business_id_foreign` (`business_id`),
  ADD KEY `clients_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `contractor_profiles`
--
ALTER TABLE `contractor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contractor_profiles_uuid_unique` (`uuid`),
  ADD KEY `contractor_profiles_business_id_foreign` (`business_id`),
  ADD KEY `contractor_profiles_user_id_foreign` (`user_id`);

--
-- Indexes for table `contractor_service_charges`
--
ALTER TABLE `contractor_service_charges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contractor_service_charges_uuid_unique` (`uuid`),
  ADD KEY `contractor_service_charges_contractor_profile_id_index` (`contractor_profile_id`),
  ADD KEY `contractor_service_charges_business_id_index` (`business_id`),
  ADD KEY `contractor_service_charges_created_by_index` (`created_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_uuid_unique` (`uuid`),
  ADD KEY `departments_business_id_foreign` (`business_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groups_uuid_unique` (`uuid`),
  ADD KEY `groups_business_id_foreign` (`business_id`);

--
-- Indexes for table `insurance_companies`
--
ALTER TABLE `insurance_companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `insurance_companies_uuid_unique` (`uuid`),
  ADD KEY `insurance_companies_business_id_foreign` (`business_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_client_id_foreign` (`client_id`),
  ADD KEY `invoices_business_id_foreign` (`business_id`),
  ADD KEY `invoices_branch_id_foreign` (`branch_id`),
  ADD KEY `invoices_created_by_foreign` (`created_by`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `items_code_unique` (`code`),
  ADD KEY `items_business_id_foreign` (`business_id`);

--
-- Indexes for table `item_units`
--
ALTER TABLE `item_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_units_uuid_unique` (`uuid`),
  ADD KEY `item_units_business_id_foreign` (`business_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `money_accounts`
--
ALTER TABLE `money_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `money_accounts_uuid_unique` (`uuid`),
  ADD KEY `money_accounts_business_id_foreign` (`business_id`),
  ADD KEY `money_accounts_client_id_foreign` (`client_id`),
  ADD KEY `money_accounts_contractor_profile_id_foreign` (`contractor_profile_id`);

--
-- Indexes for table `money_transfers`
--
ALTER TABLE `money_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `money_transfers_business_id_foreign` (`business_id`),
  ADD KEY `money_transfers_from_account_id_foreign` (`from_account_id`),
  ADD KEY `money_transfers_to_account_id_foreign` (`to_account_id`);

--
-- Indexes for table `package_items`
--
ALTER TABLE `package_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `package_items_uuid_unique` (`uuid`),
  ADD KEY `package_items_package_item_id_foreign` (`package_item_id`),
  ADD KEY `package_items_included_item_id_foreign` (`included_item_id`),
  ADD KEY `package_items_business_id_foreign` (`business_id`);

--
-- Indexes for table `package_tracking`
--
ALTER TABLE `package_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `package_tracking_uuid_unique` (`uuid`),
  ADD KEY `package_tracking_business_id_foreign` (`business_id`),
  ADD KEY `package_tracking_client_id_foreign` (`client_id`),
  ADD KEY `package_tracking_invoice_id_foreign` (`invoice_id`),
  ADD KEY `package_tracking_package_item_id_foreign` (`package_item_id`),
  ADD KEY `package_tracking_included_item_id_foreign` (`included_item_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD KEY `password_reset_tokens_email_index` (`email`);

--
-- Indexes for table `patient_categories`
--
ALTER TABLE `patient_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_categories_uuid_unique` (`uuid`),
  ADD KEY `patient_categories_business_id_foreign` (`business_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qualifications_uuid_unique` (`uuid`),
  ADD KEY `qualifications_business_id_foreign` (`business_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_uuid_unique` (`uuid`),
  ADD KEY `roles_business_id_foreign` (`business_id`),
  ADD KEY `roles_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_uuid_unique` (`uuid`),
  ADD KEY `rooms_business_id_foreign` (`business_id`),
  ADD KEY `rooms_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sections_uuid_unique` (`uuid`),
  ADD KEY `sections_business_id_foreign` (`business_id`),
  ADD KEY `sections_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `service_charges`
--
ALTER TABLE `service_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_charges_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `service_charges_business_id_index` (`business_id`),
  ADD KEY `service_charges_created_by_index` (`created_by`);

--
-- Indexes for table `service_points`
--
ALTER TABLE `service_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_points_uuid_unique` (`uuid`),
  ADD KEY `service_points_business_id_foreign` (`business_id`),
  ADD KEY `service_points_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_uuid_unique` (`uuid`),
  ADD KEY `stores_business_id_foreign` (`business_id`),
  ADD KEY `stores_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sub_groups`
--
ALTER TABLE `sub_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sub_groups_uuid_unique` (`uuid`),
  ADD KEY `sub_groups_business_id_foreign` (`business_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `suppliers_uuid_unique` (`uuid`),
  ADD KEY `suppliers_business_id_foreign` (`business_id`);

--
-- Indexes for table `titles`
--
ALTER TABLE `titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `titles_business_id_foreign` (`business_id`),
  ADD KEY `titles_uuid_index` (`uuid`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_uuid_unique` (`uuid`),
  ADD KEY `1` (`business_id`),
  ADD KEY `transactions_branch_id_index` (`branch_id`),
  ADD KEY `transactions_reference_index` (`reference`),
  ADD KEY `transactions_status_index` (`status`),
  ADD KEY `transactions_type_index` (`type`),
  ADD KEY `transactions_origin_index` (`origin`),
  ADD KEY `transactions_provider_index` (`provider`),
  ADD KEY `transactions_date_index` (`date`),
  ADD KEY `transactions_transaction_for_index` (`transaction_for`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_business_id_foreign` (`business_id`),
  ADD KEY `users_branch_id_foreign` (`branch_id`),
  ADD KEY `users_uuid_index` (`uuid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `branch_item_prices`
--
ALTER TABLE `branch_item_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `branch_service_points`
--
ALTER TABLE `branch_service_points`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `bulk_items`
--
ALTER TABLE `bulk_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contractor_profiles`
--
ALTER TABLE `contractor_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contractor_service_charges`
--
ALTER TABLE `contractor_service_charges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `insurance_companies`
--
ALTER TABLE `insurance_companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `item_units`
--
ALTER TABLE `item_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `money_accounts`
--
ALTER TABLE `money_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `money_transfers`
--
ALTER TABLE `money_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `package_items`
--
ALTER TABLE `package_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `package_tracking`
--
ALTER TABLE `package_tracking`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_categories`
--
ALTER TABLE `patient_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qualifications`
--
ALTER TABLE `qualifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_charges`
--
ALTER TABLE `service_charges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_points`
--
ALTER TABLE `service_points`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sub_groups`
--
ALTER TABLE `sub_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `titles`
--
ALTER TABLE `titles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_item_prices`
--
ALTER TABLE `branch_item_prices`
  ADD CONSTRAINT `branch_item_prices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_item_prices_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_item_prices_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_service_points`
--
ALTER TABLE `branch_service_points`
  ADD CONSTRAINT `branch_service_points_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_service_points_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_service_points_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_service_points_service_point_id_foreign` FOREIGN KEY (`service_point_id`) REFERENCES `service_points` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bulk_items`
--
ALTER TABLE `bulk_items`
  ADD CONSTRAINT `bulk_items_bulk_item_id_foreign` FOREIGN KEY (`bulk_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_items_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_items_included_item_id_foreign` FOREIGN KEY (`included_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contractor_profiles`
--
ALTER TABLE `contractor_profiles`
  ADD CONSTRAINT `contractor_profiles_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contractor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contractor_service_charges`
--
ALTER TABLE `contractor_service_charges`
  ADD CONSTRAINT `contractor_service_charges_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contractor_service_charges_contractor_profile_id_foreign` FOREIGN KEY (`contractor_profile_id`) REFERENCES `contractor_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contractor_service_charges_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_companies`
--
ALTER TABLE `insurance_companies`
  ADD CONSTRAINT `insurance_companies_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item_units`
--
ALTER TABLE `item_units`
  ADD CONSTRAINT `item_units_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `money_accounts`
--
ALTER TABLE `money_accounts`
  ADD CONSTRAINT `money_accounts_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `money_accounts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `money_accounts_contractor_profile_id_foreign` FOREIGN KEY (`contractor_profile_id`) REFERENCES `contractor_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `money_transfers`
--
ALTER TABLE `money_transfers`
  ADD CONSTRAINT `money_transfers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `money_transfers_from_account_id_foreign` FOREIGN KEY (`from_account_id`) REFERENCES `money_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `money_transfers_to_account_id_foreign` FOREIGN KEY (`to_account_id`) REFERENCES `money_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_items`
--
ALTER TABLE `package_items`
  ADD CONSTRAINT `package_items_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_items_included_item_id_foreign` FOREIGN KEY (`included_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_items_package_item_id_foreign` FOREIGN KEY (`package_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_tracking`
--
ALTER TABLE `package_tracking`
  ADD CONSTRAINT `package_tracking_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_tracking_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_tracking_included_item_id_foreign` FOREIGN KEY (`included_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_tracking_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_tracking_package_item_id_foreign` FOREIGN KEY (`package_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_categories`
--
ALTER TABLE `patient_categories`
  ADD CONSTRAINT `patient_categories_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD CONSTRAINT `qualifications_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `roles_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rooms_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sections_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_charges`
--
ALTER TABLE `service_charges`
  ADD CONSTRAINT `service_charges_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_charges_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_points`
--
ALTER TABLE `service_points`
  ADD CONSTRAINT `service_points_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_points_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stores_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_groups`
--
ALTER TABLE `sub_groups`
  ADD CONSTRAINT `sub_groups_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `titles`
--
ALTER TABLE `titles`
  ADD CONSTRAINT `titles_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

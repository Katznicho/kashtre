-- Import staging data only (no table creation)
-- Run this script on the server to import existing data

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- Disable foreign key checks temporarily to avoid constraint issues during import
SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing data (optional - remove if you want to keep existing data)
-- Note: activity_logs are excluded as they generate themselves
-- TRUNCATE TABLE branches;
-- TRUNCATE TABLE branch_item_prices;
-- TRUNCATE TABLE branch_service_points;
-- TRUNCATE TABLE bulk_items;
-- TRUNCATE TABLE businesses;
-- TRUNCATE TABLE clients;
-- TRUNCATE TABLE contractor_profiles;
-- TRUNCATE TABLE contractor_service_charges;
-- TRUNCATE TABLE departments;
-- TRUNCATE TABLE failed_jobs;
-- TRUNCATE TABLE groups;
-- TRUNCATE TABLE insurance_companies;
-- TRUNCATE TABLE invoices;
-- TRUNCATE TABLE items;
-- TRUNCATE TABLE item_units;
-- TRUNCATE TABLE money_accounts;
-- TRUNCATE TABLE money_transfers;
-- TRUNCATE TABLE package_items;
-- TRUNCATE TABLE package_tracking;
-- TRUNCATE TABLE password_reset_tokens;
-- TRUNCATE TABLE patient_categories;
-- TRUNCATE TABLE personal_access_tokens;
-- TRUNCATE TABLE qualifications;
-- TRUNCATE TABLE roles;
-- TRUNCATE TABLE rooms;
-- TRUNCATE TABLE sections;
-- TRUNCATE TABLE service_charges;
-- TRUNCATE TABLE service_points;
-- TRUNCATE TABLE sessions;
-- TRUNCATE TABLE stores;
-- TRUNCATE TABLE sub_groups;
-- TRUNCATE TABLE suppliers;
-- TRUNCATE TABLE titles;
-- TRUNCATE TABLE transactions;
-- TRUNCATE TABLE users;

INSERT INTO `branches` (`id`, `uuid`, `business_id`, `name`, `email`, `phone`, `address`, `created_at`, `updated_at`) VALUES
INSERT INTO `branch_item_prices` (`id`, `uuid`, `business_id`, `branch_id`, `item_id`, `price`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `branch_service_points` (`id`, `business_id`, `branch_id`, `service_point_id`, `item_id`, `created_at`, `updated_at`) VALUES
INSERT INTO `bulk_items` (`id`, `uuid`, `bulk_item_id`, `included_item_id`, `fixed_quantity`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `businesses` (`id`, `uuid`, `name`, `email`, `phone`, `address`, `logo`, `date`, `account_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `clients` (`id`, `uuid`, `business_id`, `branch_id`, `type`, `client_id`, `visit_id`, `nin`, `tin_number`, `surname`, `first_name`, `other_names`, `name`, `age`, `sex`, `marital_status`, `occupation`, `phone_number`, `date_of_birth`, `payment_phone_number`, `village`, `county`, `services_category`, `balance`, `status`, `email`, `next_of_kin`, `preferred_payment_method`, `payment_methods`, `nok_surname`, `nok_first_name`, `nok_other_names`, `nok_sex`, `nok_marital_status`, `nok_occupation`, `nok_phone_number`, `nok_village`, `nok_county`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `contractor_profiles` (`id`, `business_id`, `uuid`, `bank_name`, `account_name`, `account_number`, `account_balance`, `kashtre_account_number`, `signing_qualifications`, `deleted_at`, `created_at`, `updated_at`, `user_id`) VALUES
INSERT INTO `contractor_service_charges` (`id`, `uuid`, `contractor_profile_id`, `amount`, `upper_bound`, `lower_bound`, `type`, `description`, `is_active`, `business_id`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `departments` (`id`, `uuid`, `business_id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `groups` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `business_id`, `branch_id`, `created_by`, `client_name`, `client_phone`, `payment_phone`, `visit_id`, `items`, `subtotal`, `package_adjustment`, `account_balance_adjustment`, `service_charge`, `total_amount`, `amount_paid`, `balance_due`, `payment_methods`, `payment_status`, `notes`, `status`, `confirmed_at`, `printed_at`, `created_at`, `updated_at`) VALUES
INSERT INTO `items` (`id`, `uuid`, `name`, `code`, `type`, `description`, `group_id`, `subgroup_id`, `department_id`, `uom_id`, `service_point_id`, `default_price`, `validity_days`, `hospital_share`, `contractor_account_id`, `business_id`, `created_at`, `updated_at`, `deleted_at`, `other_names`) VALUES
INSERT INTO `item_units` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
INSERT INTO `money_accounts` (`id`, `uuid`, `name`, `type`, `business_id`, `client_id`, `contractor_profile_id`, `balance`, `currency`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `money_transfers` (`id`, `uuid`, `business_id`, `from_account_id`, `to_account_id`, `amount`, `currency`, `status`, `transfer_type`, `invoice_id`, `client_id`, `item_id`, `package_usage_id`, `reference`, `description`, `metadata`, `processed_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `package_items` (`id`, `uuid`, `package_item_id`, `included_item_id`, `max_quantity`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
INSERT INTO `patient_categories` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `qualifications` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `rooms` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `sections` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `service_charges` (`id`, `entity_type`, `entity_id`, `amount`, `upper_bound`, `lower_bound`, `type`, `description`, `is_active`, `business_id`, `created_by`, `created_at`, `updated_at`) VALUES
INSERT INTO `service_points` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
INSERT INTO `stores` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `sub_groups` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `suppliers` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `titles` (`id`, `uuid`, `business_id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `transactions` (`id`, `uuid`, `business_id`, `branch_id`, `amount`, `reference`, `description`, `status`, `type`, `origin`, `phone_number`, `provider`, `service`, `date`, `currency`, `names`, `email`, `ip_address`, `user_agent`, `method`, `transaction_for`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `users` (`id`, `uuid`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `phone`, `nin`, `remember_token`, `profile_photo_path`, `status`, `business_id`, `branch_id`, `service_points`, `permissions`, `allowed_branches`, `qualification_id`, `department_id`, `section_id`, `title_id`, `gender`, `created_at`, `updated_at`, `deleted_at`) VALUES

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- Import completed successfully!


-- Dumping data for table `activity_logs`
INSERT INTO `activity_logs` (`id`, `uuid`, `user_id`, `business_id`, `branch_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `action_type`, `description`, `date`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `activity_logs` (`id`, `uuid`, `user_id`, `business_id`, `branch_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `action_type`, `description`, `date`, `created_at`, `updated_at`, `deleted_at`) VALUES
INSERT INTO `activity_logs` (`id`, `uuid`, `user_id`, `business_id`, `branch_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`, `action_type`, `description`, `date`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `branches`
INSERT INTO `branches` (`id`, `uuid`, `business_id`, `name`, `email`, `phone`, `address`, `created_at`, `updated_at`) VALUES
-- Dumping data for table `branch_item_prices`
INSERT INTO `branch_item_prices` (`id`, `uuid`, `business_id`, `branch_id`, `item_id`, `price`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `branch_service_points`
INSERT INTO `branch_service_points` (`id`, `business_id`, `branch_id`, `service_point_id`, `item_id`, `created_at`, `updated_at`) VALUES
-- Dumping data for table `bulk_items`
INSERT INTO `bulk_items` (`id`, `uuid`, `bulk_item_id`, `included_item_id`, `fixed_quantity`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `businesses`
INSERT INTO `businesses` (`id`, `uuid`, `name`, `email`, `phone`, `address`, `logo`, `date`, `account_number`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `clients`
INSERT INTO `clients` (`id`, `uuid`, `business_id`, `branch_id`, `type`, `client_id`, `visit_id`, `nin`, `tin_number`, `surname`, `first_name`, `other_names`, `name`, `age`, `sex`, `marital_status`, `occupation`, `phone_number`, `date_of_birth`, `payment_phone_number`, `village`, `county`, `services_category`, `balance`, `status`, `email`, `next_of_kin`, `preferred_payment_method`, `payment_methods`, `nok_surname`, `nok_first_name`, `nok_other_names`, `nok_sex`, `nok_marital_status`, `nok_occupation`, `nok_phone_number`, `nok_village`, `nok_county`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `contractor_profiles`
INSERT INTO `contractor_profiles` (`id`, `business_id`, `uuid`, `bank_name`, `account_name`, `account_number`, `account_balance`, `kashtre_account_number`, `signing_qualifications`, `deleted_at`, `created_at`, `updated_at`, `user_id`) VALUES
-- Dumping data for table `contractor_service_charges`
INSERT INTO `contractor_service_charges` (`id`, `uuid`, `contractor_profile_id`, `amount`, `upper_bound`, `lower_bound`, `type`, `description`, `is_active`, `business_id`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `departments`
INSERT INTO `departments` (`id`, `uuid`, `business_id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `groups`
INSERT INTO `groups` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `invoices`
INSERT INTO `invoices` (`id`, `invoice_number`, `client_id`, `business_id`, `branch_id`, `created_by`, `client_name`, `client_phone`, `payment_phone`, `visit_id`, `items`, `subtotal`, `package_adjustment`, `account_balance_adjustment`, `service_charge`, `total_amount`, `amount_paid`, `balance_due`, `payment_methods`, `payment_status`, `notes`, `status`, `confirmed_at`, `printed_at`, `created_at`, `updated_at`) VALUES
-- Dumping data for table `items`
INSERT INTO `items` (`id`, `uuid`, `name`, `code`, `type`, `description`, `group_id`, `subgroup_id`, `department_id`, `uom_id`, `service_point_id`, `default_price`, `validity_days`, `hospital_share`, `contractor_account_id`, `business_id`, `created_at`, `updated_at`, `deleted_at`, `other_names`) VALUES
-- Dumping data for table `item_units`
INSERT INTO `item_units` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `migrations`
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
-- Dumping data for table `money_accounts`
INSERT INTO `money_accounts` (`id`, `uuid`, `name`, `type`, `business_id`, `client_id`, `contractor_profile_id`, `balance`, `currency`, `description`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `money_transfers`
INSERT INTO `money_transfers` (`id`, `uuid`, `business_id`, `from_account_id`, `to_account_id`, `amount`, `currency`, `status`, `transfer_type`, `invoice_id`, `client_id`, `item_id`, `package_usage_id`, `reference`, `description`, `metadata`, `processed_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `package_items`
INSERT INTO `package_items` (`id`, `uuid`, `package_item_id`, `included_item_id`, `max_quantity`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `password_reset_tokens`
INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
-- Dumping data for table `patient_categories`
INSERT INTO `patient_categories` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `qualifications`
INSERT INTO `qualifications` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `rooms`
INSERT INTO `rooms` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `sections`
INSERT INTO `sections` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `service_charges`
INSERT INTO `service_charges` (`id`, `entity_type`, `entity_id`, `amount`, `upper_bound`, `lower_bound`, `type`, `description`, `is_active`, `business_id`, `created_by`, `created_at`, `updated_at`) VALUES
-- Dumping data for table `service_points`
INSERT INTO `service_points` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `sessions`
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
-- Dumping data for table `stores`
INSERT INTO `stores` (`id`, `uuid`, `name`, `description`, `business_id`, `branch_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `sub_groups`
INSERT INTO `sub_groups` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `suppliers`
INSERT INTO `suppliers` (`id`, `uuid`, `name`, `description`, `business_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `titles`
INSERT INTO `titles` (`id`, `uuid`, `business_id`, `name`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `transactions`
INSERT INTO `transactions` (`id`, `uuid`, `business_id`, `branch_id`, `amount`, `reference`, `description`, `status`, `type`, `origin`, `phone_number`, `provider`, `service`, `date`, `currency`, `names`, `email`, `ip_address`, `user_agent`, `method`, `transaction_for`, `created_at`, `updated_at`, `deleted_at`) VALUES
-- Dumping data for table `users`
INSERT INTO `users` (`id`, `uuid`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `phone`, `nin`, `remember_token`, `profile_photo_path`, `status`, `business_id`, `branch_id`, `service_points`, `permissions`, `allowed_branches`, `qualification_id`, `department_id`, `section_id`, `title_id`, `gender`, `created_at`, `updated_at`, `deleted_at`) VALUES

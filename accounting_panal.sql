-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 16, 2026 at 09:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `accounting_panal`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_transactions`
--

CREATE TABLE `accounting_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `occurred_at` datetime NOT NULL,
  `source_type` enum('company','personal') NOT NULL DEFAULT 'personal',
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `issuer_name` varchar(255) NOT NULL,
  `issuer_email` varchar(255) DEFAULT NULL,
  `issuer_address` text DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `tax_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_transaction_attachments`
--

CREATE TABLE `accounting_transaction_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `accounting_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'public',
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_transaction_items`
--

CREATE TABLE `accounting_transaction_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `accounting_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` enum('product','service','label','other') NOT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `label_id` bigint(20) UNSIGNED DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 1.000,
  `unit_price` decimal(14,2) NOT NULL,
  `tax_rate` decimal(7,3) NOT NULL DEFAULT 0.000,
  `subtotal` decimal(14,2) NOT NULL,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `base_country` varchar(255) DEFAULT NULL,
  `allow_super_admin_permanent_delete` tinyint(1) NOT NULL DEFAULT 0,
  `pdf_sponsor_image` varchar(255) DEFAULT NULL,
  `mail_host` varchar(255) DEFAULT NULL,
  `mail_port` smallint(5) UNSIGNED DEFAULT NULL,
  `mail_scheme` enum('smtp','smtps') DEFAULT NULL,
  `mail_username` varchar(255) DEFAULT NULL,
  `mail_password` text DEFAULT NULL,
  `mail_from_address` varchar(255) DEFAULT NULL,
  `mail_from_name` varchar(255) DEFAULT NULL,
  `mail_cc` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `certificate_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `issued_at` date NOT NULL,
  `expires_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `billing_address` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labels`
--

CREATE TABLE `labels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_otps`
--

CREATE TABLE `login_otps` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '2026_05_29_000001_create_roles_table', 1),
(2, '2026_05_29_000002_create_permissions_table', 1),
(3, '2026_05_29_000003_create_role_permissions_table', 1),
(4, '2026_05_29_000004_create_users_table', 1),
(5, '2026_05_29_000005_create_password_resets_table', 1),
(6, '2026_05_29_000006_create_sessions_table', 1),
(7, '2026_05_29_000007_create_cache_table', 1),
(8, '2026_05_29_000008_create_jobs_table', 1),
(9, '2026_06_08_000001_add_soft_deletes_to_users_table', 1),
(10, '2026_08_15_000001_create_accounting_master_tables', 1),
(11, '2026_08_15_000002_create_accounting_transaction_tables', 1),
(12, '2026_08_16_000001_create_login_otps_table', 1),
(13, '2026_08_16_000002_create_app_settings_table', 1),
(14, '2026_08_16_000003_harden_users_and_transaction_items', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', 'View Dashboard', 'dashboard-view-dashboard', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(2, 'Admin Auth', 'Login Admin', 'admin-auth-login-admin', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(3, 'Admin Auth', 'Logout Admin', 'admin-auth-logout-admin', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(4, 'Admin Auth', 'Reset Admin Password', 'admin-auth-reset-admin-password', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(5, 'Profile', 'Manage Profile', 'profile-manage-profile', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(6, 'Profile', 'View Profile', 'profile-view-profile', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(7, 'Profile', 'Edit Profile', 'profile-edit-profile', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(8, 'Profile', 'Change Profile Password', 'profile-change-profile-password', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(9, 'Settings', 'Manage Settings', 'settings-manage-settings', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(10, 'Settings', 'View Settings', 'settings-view-settings', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(11, 'Settings', 'Update Settings', 'settings-update-settings', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(12, 'Master Control', 'Manage Master Control', 'master-control-manage-master-control', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(13, 'Master Control', 'View Master Control', 'master-control-view-master-control', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(14, 'Users', 'Manage Users', 'users-manage-users', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(15, 'Users', 'View Users', 'users-view-users', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(16, 'Users', 'Create Users', 'users-create-users', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(17, 'Users', 'Edit Users', 'users-edit-users', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(18, 'Users', 'Delete Users', 'users-delete-users', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(19, 'Roles', 'Manage Roles', 'roles-manage-roles', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(20, 'Roles', 'View Roles', 'roles-view-roles', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(21, 'Roles', 'Create Roles', 'roles-create-roles', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(22, 'Roles', 'Edit Roles', 'roles-edit-roles', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(23, 'Roles', 'Delete Roles', 'roles-delete-roles', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(24, 'Permissions', 'Manage Permissions', 'permissions-manage-permissions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(25, 'Permissions', 'View Permissions', 'permissions-view-permissions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(26, 'Permissions', 'Create Permissions', 'permissions-create-permissions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(27, 'Permissions', 'Edit Permissions', 'permissions-edit-permissions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(28, 'Permissions', 'Delete Permissions', 'permissions-delete-permissions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(29, 'Companies', 'Manage Companies', 'companies-manage-companies', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(30, 'Companies', 'View Companies', 'companies-view-companies', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(31, 'Companies', 'Create Companies', 'companies-create-companies', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(32, 'Companies', 'Edit Companies', 'companies-edit-companies', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(33, 'Companies', 'Delete Companies', 'companies-delete-companies', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(34, 'Customers', 'Manage Customers', 'customers-manage-customers', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(35, 'Customers', 'View Customers', 'customers-view-customers', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(36, 'Customers', 'Create Customers', 'customers-create-customers', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(37, 'Customers', 'Edit Customers', 'customers-edit-customers', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(38, 'Customers', 'Delete Customers', 'customers-delete-customers', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(39, 'Services', 'Manage Services', 'services-manage-services', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(40, 'Services', 'View Services', 'services-view-services', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(41, 'Services', 'Create Services', 'services-create-services', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(42, 'Services', 'Edit Services', 'services-edit-services', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(43, 'Services', 'Delete Services', 'services-delete-services', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(44, 'Products', 'Manage Products', 'products-manage-products', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(45, 'Products', 'View Products', 'products-view-products', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(46, 'Products', 'Create Products', 'products-create-products', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(47, 'Products', 'Edit Products', 'products-edit-products', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(48, 'Products', 'Delete Products', 'products-delete-products', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(49, 'Tax Classes', 'Manage Tax Classes', 'tax-classes-manage-tax-classes', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(50, 'Tax Classes', 'View Tax Classes', 'tax-classes-view-tax-classes', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(51, 'Tax Classes', 'Create Tax Classes', 'tax-classes-create-tax-classes', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(52, 'Tax Classes', 'Edit Tax Classes', 'tax-classes-edit-tax-classes', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(53, 'Tax Classes', 'Delete Tax Classes', 'tax-classes-delete-tax-classes', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(54, 'Labels', 'Manage Labels', 'labels-manage-labels', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(55, 'Labels', 'View Labels', 'labels-view-labels', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(56, 'Labels', 'Create Labels', 'labels-create-labels', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(57, 'Labels', 'Edit Labels', 'labels-edit-labels', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(58, 'Labels', 'Delete Labels', 'labels-delete-labels', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(59, 'Certificates', 'Manage Certificates', 'certificates-manage-certificates', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(60, 'Certificates', 'View Certificates', 'certificates-view-certificates', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(61, 'Certificates', 'Create Certificates', 'certificates-create-certificates', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(62, 'Certificates', 'Delete Certificates', 'certificates-delete-certificates', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(63, 'Certificates', 'Download Certificates', 'certificates-download-certificates', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(64, 'Transactions', 'Manage Transactions', 'transactions-manage-transactions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(65, 'Transactions', 'View Transactions', 'transactions-view-transactions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(66, 'Transactions', 'Create Income', 'transactions-create-income', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(67, 'Transactions', 'Create Expense', 'transactions-create-expense', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(68, 'Transactions', 'Edit Transactions', 'transactions-edit-transactions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(69, 'Transactions', 'Delete Transactions', 'transactions-delete-transactions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(70, 'Transactions', 'Export Transactions', 'transactions-export-transactions', NULL, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(71, 'Transactions', 'Bulk Delete Transactions', 'transactions-bulk-delete-transactions', NULL, '2026-08-17 01:14:04', '2026-08-17 01:14:04'),
(72, 'Transactions', 'Send Invoice Email', 'transactions-send-invoice-email', NULL, '2026-08-17 01:14:04', '2026-08-17 01:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `tax_class_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'Admin role with standard seeded permissions.', '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(2, 'super admin', 'super-admin', 'Super admin role with standard seeded permissions.', '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(3, 'developer admin', 'developer-admin', 'Developer admin role with all seeded permissions.', '2026-08-17 00:53:38', '2026-08-17 00:53:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(2, 1, 3, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(3, 1, 4, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(4, 1, 61, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(5, 1, 62, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(6, 1, 63, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(7, 1, 59, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(8, 1, 60, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(9, 1, 31, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(10, 1, 33, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(11, 1, 32, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(12, 1, 29, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(13, 1, 30, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(14, 1, 36, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(15, 1, 38, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(16, 1, 37, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(17, 1, 34, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(18, 1, 35, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(19, 1, 1, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(20, 1, 56, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(21, 1, 58, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(22, 1, 57, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(23, 1, 54, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(24, 1, 55, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(25, 1, 46, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(26, 1, 48, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(27, 1, 47, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(28, 1, 44, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(29, 1, 45, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(30, 1, 8, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(31, 1, 7, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(32, 1, 5, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(33, 1, 6, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(34, 1, 41, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(35, 1, 43, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(36, 1, 42, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(37, 1, 39, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(38, 1, 40, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(39, 1, 9, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(40, 1, 11, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(41, 1, 10, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(42, 1, 51, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(43, 1, 53, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(44, 1, 52, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(45, 1, 49, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(46, 1, 50, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(47, 1, 67, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(48, 1, 66, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(49, 1, 69, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(50, 1, 68, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(51, 1, 70, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(52, 1, 64, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(53, 1, 65, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(54, 2, 2, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(55, 2, 3, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(56, 2, 4, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(57, 2, 61, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(58, 2, 62, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(59, 2, 63, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(60, 2, 59, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(61, 2, 60, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(62, 2, 31, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(63, 2, 33, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(64, 2, 32, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(65, 2, 29, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(66, 2, 30, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(67, 2, 36, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(68, 2, 38, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(69, 2, 37, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(70, 2, 34, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(71, 2, 35, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(72, 2, 1, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(73, 2, 56, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(74, 2, 58, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(75, 2, 57, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(76, 2, 54, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(77, 2, 55, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(78, 2, 46, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(79, 2, 48, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(80, 2, 47, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(81, 2, 44, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(82, 2, 45, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(83, 2, 8, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(84, 2, 7, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(85, 2, 5, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(86, 2, 6, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(87, 2, 41, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(88, 2, 43, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(89, 2, 42, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(90, 2, 39, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(91, 2, 40, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(92, 2, 9, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(93, 2, 11, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(94, 2, 10, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(95, 2, 51, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(96, 2, 53, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(97, 2, 52, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(98, 2, 49, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(99, 2, 50, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(100, 2, 67, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(101, 2, 66, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(102, 2, 69, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(103, 2, 68, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(104, 2, 70, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(105, 2, 64, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(106, 2, 65, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(107, 3, 2, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(108, 3, 3, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(109, 3, 4, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(110, 3, 61, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(111, 3, 62, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(112, 3, 63, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(113, 3, 59, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(114, 3, 60, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(115, 3, 31, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(116, 3, 33, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(117, 3, 32, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(118, 3, 29, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(119, 3, 30, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(120, 3, 36, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(121, 3, 38, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(122, 3, 37, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(123, 3, 34, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(124, 3, 35, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(125, 3, 1, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(126, 3, 56, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(127, 3, 58, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(128, 3, 57, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(129, 3, 54, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(130, 3, 55, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(131, 3, 12, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(132, 3, 13, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(133, 3, 26, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(134, 3, 28, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(135, 3, 27, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(136, 3, 24, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(137, 3, 25, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(138, 3, 46, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(139, 3, 48, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(140, 3, 47, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(141, 3, 44, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(142, 3, 45, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(143, 3, 8, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(144, 3, 7, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(145, 3, 5, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(146, 3, 6, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(147, 3, 21, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(148, 3, 23, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(149, 3, 22, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(150, 3, 19, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(151, 3, 20, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(152, 3, 41, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(153, 3, 43, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(154, 3, 42, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(155, 3, 39, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(156, 3, 40, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(157, 3, 9, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(158, 3, 11, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(159, 3, 10, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(160, 3, 51, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(161, 3, 53, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(162, 3, 52, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(163, 3, 49, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(164, 3, 50, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(165, 3, 67, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(166, 3, 66, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(167, 3, 69, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(168, 3, 68, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(169, 3, 70, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(170, 3, 64, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(171, 3, 65, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(172, 3, 16, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(173, 3, 18, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(174, 3, 17, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(175, 3, 14, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(176, 3, 15, '2026-08-17 00:53:38', '2026-08-17 00:53:38'),
(177, 1, 71, '2026-08-17 01:14:04', '2026-08-17 01:14:04'),
(178, 1, 72, '2026-08-17 01:14:04', '2026-08-17 01:14:04'),
(179, 2, 71, '2026-08-17 01:14:04', '2026-08-17 01:14:04'),
(180, 2, 72, '2026-08-17 01:14:04', '2026-08-17 01:14:04'),
(181, 3, 71, '2026-08-17 01:14:04', '2026-08-17 01:14:04'),
(182, 3, 72, '2026-08-17 01:14:04', '2026-08-17 01:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `default_rate` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('45958yZTx22X8KcSsLjo7wdXX1C67jSMuqyGqQWm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.19045; en-US) PowerShell/7.6.4', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSHhUS2Z1aTUzVXlpWktpMDdQbUlNVlF6ZDhHSWtxZFNMRGtzZDk4OSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1786904092),
('aNQqsSzq3F0f8L9vzAVYMimwpayAHvPFx61Oye4s', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.19045; en-US) PowerShell/7.6.4', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidDBxNDl4OEhhREhoNEgySjh1OW16UUI3Nzl1eEpRUnQzUWxRcXl6TyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1786903178),
('mV7tHvi39R5NnbyfAHsQZ3jLRLGI3jgGDMeLr3YA', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUDM3QmpLUXlhcVlKWTh5UXRrVXc5UkhpYzNLa2s3ME1kckF3aTlUaCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9tYXN0ZXItY29udHJvbC91c2VycyI7czo1OiJyb3V0ZSI7czoxNzoiYWRtaW4udXNlcnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1786907108),
('w6LVq6ZuXXOshZeMY5RYoQDfJqzoWdvPJDGzSLjf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUWlwZkdPdDIzNmRjb0ZlenVPN1RxOWpyRk8xNUdndmVUcGpOZkZvaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1786905860);

-- --------------------------------------------------------

--
-- Table structure for table `tax_classes`
--

CREATE TABLE `tax_classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `percentage` decimal(7,3) NOT NULL DEFAULT 0.000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `mobile_number_prefix` varchar(10) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `role`, `profile_picture`, `mobile_number_prefix`, `mobile_number`, `address`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 'admin', 'saurabhwork9784@gmail.com', '2026-08-17 00:53:39', '$2y$12$Kn0uJWAM90fijBhxnm7eY.qNEEZ0z.XPND9XPGY3pQgedTiKmnmeq', 3, NULL, '+91', '5244524525', NULL, NULL, '2026-08-17 00:53:39', '2026-08-17 00:53:39', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounting_transactions_reference_number_unique` (`reference_number`),
  ADD KEY `accounting_transactions_customer_id_foreign` (`customer_id`),
  ADD KEY `accounting_transactions_company_id_foreign` (`company_id`),
  ADD KEY `accounting_transactions_created_by_foreign` (`created_by`),
  ADD KEY `accounting_transactions_type_occurred_at_index` (`type`,`occurred_at`),
  ADD KEY `accounting_transactions_source_type_company_id_index` (`source_type`,`company_id`);

--
-- Indexes for table `accounting_transaction_attachments`
--
ALTER TABLE `accounting_transaction_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acct_txn_files_transaction_fk` (`accounting_transaction_id`);

--
-- Indexes for table `accounting_transaction_items`
--
ALTER TABLE `accounting_transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acct_txn_items_transaction_fk` (`accounting_transaction_id`),
  ADD KEY `accounting_transaction_items_label_index` (`label`),
  ADD KEY `acct_txn_items_source_idx` (`item_type`,`source_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificates_certificate_number_unique` (`certificate_number`),
  ADD KEY `certificates_customer_id_foreign` (`customer_id`),
  ADD KEY `certificates_company_id_foreign` (`company_id`),
  ADD KEY `certificates_expires_at_issued_at_index` (`expires_at`,`issued_at`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `companies_name_unique` (`name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_company_id_name_index` (`company_id`,`name`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `labels_name_unique` (`name`);

--
-- Indexes for table `login_otps`
--
ALTER TABLE `login_otps`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`),
  ADD KEY `permissions_module_index` (`module`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_company_id_name_unique` (`company_id`,`name`),
  ADD KEY `products_tax_class_id_foreign` (`tax_class_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_company_id_name_unique` (`company_id`,`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tax_classes`
--
ALTER TABLE `tax_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tax_classes_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_foreign` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `accounting_transaction_attachments`
--
ALTER TABLE `accounting_transaction_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accounting_transaction_items`
--
ALTER TABLE `accounting_transaction_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `labels`
--
ALTER TABLE `labels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=183;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_classes`
--
ALTER TABLE `tax_classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  ADD CONSTRAINT `accounting_transactions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `accounting_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `accounting_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `accounting_transaction_attachments`
--
ALTER TABLE `accounting_transaction_attachments`
  ADD CONSTRAINT `acct_txn_files_transaction_fk` FOREIGN KEY (`accounting_transaction_id`) REFERENCES `accounting_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `accounting_transaction_items`
--
ALTER TABLE `accounting_transaction_items`
  ADD CONSTRAINT `acct_txn_items_transaction_fk` FOREIGN KEY (`accounting_transaction_id`) REFERENCES `accounting_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `certificates_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `products_tax_class_id_foreign` FOREIGN KEY (`tax_class_id`) REFERENCES `tax_classes` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_foreign` FOREIGN KEY (`role`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

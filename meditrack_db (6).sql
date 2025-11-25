-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 22, 2025 at 01:38 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `meditrack_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `notes` text,
  `amount` decimal(12,2) DEFAULT NULL,
  `currency` varchar(8) DEFAULT 'PHP',
  `invoice_id` varchar(64) DEFAULT NULL,
  `invoice_url` varchar(255) DEFAULT NULL,
  `checkout_url` varchar(255) DEFAULT NULL,
  `payment_status` varchar(32) DEFAULT 'pending',
  `xendit_status` varchar(32) DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_id` int DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'scheduled',
  `cancellation_reason` text,
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_by` varchar(150) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_patient` (`patient_id`),
  KEY `fk_doctor_appointment` (`doctor_id`),
  KEY `fk_slot` (`slot_id`),
  KEY `idx_payment_id` (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `slot_id`, `notes`, `amount`, `currency`, `invoice_id`, `invoice_url`, `checkout_url`, `payment_status`, `xendit_status`, `payment_due_at`, `paid_at`, `payment_id`, `consultation_fee`, `status`, `cancellation_reason`, `cancelled_at`, `cancelled_by`, `completed_at`) VALUES
(90, 4, 7, 180, 'ok', 1500.00, 'PHP', '69196dca26d6bc37164d7ca6', 'https://checkout-staging.xendit.co/web/69196dca26d6bc37164d7ca6', 'https://checkout-staging.xendit.co/web/69196dca26d6bc37164d7ca6', 'cancelled', 'CANCELLED', '2025-11-16 16:23:07', NULL, 20, 1500.00, 'cancelled', 'sorry', '2025-11-16 14:23:17', 'Administrator', NULL),
(91, 4, 7, 181, 'ok', 1500.00, 'PHP', '69196e18fbcbf149203e69d6', 'https://checkout-staging.xendit.co/web/69196e18fbcbf149203e69d6', 'https://checkout-staging.xendit.co/web/69196e18fbcbf149203e69d6', 'paid', 'PAID', '2025-11-16 16:24:24', '2025-11-16 14:24:42', 21, 1500.00, 'completed', NULL, NULL, NULL, '2025-11-19 13:15:34'),
(92, 4, 7, 182, 'ok', 1500.00, 'PHP', '69196e7926d6bc37164d7d2d', 'https://checkout-staging.xendit.co/web/69196e7926d6bc37164d7d2d', 'https://checkout-staging.xendit.co/web/69196e7926d6bc37164d7d2d', 'paid', 'MANUAL_OVERRIDE', '2025-11-16 16:26:02', '2025-11-16 14:26:25', 22, 1500.00, 'completed', NULL, NULL, NULL, '2025-11-16 14:26:25'),
(93, 4, 7, 183, 'ok', 1500.00, 'PHP', '69196f0826d6bc37164d7d8e', 'https://checkout-staging.xendit.co/web/69196f0826d6bc37164d7d8e', 'https://checkout-staging.xendit.co/web/69196f0826d6bc37164d7d8e', 'paid', 'PAID', '2025-11-16 16:28:25', '2025-11-16 14:28:35', 23, 1500.00, 'completed', NULL, NULL, NULL, '2025-11-20 12:46:09'),
(96, 4, 7, 184, 'ok', 500.00, 'PHP', '691d549326d6bc371653153c', 'https://checkout-staging.xendit.co/web/691d549326d6bc371653153c', 'https://checkout-staging.xendit.co/web/691d549326d6bc371653153c', 'paid', 'PAID', '2025-11-19 15:24:36', '2025-11-19 13:25:12', 24, 500.00, 'completed', NULL, NULL, NULL, '2025-11-21 16:35:26');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
CREATE TABLE IF NOT EXISTS `doctors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `name`, `specialty`, `contact`) VALUES
(7, 12, 'jv', 'Brain', '09123456789'),
(9, 4, 'Vincent', 'Bone', '09123456789');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_slots`
--

DROP TABLE IF EXISTS `doctor_slots`;
CREATE TABLE IF NOT EXISTS `doctor_slots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `doctor_id` int NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_booked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slot` (`doctor_id`,`date`,`start_time`,`end_time`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor_slots`
--

INSERT INTO `doctor_slots` (`id`, `doctor_id`, `date`, `start_time`, `end_time`, `is_booked`) VALUES
(180, 7, '2025-11-17', '15:22:00', '16:22:00', 0),
(181, 7, '2025-11-17', '17:24:00', '18:24:00', 1),
(182, 7, '2025-11-18', '15:25:00', '16:25:00', 1),
(183, 7, '2025-11-19', '14:28:00', '15:28:00', 1),
(184, 7, '2025-11-20', '13:16:00', '14:16:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `age`, `gender`, `contact`) VALUES
(4, 'John', 12, 'Male', '09123456789'),
(5, 'Joe', 30, 'Male', '0973487321111');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `external_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PHP',
  `status` enum('pending','paid','expired','failed','cancelled','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payer_email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_channel` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `raw_payload` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_external_id` (`external_id`),
  UNIQUE KEY `uniq_appointment_payment` (`appointment_id`),
  UNIQUE KEY `uniq_invoice_id` (`invoice_id`),
  KEY `idx_payment_status` (`status`),
  KEY `idx_payment_patient` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `appointment_id`, `patient_id`, `doctor_id`, `external_id`, `invoice_id`, `invoice_url`, `amount`, `currency`, `status`, `payer_email`, `payer_name`, `payment_channel`, `failure_reason`, `paid_at`, `expires_at`, `raw_payload`, `created_at`, `updated_at`) VALUES
(20, 90, 4, 7, 'MTK-90-82B855', '69196dca26d6bc37164d7ca6', 'https://checkout-staging.xendit.co/web/69196dca26d6bc37164d7ca6', 1500.00, 'PHP', 'cancelled', NULL, 'John', NULL, 'Appointment cancelled by Administrator (invoice void failed: Unable to expire invoice: {\"error_code\":\"NOT_FOUND\",\"message\":\"The requested resource was not found\"})', NULL, '2025-11-16 16:23:07', '{\"id\": \"69196dca26d6bc37164d7ca6\", \"amount\": 1500, \"status\": \"PENDING\", \"created\": \"2025-11-16T06:23:07.153Z\", \"updated\": \"2025-11-16T06:23:07.153Z\", \"user_id\": \"69180880ac518b2becbc3e40\", \"currency\": \"PHP\", \"customer\": {\"given_names\": \"John\", \"mobile_number\": \"+639123456789\"}, \"metadata\": {\"notes\": \"ok\", \"slot_id\": 180, \"doctor_id\": 7, \"patient_id\": 4, \"external_id\": \"MTK-90-82B855\", \"appointment_id\": 90}, \"description\": \"Consultation with Dr. jv on Nov 17, 2025 15:22-16:22\", \"expiry_date\": \"2025-11-16T08:23:07.083Z\", \"external_id\": \"MTK-90-82B855\", \"invoice_url\": \"https://checkout-staging.xendit.co/web/69196dca26d6bc37164d7ca6\", \"merchant_name\": \"Payment\", \"reminder_date\": \"2025-11-16T07:23:07.083Z\", \"available_banks\": [], \"should_send_email\": false, \"available_ewallets\": [{\"ewallet_type\": \"SHOPEEPAY\"}, {\"ewallet_type\": \"GCASH\"}, {\"ewallet_type\": \"GRABPAY\"}, {\"ewallet_type\": \"PAYMAYA\"}], \"available_qr_codes\": [{\"qr_code_type\": \"QRPH\"}], \"available_paylaters\": [{\"paylater_type\": \"BILLEASE\"}, {\"paylater_type\": \"CASHALO\"}], \"failure_redirect_url\": \"http://localhost:3000/appointments/payment-failed?external_id=MTK-90-82B855\", \"success_redirect_url\": \"http://localhost:3000/appointments/payment-success?external_id=MTK-90-82B855\", \"available_direct_debits\": [{\"direct_debit_type\": \"DD_RCBC\"}, {\"direct_debit_type\": \"DD_CHINABANK\"}, {\"direct_debit_type\": \"DD_UBP\"}, {\"direct_debit_type\": \"DD_BPI\"}, {\"direct_debit_type\": \"DD_BDO_EPAY\"}, {\"direct_debit_type\": \"DD_BDO_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BPI_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_UNIONBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BOC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_CHINABANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_INSTAPAY_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_LANDBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_MAYBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_METROBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PNB_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PSBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PESONET_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_RCBC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_ROBINSONS_BANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_SECURITY_BANK_ONLINE_BANKING\"}], \"available_retail_outlets\": [{\"retail_outlet_name\": \"7ELEVEN\"}, {\"retail_outlet_name\": \"CEBUANA\"}, {\"retail_outlet_name\": \"DP_MLHUILLIER\"}, {\"retail_outlet_name\": \"DP_ECPAY_LOAN\"}, {\"retail_outlet_name\": \"DP_PALAWAN\"}, {\"retail_outlet_name\": \"LBC\"}, {\"retail_outlet_name\": \"DP_ECPAY_SCHOOL\"}], \"should_exclude_credit_card\": false, \"merchant_profile_picture_url\": \"https://du8nwjtfkinx.cloudfront.net/xendit.png\"}', '2025-11-16 06:23:08', '2025-11-16 06:23:17'),
(21, 91, 4, 7, 'MTK-91-4E265D', '69196e18fbcbf149203e69d6', 'https://checkout-staging.xendit.co/web/69196e18fbcbf149203e69d6', 1500.00, 'PHP', 'paid', NULL, 'John', 'GCASH', NULL, '2025-11-16 14:24:42', '2025-11-16 16:24:24', '{\"id\": \"69196e18fbcbf149203e69d6\", \"amount\": 1500, \"status\": \"PAID\", \"created\": \"2025-11-16T06:24:25.115Z\", \"paid_at\": \"2025-11-16T06:24:42.644Z\", \"updated\": \"2025-11-16T06:24:44.999Z\", \"user_id\": \"69180880ac518b2becbc3e40\", \"currency\": \"PHP\", \"customer\": {\"given_names\": \"John\", \"mobile_number\": \"+639123456789\"}, \"metadata\": {\"notes\": \"ok\", \"slot_id\": 181, \"doctor_id\": 7, \"patient_id\": 4, \"external_id\": \"MTK-91-4E265D\", \"appointment_id\": 91}, \"payment_id\": \"ewc_aa7dd8a9-73b4-4e5f-929a-dbe01f7f5da5\", \"description\": \"Consultation with Dr. jv on Nov 17, 2025 17:24-18:24\", \"expiry_date\": \"2025-11-16T08:24:24.886Z\", \"external_id\": \"MTK-91-4E265D\", \"invoice_url\": \"https://checkout-staging.xendit.co/web/69196e18fbcbf149203e69d6\", \"paid_amount\": 1500, \"merchant_name\": \"Payment\", \"reminder_date\": \"2025-11-16T07:24:24.886Z\", \"payment_method\": \"EWALLET\", \"available_banks\": [], \"payment_channel\": \"GCASH\", \"payment_method_id\": \"pm-8dcff176-a918-438d-baa7-04ec297aa64f\", \"should_send_email\": false, \"available_ewallets\": [{\"ewallet_type\": \"SHOPEEPAY\"}, {\"ewallet_type\": \"GCASH\"}, {\"ewallet_type\": \"GRABPAY\"}, {\"ewallet_type\": \"PAYMAYA\"}], \"available_qr_codes\": [{\"qr_code_type\": \"QRPH\"}], \"available_paylaters\": [{\"paylater_type\": \"BILLEASE\"}, {\"paylater_type\": \"CASHALO\"}], \"failure_redirect_url\": \"http://localhost:3000/appointments/payment-failed?external_id=MTK-91-4E265D\", \"success_redirect_url\": \"http://localhost:3000/appointments/payment-success?external_id=MTK-91-4E265D\", \"available_direct_debits\": [{\"direct_debit_type\": \"DD_RCBC\"}, {\"direct_debit_type\": \"DD_CHINABANK\"}, {\"direct_debit_type\": \"DD_UBP\"}, {\"direct_debit_type\": \"DD_BPI\"}, {\"direct_debit_type\": \"DD_BDO_EPAY\"}, {\"direct_debit_type\": \"DD_BDO_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BPI_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_UNIONBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BOC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_CHINABANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_INSTAPAY_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_LANDBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_MAYBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_METROBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PNB_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PSBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PESONET_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_RCBC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_ROBINSONS_BANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_SECURITY_BANK_ONLINE_BANKING\"}], \"available_retail_outlets\": [{\"retail_outlet_name\": \"7ELEVEN\"}, {\"retail_outlet_name\": \"CEBUANA\"}, {\"retail_outlet_name\": \"DP_MLHUILLIER\"}, {\"retail_outlet_name\": \"DP_ECPAY_LOAN\"}, {\"retail_outlet_name\": \"DP_PALAWAN\"}, {\"retail_outlet_name\": \"LBC\"}, {\"retail_outlet_name\": \"DP_ECPAY_SCHOOL\"}], \"should_exclude_credit_card\": false, \"merchant_profile_picture_url\": \"https://du8nwjtfkinx.cloudfront.net/xendit.png\"}', '2025-11-16 06:24:26', '2025-11-16 06:24:52'),
(22, 92, 4, 7, 'MTK-92-3291A6', '69196e7926d6bc37164d7d2d', 'https://checkout-staging.xendit.co/web/69196e7926d6bc37164d7d2d', 1500.00, 'PHP', 'paid', NULL, 'John', 'cash', 'Manual override completion', '2025-11-16 14:26:25', '2025-11-16 16:26:02', '{\"id\": \"69196e7926d6bc37164d7d2d\", \"amount\": 1500, \"status\": \"PENDING\", \"created\": \"2025-11-16T06:26:02.292Z\", \"updated\": \"2025-11-16T06:26:02.292Z\", \"user_id\": \"69180880ac518b2becbc3e40\", \"currency\": \"PHP\", \"customer\": {\"given_names\": \"John\", \"mobile_number\": \"+639123456789\"}, \"metadata\": {\"notes\": \"ok\", \"slot_id\": 182, \"doctor_id\": 7, \"patient_id\": 4, \"external_id\": \"MTK-92-3291A6\", \"appointment_id\": 92}, \"description\": \"Consultation with Dr. jv on Nov 18, 2025 15:25-16:25\", \"expiry_date\": \"2025-11-16T08:26:02.080Z\", \"external_id\": \"MTK-92-3291A6\", \"invoice_url\": \"https://checkout-staging.xendit.co/web/69196e7926d6bc37164d7d2d\", \"merchant_name\": \"Payment\", \"reminder_date\": \"2025-11-16T07:26:02.080Z\", \"available_banks\": [], \"manual_override\": {\"role\": \"admin\", \"amount\": 1500, \"method\": \"cash\", \"reason\": \"cash\", \"approved_at\": \"2025-11-16 14:26:25\", \"approved_by\": \"Administrator\"}, \"should_send_email\": false, \"available_ewallets\": [{\"ewallet_type\": \"SHOPEEPAY\"}, {\"ewallet_type\": \"GCASH\"}, {\"ewallet_type\": \"GRABPAY\"}, {\"ewallet_type\": \"PAYMAYA\"}], \"available_qr_codes\": [{\"qr_code_type\": \"QRPH\"}], \"available_paylaters\": [{\"paylater_type\": \"BILLEASE\"}, {\"paylater_type\": \"CASHALO\"}], \"failure_redirect_url\": \"http://localhost:3000/appointments/payment-failed?external_id=MTK-92-3291A6\", \"success_redirect_url\": \"http://localhost:3000/appointments/payment-success?external_id=MTK-92-3291A6\", \"available_direct_debits\": [{\"direct_debit_type\": \"DD_RCBC\"}, {\"direct_debit_type\": \"DD_CHINABANK\"}, {\"direct_debit_type\": \"DD_UBP\"}, {\"direct_debit_type\": \"DD_BPI\"}, {\"direct_debit_type\": \"DD_BDO_EPAY\"}, {\"direct_debit_type\": \"DD_BDO_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BPI_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_UNIONBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BOC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_CHINABANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_INSTAPAY_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_LANDBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_MAYBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_METROBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PNB_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PSBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PESONET_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_RCBC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_ROBINSONS_BANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_SECURITY_BANK_ONLINE_BANKING\"}], \"available_retail_outlets\": [{\"retail_outlet_name\": \"7ELEVEN\"}, {\"retail_outlet_name\": \"CEBUANA\"}, {\"retail_outlet_name\": \"DP_MLHUILLIER\"}, {\"retail_outlet_name\": \"DP_ECPAY_LOAN\"}, {\"retail_outlet_name\": \"DP_PALAWAN\"}, {\"retail_outlet_name\": \"LBC\"}, {\"retail_outlet_name\": \"DP_ECPAY_SCHOOL\"}], \"should_exclude_credit_card\": false, \"merchant_profile_picture_url\": \"https://du8nwjtfkinx.cloudfront.net/xendit.png\"}', '2025-11-16 06:26:03', '2025-11-16 06:26:25'),
(23, 93, 4, 7, 'MTK-93-34449E', '69196f0826d6bc37164d7d8e', 'https://checkout-staging.xendit.co/web/69196f0826d6bc37164d7d8e', 1500.00, 'PHP', 'paid', NULL, 'John', 'GCASH', NULL, '2025-11-16 14:28:35', '2025-11-16 16:28:25', '{\"id\": \"69196f0826d6bc37164d7d8e\", \"amount\": 1500, \"status\": \"PAID\", \"created\": \"2025-11-16T06:28:25.278Z\", \"paid_at\": \"2025-11-16T06:28:35.467Z\", \"updated\": \"2025-11-16T06:28:37.364Z\", \"user_id\": \"69180880ac518b2becbc3e40\", \"currency\": \"PHP\", \"customer\": {\"given_names\": \"John\", \"mobile_number\": \"+639123456789\"}, \"metadata\": {\"notes\": \"ok\", \"slot_id\": 183, \"doctor_id\": 7, \"patient_id\": 4, \"external_id\": \"MTK-93-34449E\", \"appointment_id\": 93}, \"payment_id\": \"ewc_b2767d1f-080d-439d-920b-df7a47e5e880\", \"description\": \"Consultation with Dr. jv on Nov 19, 2025 14:28-15:28\", \"expiry_date\": \"2025-11-16T08:28:25.076Z\", \"external_id\": \"MTK-93-34449E\", \"invoice_url\": \"https://checkout-staging.xendit.co/web/69196f0826d6bc37164d7d8e\", \"paid_amount\": 1500, \"merchant_name\": \"Payment\", \"reminder_date\": \"2025-11-16T07:28:25.076Z\", \"payment_method\": \"EWALLET\", \"available_banks\": [], \"payment_channel\": \"GCASH\", \"payment_method_id\": \"pm-d9b83dbf-5aa7-40be-8a95-efe175384e53\", \"should_send_email\": false, \"available_ewallets\": [{\"ewallet_type\": \"SHOPEEPAY\"}, {\"ewallet_type\": \"GCASH\"}, {\"ewallet_type\": \"GRABPAY\"}, {\"ewallet_type\": \"PAYMAYA\"}], \"available_qr_codes\": [{\"qr_code_type\": \"QRPH\"}], \"available_paylaters\": [{\"paylater_type\": \"BILLEASE\"}, {\"paylater_type\": \"CASHALO\"}], \"failure_redirect_url\": \"http://localhost:3000/appointments/payment-failed?external_id=MTK-93-34449E\", \"success_redirect_url\": \"http://localhost:3000/appointments/payment-success?external_id=MTK-93-34449E\", \"available_direct_debits\": [{\"direct_debit_type\": \"DD_RCBC\"}, {\"direct_debit_type\": \"DD_CHINABANK\"}, {\"direct_debit_type\": \"DD_UBP\"}, {\"direct_debit_type\": \"DD_BPI\"}, {\"direct_debit_type\": \"DD_BDO_EPAY\"}, {\"direct_debit_type\": \"DD_BDO_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BPI_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_UNIONBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BOC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_CHINABANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_INSTAPAY_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_LANDBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_MAYBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_METROBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PNB_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PSBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PESONET_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_RCBC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_ROBINSONS_BANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_SECURITY_BANK_ONLINE_BANKING\"}], \"available_retail_outlets\": [{\"retail_outlet_name\": \"7ELEVEN\"}, {\"retail_outlet_name\": \"CEBUANA\"}, {\"retail_outlet_name\": \"DP_MLHUILLIER\"}, {\"retail_outlet_name\": \"DP_ECPAY_LOAN\"}, {\"retail_outlet_name\": \"DP_PALAWAN\"}, {\"retail_outlet_name\": \"LBC\"}, {\"retail_outlet_name\": \"DP_ECPAY_SCHOOL\"}], \"should_exclude_credit_card\": false, \"merchant_profile_picture_url\": \"https://du8nwjtfkinx.cloudfront.net/xendit.png\"}', '2025-11-16 06:28:26', '2025-11-16 06:28:44'),
(24, 96, 4, 7, 'MTK-96-EBE467', '691d549326d6bc371653153c', 'https://checkout-staging.xendit.co/web/691d549326d6bc371653153c', 500.00, 'PHP', 'paid', NULL, 'John', 'GCASH', NULL, '2025-11-19 13:25:12', '2025-11-19 15:24:36', '{\"id\": \"691d549326d6bc371653153c\", \"amount\": 500, \"status\": \"PAID\", \"created\": \"2025-11-19T05:24:36.453Z\", \"paid_at\": \"2025-11-19T05:25:12.676Z\", \"updated\": \"2025-11-19T05:25:15.428Z\", \"user_id\": \"69180880ac518b2becbc3e40\", \"currency\": \"PHP\", \"customer\": {\"given_names\": \"John\", \"mobile_number\": \"+639123456789\"}, \"metadata\": {\"notes\": \"ok\", \"slot_id\": 184, \"doctor_id\": 7, \"patient_id\": 4, \"external_id\": \"MTK-96-EBE467\", \"appointment_id\": 96}, \"payment_id\": \"ewc_fe9884a9-378e-4432-931a-ab7b83651f11\", \"description\": \"Consultation with Dr. jv on Nov 20, 2025 13:16-14:16\", \"expiry_date\": \"2025-11-19T07:24:36.231Z\", \"external_id\": \"MTK-96-EBE467\", \"invoice_url\": \"https://checkout-staging.xendit.co/web/691d549326d6bc371653153c\", \"paid_amount\": 500, \"merchant_name\": \"Payment\", \"reminder_date\": \"2025-11-19T06:24:36.231Z\", \"payment_method\": \"EWALLET\", \"available_banks\": [], \"payment_channel\": \"GCASH\", \"payment_method_id\": \"pm-d24f2336-2edf-42e8-9a82-f55d9a980862\", \"should_send_email\": false, \"available_ewallets\": [{\"ewallet_type\": \"SHOPEEPAY\"}, {\"ewallet_type\": \"GCASH\"}, {\"ewallet_type\": \"GRABPAY\"}, {\"ewallet_type\": \"PAYMAYA\"}], \"available_qr_codes\": [{\"qr_code_type\": \"QRPH\"}], \"available_paylaters\": [{\"paylater_type\": \"BILLEASE\"}, {\"paylater_type\": \"CASHALO\"}], \"failure_redirect_url\": \"http://localhost:3000/appointments/payment-failed?external_id=MTK-96-EBE467\", \"success_redirect_url\": \"http://localhost:3000/appointments/payment-success?external_id=MTK-96-EBE467\", \"available_direct_debits\": [{\"direct_debit_type\": \"DD_RCBC\"}, {\"direct_debit_type\": \"DD_CHINABANK\"}, {\"direct_debit_type\": \"DD_UBP\"}, {\"direct_debit_type\": \"DD_BPI\"}, {\"direct_debit_type\": \"DD_BDO_EPAY\"}, {\"direct_debit_type\": \"DD_BDO_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BPI_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_UNIONBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_BOC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_CHINABANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_INSTAPAY_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_LANDBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_MAYBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_METROBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PNB_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PSBANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_PESONET_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_RCBC_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_ROBINSONS_BANK_ONLINE_BANKING\"}, {\"direct_debit_type\": \"DD_SECURITY_BANK_ONLINE_BANKING\"}], \"available_retail_outlets\": [{\"retail_outlet_name\": \"7ELEVEN\"}, {\"retail_outlet_name\": \"CEBUANA\"}, {\"retail_outlet_name\": \"DP_MLHUILLIER\"}, {\"retail_outlet_name\": \"DP_ECPAY_LOAN\"}, {\"retail_outlet_name\": \"DP_PALAWAN\"}, {\"retail_outlet_name\": \"LBC\"}, {\"retail_outlet_name\": \"DP_ECPAY_SCHOOL\"}], \"should_exclude_credit_card\": false, \"merchant_profile_picture_url\": \"https://du8nwjtfkinx.cloudfront.net/xendit.png\"}', '2025-11-19 05:24:36', '2025-11-19 05:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','doctor','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@example.com', '$2y$10$cFW3e.toQpm.7mFORBoEw.SaO0pwB23J7EVCbDAaeVJfoYh.mjXUS', 'admin', '2025-10-18 00:15:17'),
(4, 'Vincent', 'jhonvincenthernandez1@gmail.com', '$2y$10$AggOCklYQ2gyKAHSBGNLDuNPsQZmUaCFI4Vy6Gjs4hyVYEkK5y6Ba', 'doctor', '2025-10-18 02:04:05'),
(11, 'Hernandez', 'jhonvincenthernandez2@gmail.com', '$2y$10$G9yp7nXxpECgvs4tMed5DOFdsqQYtv8kthD2Ey1LWU9qu9bK8MdlS', 'staff', '2025-11-06 16:43:15'),
(12, 'jv', 'jhonvincenthernandez28@gmail.com', '$2a$10$5JqxIPc7o0imQJ7gMoDnwORJAiBvnzJdSPoJUkbdqkWnodFhOZnES', 'doctor', '2025-11-11 09:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `xendit_webhook_logs`
--

DROP TABLE IF EXISTS `xendit_webhook_logs`;
CREATE TABLE IF NOT EXISTS `xendit_webhook_logs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_payload` json DEFAULT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_xendit_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_doctor_appointment` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_slot` FOREIGN KEY (`slot_id`) REFERENCES `doctor_slots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_slots`
--
ALTER TABLE `doctor_slots`
  ADD CONSTRAINT `fk_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_appointments` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

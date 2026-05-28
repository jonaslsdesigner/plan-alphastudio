-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 28/05/2026 às 18:20
-- Versão do servidor: 11.8.6-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u310196928_alpha_planilha`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL,
  `type` enum('checking','cash','credit','saving') NOT NULL DEFAULT 'checking',
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `card_invoice_totals`
--

CREATE TABLE `card_invoice_totals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `credit_card_id` bigint(20) UNSIGNED NOT NULL,
  `reference_month` varchar(7) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `card_invoice_totals`
--

INSERT INTO `card_invoice_totals` (`id`, `user_id`, `credit_card_id`, `reference_month`, `amount`, `created_at`, `updated_at`) VALUES
(5, 2, 5, '2026-04', 208.00, '2026-04-21 21:24:48', NULL),
(6, 2, 6, '2026-04', 270.00, '2026-04-21 21:25:21', NULL),
(7, 2, 7, '2026-04', 118.00, '2026-04-21 21:25:49', NULL),
(8, 2, 8, '2026-04', 214.00, '2026-04-21 21:26:11', NULL),
(10, 1, 9, '2026-05', 2517.00, '2026-04-23 22:19:06', '2026-05-10 18:40:39'),
(11, 1, 10, '2026-05', 0.00, '2026-04-23 22:20:40', '2026-05-21 12:46:56'),
(24, 1, 9, '2026-06', 1068.20, '2026-04-27 13:11:40', '2026-05-21 01:06:29'),
(25, 1, 10, '2026-06', 0.00, '2026-04-27 13:11:50', '2026-05-21 12:47:16'),
(26, 1, 9, '2026-07', 1562.00, '2026-04-27 13:12:05', '2026-05-23 03:02:46'),
(27, 1, 10, '2026-07', 0.00, '2026-04-27 13:12:14', '2026-05-21 12:47:45'),
(28, 1, 9, '2026-08', 1245.00, '2026-04-27 13:12:30', '2026-05-23 03:10:03'),
(29, 1, 10, '2026-08', 135.00, '2026-04-27 13:12:41', NULL),
(30, 1, 9, '2026-09', 834.00, '2026-04-27 13:12:57', '2026-05-23 03:10:43'),
(31, 1, 10, '2026-09', 135.00, '2026-04-27 13:13:02', NULL),
(32, 1, 9, '2026-10', 728.00, '2026-04-27 13:13:15', '2026-05-23 03:11:01'),
(33, 1, 9, '2026-11', 613.00, '2026-04-27 13:13:31', '2026-05-23 03:11:15'),
(34, 1, 9, '2026-12', 547.00, '2026-04-27 13:13:54', '2026-05-23 03:11:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `card_purchases`
--

CREATE TABLE `card_purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `credit_card_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_month` char(7) NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `installment_group` varchar(40) DEFAULT NULL,
  `installment_number` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `installment_total` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `installment_auto` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `card_purchases`
--

INSERT INTO `card_purchases` (`id`, `user_id`, `sort_order`, `credit_card_id`, `reference_month`, `title`, `description`, `amount`, `purchase_date`, `installment_group`, `installment_number`, `installment_total`, `installment_auto`, `created_at`) VALUES
(2, 1, 0, NULL, '2026-05', 'Fábio 4/6', '', 240.00, NULL, NULL, 1, 1, 0, '2026-04-21 17:17:01'),
(4, 1, 0, NULL, '2026-05', 'Nonata 3/10', '', 73.22, NULL, NULL, 1, 1, 0, '2026-04-21 17:17:59'),
(5, 1, 0, NULL, '2026-05', 'Nonata 3/6', '', 80.24, NULL, NULL, 1, 1, 0, '2026-04-21 17:18:07'),
(6, 1, 0, NULL, '2026-05', 'Nonata 2/3', '', 65.61, NULL, NULL, 1, 1, 0, '2026-04-21 17:18:21'),
(7, 1, 0, NULL, '2026-05', 'Celene 7/10', '', 200.00, NULL, NULL, 1, 1, 0, '2026-04-21 17:18:37'),
(8, 1, 0, NULL, '2026-05', 'Junior 9/9', '', 100.00, NULL, NULL, 1, 1, 0, '2026-04-21 17:18:50'),
(12, 1, 3, 9, '2026-05', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 9, 9, 1, '2026-04-23 22:24:57'),
(13, 1, 1, 9, '2026-05', 'Fabio', '', 240.00, NULL, 'd7dec4ff6a0b156d5f3a2c07', 4, 6, 1, '2026-04-23 22:25:17'),
(14, 1, 6, 9, '2026-05', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 3, 10, 1, '2026-04-23 22:25:30'),
(15, 1, 5, 9, '2026-05', 'Nonata', '', 80.24, NULL, '1c0083038a3fbc945ca516ba', 3, 6, 1, '2026-04-23 22:25:44'),
(16, 1, 7, 9, '2026-05', 'Nonata', '', 65.61, NULL, '9a9bc1c7bb6d5916a087d308', 2, 3, 1, '2026-04-23 22:26:00'),
(17, 1, 2, 9, '2026-05', 'Celene', '', 200.00, NULL, '82b0f65009bc78fb0b2b31d6', 7, 10, 1, '2026-04-23 22:26:12'),
(18, 1, 4, 9, '2026-05', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 1, 10, 1, '2026-04-24 18:18:15'),
(42, 1, 7, 9, '2026-06', 'Nonata', '', 65.61, NULL, '9a9bc1c7bb6d5916a087d308', 3, 3, 1, '2026-04-26 17:19:14'),
(43, 1, 5, 9, '2026-06', 'Nonata', '', 80.24, NULL, '1c0083038a3fbc945ca516ba', 4, 6, 1, '2026-04-26 17:19:19'),
(44, 1, 5, 9, '2026-07', 'Nonata', '', 80.24, NULL, '1c0083038a3fbc945ca516ba', 5, 6, 1, '2026-04-26 17:19:19'),
(45, 1, 5, 9, '2026-08', 'Nonata', '', 80.24, NULL, '1c0083038a3fbc945ca516ba', 6, 6, 1, '2026-04-26 17:19:19'),
(46, 1, 6, 9, '2026-06', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 4, 10, 1, '2026-04-26 17:19:24'),
(47, 1, 6, 9, '2026-07', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 5, 10, 1, '2026-04-26 17:19:24'),
(48, 1, 6, 9, '2026-08', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 6, 10, 1, '2026-04-26 17:19:24'),
(49, 1, 6, 9, '2026-09', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 7, 10, 1, '2026-04-26 17:19:24'),
(50, 1, 6, 9, '2026-10', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 8, 10, 1, '2026-04-26 17:19:24'),
(51, 1, 6, 9, '2026-11', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 9, 10, 1, '2026-04-26 17:19:24'),
(52, 1, 6, 9, '2026-12', 'Nonata', '', 73.22, NULL, '04929880716aec2ab286bff2', 10, 10, 1, '2026-04-26 17:19:24'),
(53, 1, 1, 9, '2026-06', 'Fabio', '', 240.00, NULL, 'd7dec4ff6a0b156d5f3a2c07', 5, 6, 1, '2026-04-26 17:19:30'),
(54, 1, 1, 9, '2026-07', 'Fabio', '', 240.00, NULL, 'd7dec4ff6a0b156d5f3a2c07', 6, 6, 1, '2026-04-26 17:19:30'),
(55, 1, 2, 9, '2026-06', 'Celene', '', 200.00, NULL, '82b0f65009bc78fb0b2b31d6', 8, 10, 1, '2026-04-26 17:46:41'),
(56, 1, 2, 9, '2026-07', 'Celene', '', 200.00, NULL, '82b0f65009bc78fb0b2b31d6', 9, 10, 1, '2026-04-26 17:46:41'),
(57, 1, 2, 9, '2026-08', 'Celene', '', 200.00, NULL, '82b0f65009bc78fb0b2b31d6', 10, 10, 1, '2026-04-26 17:46:41'),
(67, 1, 4, 9, '2026-06', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 2, 10, 1, '2026-04-26 18:48:56'),
(68, 1, 4, 9, '2026-07', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 3, 10, 1, '2026-04-26 18:48:56'),
(69, 1, 4, 9, '2026-08', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 4, 10, 1, '2026-04-26 18:48:56'),
(70, 1, 4, 9, '2026-09', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 5, 10, 1, '2026-04-26 18:48:56'),
(71, 1, 4, 9, '2026-10', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 6, 10, 1, '2026-04-26 18:48:56'),
(72, 1, 4, 9, '2026-11', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 7, 10, 1, '2026-04-26 18:48:56'),
(73, 1, 4, 9, '2026-12', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 8, 10, 1, '2026-04-26 18:48:56'),
(74, 1, 4, 9, '2027-01', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 9, 10, 1, '2026-04-26 18:48:56'),
(75, 1, 4, 9, '2027-02', 'Nonata', '', 87.40, NULL, '26d42bfabcb4df918b6971a4', 10, 10, 1, '2026-04-26 18:48:56'),
(92, 1, 0, 9, '2025-09', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 1, 9, 1, '2026-04-28 18:02:34'),
(93, 1, 0, 9, '2025-10', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 2, 9, 1, '2026-04-28 18:02:34'),
(94, 1, 0, 9, '2025-11', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 3, 9, 1, '2026-04-28 18:02:34'),
(95, 1, 0, 9, '2025-12', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 4, 9, 1, '2026-04-28 18:02:34'),
(96, 1, 0, 9, '2026-01', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 5, 9, 1, '2026-04-28 18:02:34'),
(97, 1, 0, 9, '2026-02', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 6, 9, 1, '2026-04-28 18:02:34'),
(98, 1, 0, 9, '2026-03', 'Junior', 'Cell', 100.00, NULL, '1e4969fe63699086f233aef0', 7, 9, 1, '2026-04-28 18:02:34'),
(101, 1, 0, 9, '2026-06', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 1, 10, 1, '2026-05-20 13:15:48'),
(102, 1, 0, 9, '2026-07', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 2, 10, 1, '2026-05-20 13:15:48'),
(103, 1, 0, 9, '2026-08', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 3, 10, 1, '2026-05-20 13:15:48'),
(104, 1, 0, 9, '2026-09', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 4, 10, 1, '2026-05-20 13:15:48'),
(105, 1, 0, 9, '2026-10', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 5, 10, 1, '2026-05-20 13:15:48'),
(106, 1, 0, 9, '2026-11', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 6, 10, 1, '2026-05-20 13:15:48'),
(107, 1, 0, 9, '2026-12', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 7, 10, 1, '2026-05-20 13:15:48'),
(108, 1, 0, 9, '2027-01', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 8, 10, 1, '2026-05-20 13:15:48'),
(109, 1, 0, 9, '2027-02', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 9, 10, 1, '2026-05-20 13:15:48'),
(110, 1, 0, 9, '2027-03', 'Fabio Tablet Metade', '', 88.00, NULL, '58ad3774044530aae668655f', 10, 10, 1, '2026-05-20 13:15:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL,
  `type` enum('income','expense') NOT NULL DEFAULT 'expense',
  `color` varchar(16) NOT NULL DEFAULT '#4f46e5',
  `icon` varchar(40) NOT NULL DEFAULT 'tag',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `sort_order`, `name`, `type`, `color`, `icon`, `created_at`) VALUES
(1, 1, 0, 'Casa/Fixas', 'expense', '#24589b', 'tag', '2026-04-21 16:53:00'),
(2, 1, 0, 'Assinaturas', 'expense', '#204b11', 'tag', '2026-04-21 16:53:30'),
(4, 1, 0, 'Outras', 'expense', '#60728a', 'tag', '2026-04-21 16:54:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `commitments`
--

CREATE TABLE `commitments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `start_year` smallint(5) UNSIGNED NOT NULL,
  `start_month` tinyint(3) UNSIGNED NOT NULL,
  `duration_months` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('active','done') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `commitments`
--

INSERT INTO `commitments` (`id`, `user_id`, `sort_order`, `title`, `description`, `amount`, `start_year`, `start_month`, `duration_months`, `status`, `created_at`) VALUES
(3, 1, 2, 'Transferência Moto', '', 650.00, 2026, 1, 12, 'active', '2026-04-21 17:34:30');

-- --------------------------------------------------------

--
-- Estrutura para tabela `credit_cards`
--

CREATE TABLE `credit_cards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(120) NOT NULL,
  `closing_day` tinyint(3) UNSIGNED DEFAULT NULL,
  `due_day` tinyint(3) UNSIGNED DEFAULT NULL,
  `color` varchar(16) NOT NULL DEFAULT '#191929',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `credit_cards`
--

INSERT INTO `credit_cards` (`id`, `user_id`, `sort_order`, `name`, `closing_day`, `due_day`, `color`, `created_at`) VALUES
(5, 2, 0, 'Nubank Au', NULL, 10, '#ff00ff', '2026-04-21 21:24:48'),
(6, 2, 0, 'Neon Joyci', NULL, 15, '#00ffff', '2026-04-21 21:25:21'),
(7, 2, 0, 'Cartão Mercado', NULL, 20, '#0000ff', '2026-04-21 21:25:49'),
(8, 2, 0, 'Santander Pj', NULL, 25, '#ff0000', '2026-04-21 21:26:11'),
(9, 1, 0, 'Fábio Nubank', 17, 20, '#441c6d', '2026-04-23 22:19:06'),
(10, 1, 0, 'Jonas Nubank', 20, 27, '#692bab', '2026-04-23 22:20:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `goals`
--

CREATE TABLE `goals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(140) NOT NULL,
  `target_amount` decimal(12,2) NOT NULL,
  `current_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `color` varchar(16) NOT NULL DEFAULT '#2563eb',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `income_sources`
--

CREATE TABLE `income_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reference_month` char(7) NOT NULL,
  `title` varchar(140) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `income_type` varchar(40) NOT NULL DEFAULT 'other',
  `received_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `income_sources`
--

INSERT INTO `income_sources` (`id`, `user_id`, `sort_order`, `reference_month`, `title`, `amount`, `income_type`, `received_date`, `status`, `created_at`) VALUES
(1, 1, 1, '2026-05', 'Projetar Agência', 1050.00, 'other', '2026-05-06', 'received', '2026-04-21 17:35:16'),
(3, 1, 0, '2026-05', 'Outros', 415.40, 'other', '2026-04-29', 'received', '2026-04-29 13:22:06'),
(5, 1, 0, '2026-05', 'Quizena Coaph', 705.13, 'first_half', '2026-05-15', 'pending', '2026-05-01 19:08:21'),
(6, 1, 0, '2026-05', 'Final de Mês Coaph', 1035.00, 'other', '2026-06-01', 'pending', '2026-05-01 19:08:40'),
(9, 1, 0, '2026-06', 'Quizena Coaph', 750.00, 'first_half', '2026-06-15', 'pending', '2026-05-17 16:33:23'),
(10, 1, 0, '2026-06', 'Final de Mês Coaph', 1000.00, 'month_end', '2026-06-30', 'pending', '2026-05-17 16:33:43'),
(11, 1, 0, '2026-11', 'Quizena Coaph', 750.00, 'first_half', '2026-11-15', 'pending', '2026-05-17 16:38:49'),
(12, 1, 0, '2026-11', 'Final de Mês Coaph', 1035.00, 'month_end', '2026-11-17', 'pending', '2026-05-17 16:39:03'),
(13, 1, 0, '2026-07', 'Quizena Coaph', 750.00, 'first_half', '2026-07-15', 'pending', '2026-05-17 16:46:02'),
(14, 1, 0, '2026-07', 'Final de Mês Coaph', 1000.00, 'month_end', '2026-07-31', 'pending', '2026-05-17 16:46:14'),
(15, 1, 0, '2026-08', 'Quizena Coaph', 750.00, 'first_half', '2026-08-14', 'pending', '2026-05-17 16:46:29'),
(16, 1, 0, '2026-08', 'Final de Mês Coaph', 1035.00, 'month_end', '2026-08-31', 'pending', '2026-05-17 16:46:41'),
(17, 1, 0, '2026-09', 'Quizena Coaph', 750.00, 'first_half', '2026-09-15', 'pending', '2026-05-17 16:47:20'),
(18, 1, 0, '2026-09', 'Final de Mês Coaph', 1035.00, 'month_end', '2026-09-30', 'pending', '2026-05-17 16:47:30'),
(19, 1, 0, '2026-10', 'Quizena Coaph', 750.00, 'first_half', '2026-10-15', 'pending', '2026-05-17 16:47:48'),
(20, 1, 0, '2026-10', 'Final de Mês Coaph', 1035.00, 'month_end', '2026-10-30', 'pending', '2026-05-17 16:48:00'),
(21, 1, 0, '2026-12', 'Quizena Coaph', 750.00, 'first_half', '2026-12-15', 'pending', '2026-05-17 16:48:20'),
(22, 1, 0, '2026-12', 'Final de Mês Coaph', 1035.00, 'month_end', '2026-12-31', 'pending', '2026-05-17 16:48:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `monthly_bills`
--

CREATE TABLE `monthly_bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reference_month` char(7) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `due_day` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `payment_month_offset` tinyint(4) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `auto_create` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `monthly_bills`
--

INSERT INTO `monthly_bills` (`id`, `user_id`, `sort_order`, `reference_month`, `category_id`, `title`, `amount`, `due_day`, `payment_month_offset`, `active`, `auto_create`, `created_at`) VALUES
(7, 2, 3, '2026-04', NULL, 'Cred amigo 2/6', 1077.00, 5, 0, 1, 0, '2026-04-21 21:12:52'),
(8, 2, 4, '2026-04', NULL, 'Consorcio', 650.00, 6, 0, 1, 0, '2026-04-21 21:14:30'),
(9, 2, 6, '2026-04', NULL, 'Hapvida', 210.00, 10, 0, 1, 1, '2026-04-21 21:16:08'),
(11, 2, 8, '2026-04', NULL, 'Cama', 155.00, 15, 0, 1, 1, '2026-04-21 21:23:02'),
(12, 2, 5, '2026-04', NULL, 'Empréstimo Nub', 378.00, 10, 0, 1, 1, '2026-04-21 21:23:33'),
(13, 2, 10, '2026-04', NULL, 'Ceará cred', 400.00, 30, 0, 1, 1, '2026-04-21 21:23:52'),
(14, 2, 7, '2026-04', NULL, 'Empréstimo Nub', 150.00, 13, 0, 1, 1, '2026-04-21 21:24:17'),
(15, 2, 1, '2026-04', NULL, 'Escola', 480.00, 7, 0, 1, 1, '2026-04-21 21:30:32'),
(16, 2, 9, '2026-04', NULL, 'Aluguel ponto', 300.00, 20, 0, 1, 1, '2026-04-21 21:34:55'),
(17, 2, 2, '2026-04', NULL, 'Internet ponto', 75.00, 5, 0, 1, 1, '2026-04-21 21:35:13'),
(18, 2, 11, '2026-04', NULL, 'Internet Ap', 85.00, 30, 0, 1, 1, '2026-04-21 21:35:28'),
(19, 1, 1, '2026-05', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-23 19:01:36'),
(21, 1, 4, '2026-05', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-04-23 22:17:06'),
(29, 1, 3, '2026-05', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-04-23 22:17:28'),
(37, 1, 5, '2026-05', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-04-23 22:17:39'),
(45, 1, 2, '2026-05', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-04-23 22:17:49'),
(69, 1, 1, '2026-06', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(70, 1, 1, '2026-07', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(71, 1, 1, '2026-08', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(72, 1, 1, '2026-09', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(73, 1, 1, '2026-10', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(74, 1, 1, '2026-11', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(75, 1, 1, '2026-12', 1, 'Aluguel Robercildo', 500.00, 15, 0, 1, 1, '2026-04-26 06:30:22'),
(83, 1, 0, '2026-06', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(84, 1, 0, '2026-07', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(85, 1, 0, '2026-08', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(86, 1, 0, '2026-09', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(87, 1, 0, '2026-10', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(88, 1, 0, '2026-11', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(89, 1, 0, '2026-12', 1, 'Energia', 199.00, 1, 1, 1, 1, '2026-05-01 19:18:03'),
(97, 1, 0, '2026-06', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(98, 1, 0, '2026-07', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(99, 1, 0, '2026-08', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(100, 1, 0, '2026-09', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(101, 1, 0, '2026-10', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(102, 1, 0, '2026-11', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(103, 1, 0, '2026-12', 1, 'Internet', 96.00, 1, 1, 1, 1, '2026-05-01 19:18:13'),
(104, 1, 0, '2026-06', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(105, 1, 0, '2026-07', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(106, 1, 0, '2026-08', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(107, 1, 0, '2026-09', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(108, 1, 0, '2026-10', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(109, 1, 0, '2026-11', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(110, 1, 0, '2026-12', 1, 'Laura Escola', 240.00, 1, 1, 1, 1, '2026-05-01 19:18:17'),
(111, 1, 0, '2026-06', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06'),
(112, 1, 0, '2026-07', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06'),
(113, 1, 0, '2026-08', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06'),
(114, 1, 0, '2026-09', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06'),
(115, 1, 0, '2026-10', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06'),
(116, 1, 0, '2026-11', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06'),
(117, 1, 0, '2026-12', 1, 'Água', 152.30, 1, 1, 1, 1, '2026-05-11 12:42:06');

-- --------------------------------------------------------

--
-- Estrutura para tabela `monthly_item_statuses`
--

CREATE TABLE `monthly_item_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `item_type` varchar(40) NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `reference_month` char(7) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `actual_date` date DEFAULT NULL,
  `actual_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `monthly_item_statuses`
--

INSERT INTO `monthly_item_statuses` (`id`, `user_id`, `item_type`, `item_id`, `reference_month`, `status`, `actual_date`, `actual_amount`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'monthly_bill', 19, '2026-05', 'paid', '2026-05-17', NULL, NULL, '2026-05-17 16:19:42', '2026-05-17 16:19:42'),
(2, 1, 'card_invoice', 9, '2026-05', 'paid', '2026-05-21', NULL, NULL, '2026-05-21 12:46:35', '2026-05-21 12:46:35'),
(3, 1, 'card_invoice', 10, '2026-05', 'paid', '2026-05-21', NULL, NULL, '2026-05-21 12:46:39', '2026-05-21 12:46:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `monthly_sort_orders`
--

CREATE TABLE `monthly_sort_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(60) NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `reference_month` char(7) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `monthly_sort_orders`
--

INSERT INTO `monthly_sort_orders` (`id`, `user_id`, `table_name`, `item_id`, `reference_month`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'card_purchases', 12, '2026-05', 1, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(2, 1, 'card_purchases', 13, '2026-05', 2, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(3, 1, 'card_purchases', 14, '2026-05', 3, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(4, 1, 'card_purchases', 15, '2026-05', 5, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(5, 1, 'card_purchases', 16, '2026-05', 6, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(6, 1, 'card_purchases', 17, '2026-05', 7, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(7, 1, 'card_purchases', 18, '2026-05', 4, '2026-04-29 21:01:25', '2026-05-18 18:52:35'),
(8, 1, 'categories', 2, '2026-05', 2, '2026-04-29 22:36:20', '2026-04-29 22:36:23'),
(9, 1, 'categories', 1, '2026-05', 1, '2026-04-29 22:36:20', '2026-04-29 22:36:23'),
(10, 1, 'categories', 4, '2026-05', 3, '2026-04-29 22:36:20', '2026-04-29 22:36:23'),
(14, 1, 'categories', 1, '2026-04', 1, '2026-04-30 13:42:15', '2026-04-30 13:42:21'),
(15, 1, 'categories', 2, '2026-04', 2, '2026-04-30 13:42:15', '2026-04-30 13:42:21'),
(16, 1, 'categories', 4, '2026-04', 3, '2026-04-30 13:42:15', '2026-04-30 13:42:21'),
(26, 1, 'income_sources', 3, '2026-05', 1, '2026-05-01 19:07:57', '2026-05-02 17:41:57'),
(27, 1, 'income_sources', 1, '2026-05', 2, '2026-05-01 19:07:57', '2026-05-02 17:41:57'),
(28, 1, 'monthly_bills', 21, '2026-05', 2, '2026-05-01 21:16:02', '2026-05-01 21:16:08'),
(29, 1, 'monthly_bills', 19, '2026-05', 1, '2026-05-01 21:16:02', '2026-05-01 21:16:08'),
(30, 1, 'monthly_bills', 29, '2026-05', 3, '2026-05-01 21:16:02', '2026-05-01 21:16:08'),
(31, 1, 'monthly_bills', 37, '2026-05', 4, '2026-05-01 21:16:02', '2026-05-01 21:16:08'),
(32, 1, 'monthly_bills', 45, '2026-05', 5, '2026-05-01 21:16:02', '2026-05-01 21:16:08'),
(40, 1, 'income_sources', 5, '2026-05', 3, '2026-05-02 17:41:57', '2026-05-02 17:41:57'),
(42, 1, 'income_sources', 6, '2026-05', 5, '2026-05-02 17:41:57', '2026-05-02 17:41:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('income','expense') NOT NULL DEFAULT 'expense',
  `title` varchar(160) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_at` date DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `monthly_income` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nickname` varchar(80) DEFAULT NULL,
  `age` tinyint(3) UNSIGNED DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `currency` varchar(8) NOT NULL DEFAULT 'BRL',
  `theme_color` varchar(16) NOT NULL DEFAULT '#4f46e5',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `monthly_income`, `nickname`, `age`, `avatar_path`, `currency`, `theme_color`, `created_at`) VALUES
(1, 'Jonas Lima', 'jonaslimasousa9@gmail.com', '$2y$10$dJLqcYG5ABGg9fswSPaB0OTXhqUgH4S3yN99d3lVokBHWhv7uU9J.', 1815.00, NULL, 26, 'uploads/avatar-1-1777322724.jpg', 'BRL', '#066ab5', '2026-04-21 15:04:36'),
(2, 'Joyce', 'Joyce.lene@hotmail.com', '$2y$10$aKjTvkRWr4kyrOzWK0R2w.6PJAH.C.Q1TAuCbQU0/6zrLKvRXwBxS', 0.00, NULL, NULL, NULL, 'BRL', '#4f46e5', '2026-04-21 20:41:45'),
(3, 'Viviane do Nascimento', 'vivianeux1906@gmail.com', '$2y$10$VZIzZX/E.t.DLB60uHCGp.95dzGvNPIzyT6haJ0id7b4i6gbqEeHS', 0.00, NULL, NULL, NULL, 'BRL', '#4f46e5', '2026-04-29 01:34:05');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_accounts_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `card_invoice_totals`
--
ALTER TABLE `card_invoice_totals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_invoice` (`user_id`,`credit_card_id`,`reference_month`),
  ADD KEY `credit_card_id` (`credit_card_id`);

--
-- Índices de tabela `card_purchases`
--
ALTER TABLE `card_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_card_purchases_card` (`credit_card_id`),
  ADD KEY `idx_card_purchases_user_month` (`user_id`,`reference_month`),
  ADD KEY `idx_card_purchases_installments` (`user_id`,`installment_group`,`installment_number`),
  ADD KEY `idx_card_purchases_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categories_user_type` (`user_id`,`type`),
  ADD KEY `idx_categories_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `commitments`
--
ALTER TABLE `commitments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commitments_user_status` (`user_id`,`status`),
  ADD KEY `idx_commitments_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `credit_cards`
--
ALTER TABLE `credit_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_credit_cards_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_goals_user` (`user_id`);

--
-- Índices de tabela `income_sources`
--
ALTER TABLE `income_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_income_sources_user_month` (`user_id`,`reference_month`),
  ADD KEY `idx_income_sources_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `monthly_bills`
--
ALTER TABLE `monthly_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_monthly_bills_category` (`category_id`),
  ADD KEY `idx_monthly_bills_user` (`user_id`,`reference_month`,`active`),
  ADD KEY `idx_monthly_bills_sort_order` (`user_id`,`sort_order`);

--
-- Índices de tabela `monthly_item_statuses`
--
ALTER TABLE `monthly_item_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_monthly_item_status` (`user_id`,`item_type`,`item_id`,`reference_month`),
  ADD KEY `idx_monthly_item_statuses_month` (`user_id`,`reference_month`,`status`);

--
-- Índices de tabela `monthly_sort_orders`
--
ALTER TABLE `monthly_sort_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_monthly_sort_order` (`user_id`,`table_name`,`item_id`,`reference_month`),
  ADD KEY `idx_monthly_sort_lookup` (`user_id`,`table_name`,`reference_month`,`sort_order`);

--
-- Índices de tabela `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transactions_category` (`category_id`),
  ADD KEY `fk_transactions_account` (`account_id`),
  ADD KEY `idx_transactions_user_date` (`user_id`,`due_date`),
  ADD KEY `idx_transactions_user_status` (`user_id`,`status`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `card_invoice_totals`
--
ALTER TABLE `card_invoice_totals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `card_purchases`
--
ALTER TABLE `card_purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT de tabela `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `commitments`
--
ALTER TABLE `commitments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `credit_cards`
--
ALTER TABLE `credit_cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `goals`
--
ALTER TABLE `goals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `income_sources`
--
ALTER TABLE `income_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `monthly_bills`
--
ALTER TABLE `monthly_bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT de tabela `monthly_item_statuses`
--
ALTER TABLE `monthly_item_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `monthly_sort_orders`
--
ALTER TABLE `monthly_sort_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de tabela `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_accounts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `card_invoice_totals`
--
ALTER TABLE `card_invoice_totals`
  ADD CONSTRAINT `card_invoice_totals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `card_invoice_totals_ibfk_2` FOREIGN KEY (`credit_card_id`) REFERENCES `credit_cards` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `card_purchases`
--
ALTER TABLE `card_purchases`
  ADD CONSTRAINT `fk_card_purchases_card` FOREIGN KEY (`credit_card_id`) REFERENCES `credit_cards` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_card_purchases_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `commitments`
--
ALTER TABLE `commitments`
  ADD CONSTRAINT `fk_commitments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `credit_cards`
--
ALTER TABLE `credit_cards`
  ADD CONSTRAINT `fk_credit_cards_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `fk_goals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `income_sources`
--
ALTER TABLE `income_sources`
  ADD CONSTRAINT `fk_income_sources_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `monthly_bills`
--
ALTER TABLE `monthly_bills`
  ADD CONSTRAINT `fk_monthly_bills_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_monthly_bills_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `monthly_item_statuses`
--
ALTER TABLE `monthly_item_statuses`
  ADD CONSTRAINT `fk_monthly_item_statuses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `monthly_sort_orders`
--
ALTER TABLE `monthly_sort_orders`
  ADD CONSTRAINT `fk_monthly_sort_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

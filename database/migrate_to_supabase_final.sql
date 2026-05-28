-- ─────────────────────────────────────────────────────────────────────────────
-- Migração Hostinger → Supabase
-- Execute no SQL Editor do seu projeto Supabase (roda como superuser, bypass RLS)
-- ─────────────────────────────────────────────────────────────────────────────

DO $$
DECLARE
  uid_jonas   UUID;
  uid_joyce   UUID;
  uid_viviane UUID;
BEGIN

-- ─── 1. Buscar UUIDs pelos emails ────────────────────────────────────────────
SELECT id INTO uid_jonas   FROM auth.users WHERE email = 'jonaslimasousa9@gmail.com';
SELECT id INTO uid_joyce   FROM auth.users WHERE email = 'joyce.lene@hotmail.com';
SELECT id INTO uid_viviane FROM auth.users WHERE email = 'vivianeux1906@gmail.com';

IF uid_jonas IS NULL THEN
  RAISE EXCEPTION 'Jonas não encontrado em auth.users. Certifique-se de que está registrado.';
END IF;

-- ─── 2. Limpar dados existentes (ordem inversa de FK) ────────────────────────
DELETE FROM monthly_sort_orders   WHERE user_id = uid_jonas;
DELETE FROM monthly_item_statuses WHERE user_id = uid_jonas;
DELETE FROM card_invoice_totals   WHERE user_id = uid_jonas;
DELETE FROM card_purchases        WHERE user_id = uid_jonas;
DELETE FROM monthly_bills         WHERE user_id = uid_jonas;
DELETE FROM income_sources        WHERE user_id = uid_jonas;
DELETE FROM commitments           WHERE user_id = uid_jonas;
DELETE FROM credit_cards          WHERE user_id = uid_jonas;
DELETE FROM categories            WHERE user_id = uid_jonas;

IF uid_joyce IS NOT NULL THEN
  DELETE FROM monthly_sort_orders   WHERE user_id = uid_joyce;
  DELETE FROM monthly_item_statuses WHERE user_id = uid_joyce;
  DELETE FROM card_invoice_totals   WHERE user_id = uid_joyce;
  DELETE FROM card_purchases        WHERE user_id = uid_joyce;
  DELETE FROM monthly_bills         WHERE user_id = uid_joyce;
  DELETE FROM income_sources        WHERE user_id = uid_joyce;
  DELETE FROM commitments           WHERE user_id = uid_joyce;
  DELETE FROM credit_cards          WHERE user_id = uid_joyce;
  DELETE FROM categories            WHERE user_id = uid_joyce;
END IF;

-- ─── 3. Profiles ─────────────────────────────────────────────────────────────
INSERT INTO profiles (id, name, monthly_income, age, avatar_path, currency, theme_color)
VALUES (uid_jonas, 'Jonas Lima', 1815.00, 26, 'uploads/avatar-1-1777322724.jpg', 'BRL', '#066ab5')
ON CONFLICT (id) DO UPDATE SET
  name           = EXCLUDED.name,
  monthly_income = EXCLUDED.monthly_income,
  age            = EXCLUDED.age,
  avatar_path    = EXCLUDED.avatar_path,
  currency       = EXCLUDED.currency,
  theme_color    = EXCLUDED.theme_color;

IF uid_joyce IS NOT NULL THEN
  INSERT INTO profiles (id, name, monthly_income, currency, theme_color)
  VALUES (uid_joyce, 'Joyce', 0.00, 'BRL', '#4f46e5')
  ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name, monthly_income = EXCLUDED.monthly_income;
END IF;

IF uid_viviane IS NOT NULL THEN
  INSERT INTO profiles (id, name, monthly_income, currency, theme_color)
  VALUES (uid_viviane, 'Viviane do Nascimento', 0.00, 'BRL', '#4f46e5')
  ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name;
END IF;

-- ─── 4. Categories ───────────────────────────────────────────────────────────
INSERT INTO categories (id, user_id, sort_order, name, type, color, icon, created_at) VALUES
(1, uid_jonas, 0, 'Casa/Fixas',  'expense', '#24589b', 'tag', '2026-04-21 16:53:00+00'),
(2, uid_jonas, 0, 'Assinaturas', 'expense', '#204b11', 'tag', '2026-04-21 16:53:30+00'),
(4, uid_jonas, 0, 'Outras',      'expense', '#60728a', 'tag', '2026-04-21 16:54:12+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 5. Credit Cards ─────────────────────────────────────────────────────────
IF uid_joyce IS NOT NULL THEN
  INSERT INTO credit_cards (id, user_id, sort_order, name, closing_day, due_day, color, created_at) VALUES
  (5, uid_joyce, 0, 'Nubank Au',      NULL, 10, '#ff00ff', '2026-04-21 21:24:48+00'),
  (6, uid_joyce, 0, 'Neon Joyci',     NULL, 15, '#00ffff', '2026-04-21 21:25:21+00'),
  (7, uid_joyce, 0, 'Cartão Mercado', NULL, 20, '#0000ff', '2026-04-21 21:25:49+00'),
  (8, uid_joyce, 0, 'Santander Pj',   NULL, 25, '#ff0000', '2026-04-21 21:26:11+00')
  ON CONFLICT (id) DO NOTHING;
END IF;

INSERT INTO credit_cards (id, user_id, sort_order, name, closing_day, due_day, color, created_at) VALUES
(9,  uid_jonas, 0, 'Fábio Nubank', 17, 20, '#441c6d', '2026-04-23 22:19:06+00'),
(10, uid_jonas, 0, 'Jonas Nubank', 20, 27, '#692bab', '2026-04-23 22:20:40+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 6. Commitments ──────────────────────────────────────────────────────────
INSERT INTO commitments (id, user_id, sort_order, title, description, amount, start_year, start_month, duration_months, status, created_at) VALUES
(3, uid_jonas, 2, 'Transferência Moto', '', 650.00, 2026, 1, 12, 'active', '2026-04-21 17:34:30+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 7. Income Sources ───────────────────────────────────────────────────────
INSERT INTO income_sources (id, user_id, sort_order, reference_month, title, amount, income_type, received_date, status, created_at) VALUES
(1,  uid_jonas, 1, '2026-05', 'Projetar Agência',   1050.00, 'other',      '2026-05-06', 'received', '2026-04-21 17:35:16+00'),
(3,  uid_jonas, 0, '2026-05', 'Outros',               415.40, 'other',      '2026-04-29', 'received', '2026-04-29 13:22:06+00'),
(5,  uid_jonas, 0, '2026-05', 'Quizena Coaph',        705.13, 'first_half', '2026-05-15', 'pending',  '2026-05-01 19:08:21+00'),
(6,  uid_jonas, 0, '2026-05', 'Final de Mês Coaph', 1035.00, 'other',      '2026-06-01', 'pending',  '2026-05-01 19:08:40+00'),
(9,  uid_jonas, 0, '2026-06', 'Quizena Coaph',        750.00, 'first_half', '2026-06-15', 'pending',  '2026-05-17 16:33:23+00'),
(10, uid_jonas, 0, '2026-06', 'Final de Mês Coaph', 1000.00, 'month_end',  '2026-06-30', 'pending',  '2026-05-17 16:33:43+00'),
(11, uid_jonas, 0, '2026-11', 'Quizena Coaph',        750.00, 'first_half', '2026-11-15', 'pending',  '2026-05-17 16:38:49+00'),
(12, uid_jonas, 0, '2026-11', 'Final de Mês Coaph', 1035.00, 'month_end',  '2026-11-17', 'pending',  '2026-05-17 16:39:03+00'),
(13, uid_jonas, 0, '2026-07', 'Quizena Coaph',        750.00, 'first_half', '2026-07-15', 'pending',  '2026-05-17 16:46:02+00'),
(14, uid_jonas, 0, '2026-07', 'Final de Mês Coaph', 1000.00, 'month_end',  '2026-07-31', 'pending',  '2026-05-17 16:46:14+00'),
(15, uid_jonas, 0, '2026-08', 'Quizena Coaph',        750.00, 'first_half', '2026-08-14', 'pending',  '2026-05-17 16:46:29+00'),
(16, uid_jonas, 0, '2026-08', 'Final de Mês Coaph', 1035.00, 'month_end',  '2026-08-31', 'pending',  '2026-05-17 16:46:41+00'),
(17, uid_jonas, 0, '2026-09', 'Quizena Coaph',        750.00, 'first_half', '2026-09-15', 'pending',  '2026-05-17 16:47:20+00'),
(18, uid_jonas, 0, '2026-09', 'Final de Mês Coaph', 1035.00, 'month_end',  '2026-09-30', 'pending',  '2026-05-17 16:47:30+00'),
(19, uid_jonas, 0, '2026-10', 'Quizena Coaph',        750.00, 'first_half', '2026-10-15', 'pending',  '2026-05-17 16:47:48+00'),
(20, uid_jonas, 0, '2026-10', 'Final de Mês Coaph', 1035.00, 'month_end',  '2026-10-30', 'pending',  '2026-05-17 16:48:00+00'),
(21, uid_jonas, 0, '2026-12', 'Quizena Coaph',        750.00, 'first_half', '2026-12-15', 'pending',  '2026-05-17 16:48:20+00'),
(22, uid_jonas, 0, '2026-12', 'Final de Mês Coaph', 1035.00, 'month_end',  '2026-12-31', 'pending',  '2026-05-17 16:48:31+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 8. Monthly Bills ────────────────────────────────────────────────────────
IF uid_joyce IS NOT NULL THEN
  INSERT INTO monthly_bills (id, user_id, sort_order, reference_month, category_id, title, amount, due_day, payment_month_offset, active, auto_create, created_at) VALUES
  (7,  uid_joyce,  3, '2026-04', NULL, 'Cred amigo 2/6',  1077.00,  5, 0, TRUE, FALSE, '2026-04-21 21:12:52+00'),
  (8,  uid_joyce,  4, '2026-04', NULL, 'Consorcio',         650.00,  6, 0, TRUE, FALSE, '2026-04-21 21:14:30+00'),
  (9,  uid_joyce,  6, '2026-04', NULL, 'Hapvida',           210.00, 10, 0, TRUE, TRUE,  '2026-04-21 21:16:08+00'),
  (11, uid_joyce,  8, '2026-04', NULL, 'Cama',              155.00, 15, 0, TRUE, TRUE,  '2026-04-21 21:23:02+00'),
  (12, uid_joyce,  5, '2026-04', NULL, 'Empréstimo Nub',    378.00, 10, 0, TRUE, TRUE,  '2026-04-21 21:23:33+00'),
  (13, uid_joyce, 10, '2026-04', NULL, 'Ceará cred',        400.00, 30, 0, TRUE, TRUE,  '2026-04-21 21:23:52+00'),
  (14, uid_joyce,  7, '2026-04', NULL, 'Empréstimo Nub',    150.00, 13, 0, TRUE, TRUE,  '2026-04-21 21:24:17+00'),
  (15, uid_joyce,  1, '2026-04', NULL, 'Escola',            480.00,  7, 0, TRUE, TRUE,  '2026-04-21 21:30:32+00'),
  (16, uid_joyce,  9, '2026-04', NULL, 'Aluguel ponto',     300.00, 20, 0, TRUE, TRUE,  '2026-04-21 21:34:55+00'),
  (17, uid_joyce,  2, '2026-04', NULL, 'Internet ponto',     75.00,  5, 0, TRUE, TRUE,  '2026-04-21 21:35:13+00'),
  (18, uid_joyce, 11, '2026-04', NULL, 'Internet Ap',        85.00, 30, 0, TRUE, TRUE,  '2026-04-21 21:35:28+00')
  ON CONFLICT (id) DO NOTHING;
END IF;

INSERT INTO monthly_bills (id, user_id, sort_order, reference_month, category_id, title, amount, due_day, payment_month_offset, active, auto_create, created_at) VALUES
(19,  uid_jonas, 1, '2026-05', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-23 19:01:36+00'),
(21,  uid_jonas, 4, '2026-05', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-04-23 22:17:06+00'),
(29,  uid_jonas, 3, '2026-05', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-04-23 22:17:28+00'),
(37,  uid_jonas, 5, '2026-05', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-04-23 22:17:39+00'),
(45,  uid_jonas, 2, '2026-05', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-04-23 22:17:49+00'),
(69,  uid_jonas, 1, '2026-06', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(70,  uid_jonas, 1, '2026-07', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(71,  uid_jonas, 1, '2026-08', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(72,  uid_jonas, 1, '2026-09', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(73,  uid_jonas, 1, '2026-10', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(74,  uid_jonas, 1, '2026-11', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(75,  uid_jonas, 1, '2026-12', 1, 'Aluguel Robercildo', 500.00, 15, 0, TRUE, TRUE, '2026-04-26 06:30:22+00'),
(83,  uid_jonas, 0, '2026-06', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(84,  uid_jonas, 0, '2026-07', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(85,  uid_jonas, 0, '2026-08', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(86,  uid_jonas, 0, '2026-09', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(87,  uid_jonas, 0, '2026-10', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(88,  uid_jonas, 0, '2026-11', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(89,  uid_jonas, 0, '2026-12', 1, 'Energia',            199.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:03+00'),
(97,  uid_jonas, 0, '2026-06', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(98,  uid_jonas, 0, '2026-07', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(99,  uid_jonas, 0, '2026-08', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(100, uid_jonas, 0, '2026-09', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(101, uid_jonas, 0, '2026-10', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(102, uid_jonas, 0, '2026-11', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(103, uid_jonas, 0, '2026-12', 1, 'Internet',            96.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:13+00'),
(104, uid_jonas, 0, '2026-06', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(105, uid_jonas, 0, '2026-07', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(106, uid_jonas, 0, '2026-08', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(107, uid_jonas, 0, '2026-09', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(108, uid_jonas, 0, '2026-10', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(109, uid_jonas, 0, '2026-11', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(110, uid_jonas, 0, '2026-12', 1, 'Laura Escola',       240.00,  1, 1, TRUE, TRUE, '2026-05-01 19:18:17+00'),
(111, uid_jonas, 0, '2026-06', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00'),
(112, uid_jonas, 0, '2026-07', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00'),
(113, uid_jonas, 0, '2026-08', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00'),
(114, uid_jonas, 0, '2026-09', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00'),
(115, uid_jonas, 0, '2026-10', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00'),
(116, uid_jonas, 0, '2026-11', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00'),
(117, uid_jonas, 0, '2026-12', 1, 'Água',               152.30,  1, 1, TRUE, TRUE, '2026-05-11 12:42:06+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 9. Card Invoice Totals ───────────────────────────────────────────────────
IF uid_joyce IS NOT NULL THEN
  INSERT INTO card_invoice_totals (id, user_id, credit_card_id, reference_month, amount, created_at, updated_at) VALUES
  (5, uid_joyce, 5, '2026-04', 208.00, '2026-04-21 21:24:48+00', NULL),
  (6, uid_joyce, 6, '2026-04', 270.00, '2026-04-21 21:25:21+00', NULL),
  (7, uid_joyce, 7, '2026-04', 118.00, '2026-04-21 21:25:49+00', NULL),
  (8, uid_joyce, 8, '2026-04', 214.00, '2026-04-21 21:26:11+00', NULL)
  ON CONFLICT (id) DO NOTHING;
END IF;

INSERT INTO card_invoice_totals (id, user_id, credit_card_id, reference_month, amount, created_at, updated_at) VALUES
(10, uid_jonas,  9, '2026-05', 2517.00, '2026-04-23 22:19:06+00', '2026-05-10 18:40:39+00'),
(11, uid_jonas, 10, '2026-05',    0.00, '2026-04-23 22:20:40+00', '2026-05-21 12:46:56+00'),
(24, uid_jonas,  9, '2026-06', 1068.20, '2026-04-27 13:11:40+00', '2026-05-21 01:06:29+00'),
(25, uid_jonas, 10, '2026-06',    0.00, '2026-04-27 13:11:50+00', '2026-05-21 12:47:16+00'),
(26, uid_jonas,  9, '2026-07', 1562.00, '2026-04-27 13:12:05+00', '2026-05-23 03:02:46+00'),
(27, uid_jonas, 10, '2026-07',    0.00, '2026-04-27 13:12:14+00', '2026-05-21 12:47:45+00'),
(28, uid_jonas,  9, '2026-08', 1245.00, '2026-04-27 13:12:30+00', '2026-05-23 03:10:03+00'),
(29, uid_jonas, 10, '2026-08',  135.00, '2026-04-27 13:12:41+00', NULL),
(30, uid_jonas,  9, '2026-09',  834.00, '2026-04-27 13:12:57+00', '2026-05-23 03:10:43+00'),
(31, uid_jonas, 10, '2026-09',  135.00, '2026-04-27 13:13:02+00', NULL),
(32, uid_jonas,  9, '2026-10',  728.00, '2026-04-27 13:13:15+00', '2026-05-23 03:11:01+00'),
(33, uid_jonas,  9, '2026-11',  613.00, '2026-04-27 13:13:31+00', '2026-05-23 03:11:15+00'),
(34, uid_jonas,  9, '2026-12',  547.00, '2026-04-27 13:13:54+00', '2026-05-23 03:11:38+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 10. Card Purchases ───────────────────────────────────────────────────────
INSERT INTO card_purchases (id, user_id, sort_order, credit_card_id, reference_month, title, description, amount, purchase_date, installment_group, installment_number, installment_total, installment_auto, created_at) VALUES
(2,   uid_jonas, 0, NULL, '2026-05', 'Fábio 4/6',            '',   240.00, NULL, NULL,                        1,  1, FALSE, '2026-04-21 17:17:01+00'),
(4,   uid_jonas, 0, NULL, '2026-05', 'Nonata 3/10',          '',    73.22, NULL, NULL,                        1,  1, FALSE, '2026-04-21 17:17:59+00'),
(5,   uid_jonas, 0, NULL, '2026-05', 'Nonata 3/6',           '',    80.24, NULL, NULL,                        1,  1, FALSE, '2026-04-21 17:18:07+00'),
(6,   uid_jonas, 0, NULL, '2026-05', 'Nonata 2/3',           '',    65.61, NULL, NULL,                        1,  1, FALSE, '2026-04-21 17:18:21+00'),
(7,   uid_jonas, 0, NULL, '2026-05', 'Celene 7/10',          '',   200.00, NULL, NULL,                        1,  1, FALSE, '2026-04-21 17:18:37+00'),
(8,   uid_jonas, 0, NULL, '2026-05', 'Junior 9/9',           '',   100.00, NULL, NULL,                        1,  1, FALSE, '2026-04-21 17:18:50+00'),
(12,  uid_jonas, 3, 9,    '2026-05', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  9,  9, TRUE,  '2026-04-23 22:24:57+00'),
(13,  uid_jonas, 1, 9,    '2026-05', 'Fabio',           '',        240.00, NULL, 'd7dec4ff6a0b156d5f3a2c07',  4,  6, TRUE,  '2026-04-23 22:25:17+00'),
(14,  uid_jonas, 6, 9,    '2026-05', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  3, 10, TRUE,  '2026-04-23 22:25:30+00'),
(15,  uid_jonas, 5, 9,    '2026-05', 'Nonata',          '',         80.24, NULL, '1c0083038a3fbc945ca516ba',  3,  6, TRUE,  '2026-04-23 22:25:44+00'),
(16,  uid_jonas, 7, 9,    '2026-05', 'Nonata',          '',         65.61, NULL, '9a9bc1c7bb6d5916a087d308',  2,  3, TRUE,  '2026-04-23 22:26:00+00'),
(17,  uid_jonas, 2, 9,    '2026-05', 'Celene',          '',        200.00, NULL, '82b0f65009bc78fb0b2b31d6',  7, 10, TRUE,  '2026-04-23 22:26:12+00'),
(18,  uid_jonas, 4, 9,    '2026-05', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  1, 10, TRUE,  '2026-04-24 18:18:15+00'),
(42,  uid_jonas, 7, 9,    '2026-06', 'Nonata',          '',         65.61, NULL, '9a9bc1c7bb6d5916a087d308',  3,  3, TRUE,  '2026-04-26 17:19:14+00'),
(43,  uid_jonas, 5, 9,    '2026-06', 'Nonata',          '',         80.24, NULL, '1c0083038a3fbc945ca516ba',  4,  6, TRUE,  '2026-04-26 17:19:19+00'),
(44,  uid_jonas, 5, 9,    '2026-07', 'Nonata',          '',         80.24, NULL, '1c0083038a3fbc945ca516ba',  5,  6, TRUE,  '2026-04-26 17:19:19+00'),
(45,  uid_jonas, 5, 9,    '2026-08', 'Nonata',          '',         80.24, NULL, '1c0083038a3fbc945ca516ba',  6,  6, TRUE,  '2026-04-26 17:19:19+00'),
(46,  uid_jonas, 6, 9,    '2026-06', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  4, 10, TRUE,  '2026-04-26 17:19:24+00'),
(47,  uid_jonas, 6, 9,    '2026-07', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  5, 10, TRUE,  '2026-04-26 17:19:24+00'),
(48,  uid_jonas, 6, 9,    '2026-08', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  6, 10, TRUE,  '2026-04-26 17:19:24+00'),
(49,  uid_jonas, 6, 9,    '2026-09', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  7, 10, TRUE,  '2026-04-26 17:19:24+00'),
(50,  uid_jonas, 6, 9,    '2026-10', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  8, 10, TRUE,  '2026-04-26 17:19:24+00'),
(51,  uid_jonas, 6, 9,    '2026-11', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2',  9, 10, TRUE,  '2026-04-26 17:19:24+00'),
(52,  uid_jonas, 6, 9,    '2026-12', 'Nonata',          '',         73.22, NULL, '04929880716aec2ab286bff2', 10, 10, TRUE,  '2026-04-26 17:19:24+00'),
(53,  uid_jonas, 1, 9,    '2026-06', 'Fabio',           '',        240.00, NULL, 'd7dec4ff6a0b156d5f3a2c07',  5,  6, TRUE,  '2026-04-26 17:19:30+00'),
(54,  uid_jonas, 1, 9,    '2026-07', 'Fabio',           '',        240.00, NULL, 'd7dec4ff6a0b156d5f3a2c07',  6,  6, TRUE,  '2026-04-26 17:19:30+00'),
(55,  uid_jonas, 2, 9,    '2026-06', 'Celene',          '',        200.00, NULL, '82b0f65009bc78fb0b2b31d6',  8, 10, TRUE,  '2026-04-26 17:46:41+00'),
(56,  uid_jonas, 2, 9,    '2026-07', 'Celene',          '',        200.00, NULL, '82b0f65009bc78fb0b2b31d6',  9, 10, TRUE,  '2026-04-26 17:46:41+00'),
(57,  uid_jonas, 2, 9,    '2026-08', 'Celene',          '',        200.00, NULL, '82b0f65009bc78fb0b2b31d6', 10, 10, TRUE,  '2026-04-26 17:46:41+00'),
(67,  uid_jonas, 4, 9,    '2026-06', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  2, 10, TRUE,  '2026-04-26 18:48:56+00'),
(68,  uid_jonas, 4, 9,    '2026-07', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  3, 10, TRUE,  '2026-04-26 18:48:56+00'),
(69,  uid_jonas, 4, 9,    '2026-08', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  4, 10, TRUE,  '2026-04-26 18:48:56+00'),
(70,  uid_jonas, 4, 9,    '2026-09', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  5, 10, TRUE,  '2026-04-26 18:48:56+00'),
(71,  uid_jonas, 4, 9,    '2026-10', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  6, 10, TRUE,  '2026-04-26 18:48:56+00'),
(72,  uid_jonas, 4, 9,    '2026-11', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  7, 10, TRUE,  '2026-04-26 18:48:56+00'),
(73,  uid_jonas, 4, 9,    '2026-12', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  8, 10, TRUE,  '2026-04-26 18:48:56+00'),
(74,  uid_jonas, 4, 9,    '2027-01', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4',  9, 10, TRUE,  '2026-04-26 18:48:56+00'),
(75,  uid_jonas, 4, 9,    '2027-02', 'Nonata',          '',         87.40, NULL, '26d42bfabcb4df918b6971a4', 10, 10, TRUE,  '2026-04-26 18:48:56+00'),
(92,  uid_jonas, 0, 9,    '2025-09', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  1,  9, TRUE,  '2026-04-28 18:02:34+00'),
(93,  uid_jonas, 0, 9,    '2025-10', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  2,  9, TRUE,  '2026-04-28 18:02:34+00'),
(94,  uid_jonas, 0, 9,    '2025-11', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  3,  9, TRUE,  '2026-04-28 18:02:34+00'),
(95,  uid_jonas, 0, 9,    '2025-12', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  4,  9, TRUE,  '2026-04-28 18:02:34+00'),
(96,  uid_jonas, 0, 9,    '2026-01', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  5,  9, TRUE,  '2026-04-28 18:02:34+00'),
(97,  uid_jonas, 0, 9,    '2026-02', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  6,  9, TRUE,  '2026-04-28 18:02:34+00'),
(98,  uid_jonas, 0, 9,    '2026-03', 'Junior',          'Cell',    100.00, NULL, '1e4969fe63699086f233aef0',  7,  9, TRUE,  '2026-04-28 18:02:34+00'),
(101, uid_jonas, 0, 9,    '2026-06', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  1, 10, TRUE,  '2026-05-20 13:15:48+00'),
(102, uid_jonas, 0, 9,    '2026-07', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  2, 10, TRUE,  '2026-05-20 13:15:48+00'),
(103, uid_jonas, 0, 9,    '2026-08', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  3, 10, TRUE,  '2026-05-20 13:15:48+00'),
(104, uid_jonas, 0, 9,    '2026-09', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  4, 10, TRUE,  '2026-05-20 13:15:48+00'),
(105, uid_jonas, 0, 9,    '2026-10', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  5, 10, TRUE,  '2026-05-20 13:15:48+00'),
(106, uid_jonas, 0, 9,    '2026-11', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  6, 10, TRUE,  '2026-05-20 13:15:48+00'),
(107, uid_jonas, 0, 9,    '2026-12', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  7, 10, TRUE,  '2026-05-20 13:15:48+00'),
(108, uid_jonas, 0, 9,    '2027-01', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  8, 10, TRUE,  '2026-05-20 13:15:48+00'),
(109, uid_jonas, 0, 9,    '2027-02', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f',  9, 10, TRUE,  '2026-05-20 13:15:48+00'),
(110, uid_jonas, 0, 9,    '2027-03', 'Fabio Tablet Metade', '',     88.00, NULL, '58ad3774044530aae668655f', 10, 10, TRUE,  '2026-05-20 13:15:48+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 11. Monthly Item Statuses ────────────────────────────────────────────────
INSERT INTO monthly_item_statuses (id, user_id, item_type, item_id, reference_month, status, actual_date, actual_amount, notes, created_at, updated_at) VALUES
(1, uid_jonas, 'monthly_bill', 19, '2026-05', 'paid', '2026-05-17', NULL, NULL, '2026-05-17 16:19:42+00', '2026-05-17 16:19:42+00'),
(2, uid_jonas, 'card_invoice',  9, '2026-05', 'paid', '2026-05-21', NULL, NULL, '2026-05-21 12:46:35+00', '2026-05-21 12:46:35+00'),
(3, uid_jonas, 'card_invoice', 10, '2026-05', 'paid', '2026-05-21', NULL, NULL, '2026-05-21 12:46:39+00', '2026-05-21 12:46:39+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 12. Monthly Sort Orders ──────────────────────────────────────────────────
INSERT INTO monthly_sort_orders (id, user_id, table_name, item_id, reference_month, sort_order, created_at, updated_at) VALUES
(1,  uid_jonas, 'card_purchases', 12, '2026-05', 1, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(2,  uid_jonas, 'card_purchases', 13, '2026-05', 2, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(3,  uid_jonas, 'card_purchases', 14, '2026-05', 3, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(4,  uid_jonas, 'card_purchases', 15, '2026-05', 5, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(5,  uid_jonas, 'card_purchases', 16, '2026-05', 6, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(6,  uid_jonas, 'card_purchases', 17, '2026-05', 7, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(7,  uid_jonas, 'card_purchases', 18, '2026-05', 4, '2026-04-29 21:01:25+00', '2026-05-18 18:52:35+00'),
(8,  uid_jonas, 'categories',      2, '2026-05', 2, '2026-04-29 22:36:20+00', '2026-04-29 22:36:23+00'),
(9,  uid_jonas, 'categories',      1, '2026-05', 1, '2026-04-29 22:36:20+00', '2026-04-29 22:36:23+00'),
(10, uid_jonas, 'categories',      4, '2026-05', 3, '2026-04-29 22:36:20+00', '2026-04-29 22:36:23+00'),
(14, uid_jonas, 'categories',      1, '2026-04', 1, '2026-04-30 13:42:15+00', '2026-04-30 13:42:21+00'),
(15, uid_jonas, 'categories',      2, '2026-04', 2, '2026-04-30 13:42:15+00', '2026-04-30 13:42:21+00'),
(16, uid_jonas, 'categories',      4, '2026-04', 3, '2026-04-30 13:42:15+00', '2026-04-30 13:42:21+00'),
(26, uid_jonas, 'income_sources',  3, '2026-05', 1, '2026-05-01 19:07:57+00', '2026-05-02 17:41:57+00'),
(27, uid_jonas, 'income_sources',  1, '2026-05', 2, '2026-05-01 19:07:57+00', '2026-05-02 17:41:57+00'),
(28, uid_jonas, 'monthly_bills',  21, '2026-05', 2, '2026-05-01 21:16:02+00', '2026-05-01 21:16:08+00'),
(29, uid_jonas, 'monthly_bills',  19, '2026-05', 1, '2026-05-01 21:16:02+00', '2026-05-01 21:16:08+00'),
(30, uid_jonas, 'monthly_bills',  29, '2026-05', 3, '2026-05-01 21:16:02+00', '2026-05-01 21:16:08+00'),
(31, uid_jonas, 'monthly_bills',  37, '2026-05', 4, '2026-05-01 21:16:02+00', '2026-05-01 21:16:08+00'),
(32, uid_jonas, 'monthly_bills',  45, '2026-05', 5, '2026-05-01 21:16:02+00', '2026-05-01 21:16:08+00'),
(40, uid_jonas, 'income_sources',  5, '2026-05', 3, '2026-05-02 17:41:57+00', '2026-05-02 17:41:57+00'),
(42, uid_jonas, 'income_sources',  6, '2026-05', 5, '2026-05-02 17:41:57+00', '2026-05-02 17:41:57+00')
ON CONFLICT (id) DO NOTHING;

-- ─── 13. Reset sequences ─────────────────────────────────────────────────────
PERFORM setval(pg_get_serial_sequence('categories',            'id'), GREATEST((SELECT MAX(id) FROM categories),            1));
PERFORM setval(pg_get_serial_sequence('credit_cards',          'id'), GREATEST((SELECT MAX(id) FROM credit_cards),          1));
PERFORM setval(pg_get_serial_sequence('commitments',           'id'), GREATEST((SELECT MAX(id) FROM commitments),           1));
PERFORM setval(pg_get_serial_sequence('income_sources',        'id'), GREATEST((SELECT MAX(id) FROM income_sources),        1));
PERFORM setval(pg_get_serial_sequence('monthly_bills',         'id'), GREATEST((SELECT MAX(id) FROM monthly_bills),         1));
PERFORM setval(pg_get_serial_sequence('card_invoice_totals',   'id'), GREATEST((SELECT MAX(id) FROM card_invoice_totals),   1));
PERFORM setval(pg_get_serial_sequence('card_purchases',        'id'), GREATEST((SELECT MAX(id) FROM card_purchases),        1));
PERFORM setval(pg_get_serial_sequence('monthly_item_statuses', 'id'), GREATEST((SELECT MAX(id) FROM monthly_item_statuses), 1));
PERFORM setval(pg_get_serial_sequence('monthly_sort_orders',   'id'), GREATEST((SELECT MAX(id) FROM monthly_sort_orders),   1));

RAISE NOTICE 'Migração concluída com sucesso!';
END;
$$;

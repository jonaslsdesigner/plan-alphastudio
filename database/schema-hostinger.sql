CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  monthly_income DECIMAL(12,2) NOT NULL DEFAULT 0,
  nickname VARCHAR(80) NULL,
  age TINYINT UNSIGNED NULL,
  avatar_path VARCHAR(255) NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'BRL',
  theme_color VARCHAR(16) NOT NULL DEFAULT '#4f46e5',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS income_sources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  reference_month CHAR(7) NOT NULL,
  title VARCHAR(140) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_income_sources_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_income_sources_user_month (user_id, reference_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS credit_cards (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  closing_day TINYINT UNSIGNED NULL,
  due_day TINYINT UNSIGNED NULL,
  color VARCHAR(16) NOT NULL DEFAULT '#191929',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_credit_cards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS card_invoice_totals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  credit_card_id BIGINT UNSIGNED NOT NULL,
  reference_month VARCHAR(7) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_invoice (user_id, credit_card_id, reference_month),
  CONSTRAINT fk_card_invoice_totals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_card_invoice_totals_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS card_purchases (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  credit_card_id BIGINT UNSIGNED NULL,
  reference_month CHAR(7) NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NULL,
  amount DECIMAL(12,2) NOT NULL,
  purchase_date DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_card_purchases_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_card_purchases_card FOREIGN KEY (credit_card_id) REFERENCES credit_cards(id) ON DELETE SET NULL,
  INDEX idx_card_purchases_user_month (user_id, reference_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commitments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NULL,
  amount DECIMAL(12,2) NOT NULL,
  start_year SMALLINT UNSIGNED NOT NULL,
  start_month TINYINT UNSIGNED NOT NULL,
  duration_months SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  status ENUM('active','done') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commitments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_commitments_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  type ENUM('income','expense') NOT NULL DEFAULT 'expense',
  color VARCHAR(16) NOT NULL DEFAULT '#4f46e5',
  icon VARCHAR(40) NOT NULL DEFAULT 'tag',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_categories_user_type (user_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  type ENUM('checking','cash','credit','saving') NOT NULL DEFAULT 'checking',
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_accounts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  account_id BIGINT UNSIGNED NULL,
  type ENUM('income','expense') NOT NULL DEFAULT 'expense',
  title VARCHAR(160) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  due_date DATE NOT NULL,
  paid_at DATE NULL,
  status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_transactions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT fk_transactions_account FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
  INDEX idx_transactions_user_date (user_id, due_date),
  INDEX idx_transactions_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS monthly_bills (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  reference_month CHAR(7) NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  title VARCHAR(160) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  due_day TINYINT UNSIGNED NOT NULL DEFAULT 1,
  active TINYINT(1) NOT NULL DEFAULT 1,
  auto_create TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_monthly_bills_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_monthly_bills_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_monthly_bills_user (user_id, reference_month, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS goals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(140) NOT NULL,
  target_amount DECIMAL(12,2) NOT NULL,
  current_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  due_date DATE NULL,
  color VARCHAR(16) NOT NULL DEFAULT '#2563eb',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

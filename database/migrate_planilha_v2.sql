USE alpha_planilhas;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS nickname VARCHAR(80) NULL AFTER monthly_income,
  ADD COLUMN IF NOT EXISTS age TINYINT UNSIGNED NULL AFTER nickname,
  ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER age;

CREATE TABLE IF NOT EXISTS income_sources (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  reference_month CHAR(7) NOT NULL,
  title VARCHAR(140) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_income_sources_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_income_sources_user_month (user_id, reference_month)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS credit_cards (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  closing_day TINYINT UNSIGNED NULL,
  due_day TINYINT UNSIGNED NULL,
  color VARCHAR(16) NOT NULL DEFAULT '#191929',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_credit_cards_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

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

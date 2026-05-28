-- Alpha Planilhas — Supabase Schema (PostgreSQL)
-- Execute no SQL Editor do seu projeto Supabase

-- ─── PROFILES (estende auth.users do Supabase) ───────────────────────────────
CREATE TABLE IF NOT EXISTS profiles (
  id          UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  name        TEXT NOT NULL DEFAULT '',
  monthly_income DECIMAL(12,2) NOT NULL DEFAULT 0,
  age         SMALLINT,
  avatar_path TEXT,
  currency    TEXT NOT NULL DEFAULT 'BRL',
  theme_color TEXT NOT NULL DEFAULT '#066ab5',
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Cria perfil automaticamente ao registrar
CREATE OR REPLACE FUNCTION handle_new_user()
RETURNS TRIGGER LANGUAGE plpgsql SECURITY DEFINER AS $$
BEGIN
  INSERT INTO profiles (id, name)
  VALUES (NEW.id, COALESCE(NEW.raw_user_meta_data->>'name', ''));
  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION handle_new_user();

-- ─── INCOME SOURCES ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS income_sources (
  id              BIGSERIAL PRIMARY KEY,
  user_id         UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  reference_month CHAR(7) NOT NULL,
  title           TEXT NOT NULL,
  amount          DECIMAL(12,2) NOT NULL,
  income_type     TEXT NOT NULL DEFAULT 'other',
  received_date   DATE,
  status          TEXT NOT NULL DEFAULT 'pending',
  sort_order      INT NOT NULL DEFAULT 0,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_income_sources_user_month ON income_sources (user_id, reference_month);

-- ─── CATEGORIES ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
  id         BIGSERIAL PRIMARY KEY,
  user_id    UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  name       TEXT NOT NULL,
  type       TEXT NOT NULL DEFAULT 'expense' CHECK (type IN ('income','expense')),
  color      TEXT NOT NULL DEFAULT '#4f46e5',
  icon       TEXT NOT NULL DEFAULT 'tag',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_categories_user_type ON categories (user_id, type);

-- ─── ACCOUNTS ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS accounts (
  id         BIGSERIAL PRIMARY KEY,
  user_id    UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  name       TEXT NOT NULL,
  type       TEXT NOT NULL DEFAULT 'checking' CHECK (type IN ('checking','cash','credit','saving')),
  balance    DECIMAL(12,2) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ─── TRANSACTIONS ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS transactions (
  id          BIGSERIAL PRIMARY KEY,
  user_id     UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  category_id BIGINT REFERENCES categories(id) ON DELETE SET NULL,
  account_id  BIGINT REFERENCES accounts(id) ON DELETE SET NULL,
  type        TEXT NOT NULL DEFAULT 'expense' CHECK (type IN ('income','expense')),
  title       TEXT NOT NULL,
  amount      DECIMAL(12,2) NOT NULL,
  due_date    DATE NOT NULL,
  paid_at     DATE,
  status      TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','paid')),
  notes       TEXT,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_transactions_user_date   ON transactions (user_id, due_date);
CREATE INDEX IF NOT EXISTS idx_transactions_user_status ON transactions (user_id, status);

-- ─── MONTHLY BILLS ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS monthly_bills (
  id                   BIGSERIAL PRIMARY KEY,
  user_id              UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  reference_month      CHAR(7) NOT NULL,
  category_id          BIGINT REFERENCES categories(id) ON DELETE SET NULL,
  title                TEXT NOT NULL,
  amount               DECIMAL(12,2) NOT NULL,
  due_day              SMALLINT NOT NULL DEFAULT 1,
  payment_month_offset SMALLINT NOT NULL DEFAULT 0,
  active               BOOLEAN NOT NULL DEFAULT TRUE,
  auto_create          BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order           INT NOT NULL DEFAULT 0,
  created_at           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_monthly_bills_user ON monthly_bills (user_id, reference_month, active);

-- ─── CREDIT CARDS ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS credit_cards (
  id          BIGSERIAL PRIMARY KEY,
  user_id     UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  name        TEXT NOT NULL,
  closing_day SMALLINT,
  due_day     SMALLINT,
  color       TEXT NOT NULL DEFAULT '#191929',
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ─── CARD INVOICE TOTALS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS card_invoice_totals (
  id               BIGSERIAL PRIMARY KEY,
  user_id          UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  credit_card_id   BIGINT NOT NULL REFERENCES credit_cards(id) ON DELETE CASCADE,
  reference_month  TEXT NOT NULL,
  amount           DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at       TIMESTAMPTZ DEFAULT NOW(),
  updated_at       TIMESTAMPTZ DEFAULT NOW(),
  UNIQUE (user_id, credit_card_id, reference_month)
);

-- ─── CARD PURCHASES ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS card_purchases (
  id                  BIGSERIAL PRIMARY KEY,
  user_id             UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  credit_card_id      BIGINT REFERENCES credit_cards(id) ON DELETE SET NULL,
  reference_month     CHAR(7) NOT NULL,
  title               TEXT NOT NULL,
  description         TEXT,
  amount              DECIMAL(12,2) NOT NULL,
  purchase_date       DATE,
  installment_group   TEXT,
  installment_number  SMALLINT NOT NULL DEFAULT 1,
  installment_total   SMALLINT NOT NULL DEFAULT 1,
  installment_auto    BOOLEAN NOT NULL DEFAULT FALSE,
  sort_order          INT NOT NULL DEFAULT 0,
  created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_card_purchases_user_month        ON card_purchases (user_id, reference_month);
CREATE INDEX IF NOT EXISTS idx_card_purchases_installments      ON card_purchases (user_id, installment_group, installment_number);

-- ─── COMMITMENTS ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commitments (
  id               BIGSERIAL PRIMARY KEY,
  user_id          UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  title            TEXT NOT NULL,
  description      TEXT,
  amount           DECIMAL(12,2) NOT NULL,
  start_year       SMALLINT NOT NULL,
  start_month      SMALLINT NOT NULL DEFAULT 1,
  duration_months  SMALLINT NOT NULL DEFAULT 12,
  status           TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active','done')),
  sort_order       INT NOT NULL DEFAULT 0,
  created_at       TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_commitments_user_status ON commitments (user_id, status);

-- ─── GOALS ───────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS goals (
  id             BIGSERIAL PRIMARY KEY,
  user_id        UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  name           TEXT NOT NULL,
  target_amount  DECIMAL(12,2) NOT NULL,
  current_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  due_date       DATE,
  color          TEXT NOT NULL DEFAULT '#2563eb',
  created_at     TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ─── MONTHLY ITEM STATUSES (roadmap) ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS monthly_item_statuses (
  id              BIGSERIAL PRIMARY KEY,
  user_id         UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  item_type       TEXT NOT NULL,
  item_id         BIGINT NOT NULL,
  reference_month CHAR(7) NOT NULL,
  status          TEXT NOT NULL DEFAULT 'pending',
  actual_date     DATE,
  actual_amount   DECIMAL(12,2),
  notes           TEXT,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (user_id, item_type, item_id, reference_month)
);
CREATE INDEX IF NOT EXISTS idx_monthly_item_statuses_month ON monthly_item_statuses (user_id, reference_month, status);

-- ─── MONTHLY SORT ORDERS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS monthly_sort_orders (
  id              BIGSERIAL PRIMARY KEY,
  user_id         UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
  table_name      TEXT NOT NULL,
  item_id         BIGINT NOT NULL,
  reference_month CHAR(7) NOT NULL,
  sort_order      INT NOT NULL,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (user_id, table_name, item_id, reference_month)
);
CREATE INDEX IF NOT EXISTS idx_monthly_sort_lookup ON monthly_sort_orders (user_id, table_name, reference_month, sort_order);

-- ─── ROW LEVEL SECURITY ──────────────────────────────────────────────────────
ALTER TABLE profiles              ENABLE ROW LEVEL SECURITY;
ALTER TABLE income_sources        ENABLE ROW LEVEL SECURITY;
ALTER TABLE categories            ENABLE ROW LEVEL SECURITY;
ALTER TABLE accounts              ENABLE ROW LEVEL SECURITY;
ALTER TABLE transactions          ENABLE ROW LEVEL SECURITY;
ALTER TABLE monthly_bills         ENABLE ROW LEVEL SECURITY;
ALTER TABLE credit_cards          ENABLE ROW LEVEL SECURITY;
ALTER TABLE card_invoice_totals   ENABLE ROW LEVEL SECURITY;
ALTER TABLE card_purchases        ENABLE ROW LEVEL SECURITY;
ALTER TABLE commitments           ENABLE ROW LEVEL SECURITY;
ALTER TABLE goals                 ENABLE ROW LEVEL SECURITY;
ALTER TABLE monthly_item_statuses ENABLE ROW LEVEL SECURITY;
ALTER TABLE monthly_sort_orders   ENABLE ROW LEVEL SECURITY;

-- Macro para criar as 4 políticas padrão em cada tabela
DO $$
DECLARE
  t TEXT;
BEGIN
  FOREACH t IN ARRAY ARRAY[
    'income_sources','categories','accounts','transactions',
    'monthly_bills','credit_cards','card_invoice_totals','card_purchases',
    'commitments','goals','monthly_item_statuses','monthly_sort_orders'
  ] LOOP
    EXECUTE format('CREATE POLICY "select_%s" ON %s FOR SELECT  USING (auth.uid() = user_id)', t, t);
    EXECUTE format('CREATE POLICY "insert_%s" ON %s FOR INSERT WITH CHECK (auth.uid() = user_id)', t, t);
    EXECUTE format('CREATE POLICY "update_%s" ON %s FOR UPDATE USING (auth.uid() = user_id)', t, t);
    EXECUTE format('CREATE POLICY "delete_%s" ON %s FOR DELETE USING (auth.uid() = user_id)', t, t);
  END LOOP;
END;
$$;

-- profiles usa id = auth.uid() (não user_id)
DROP POLICY IF EXISTS "select_profiles" ON profiles;
DROP POLICY IF EXISTS "insert_profiles" ON profiles;
DROP POLICY IF EXISTS "update_profiles" ON profiles;
DROP POLICY IF EXISTS "delete_profiles" ON profiles;
CREATE POLICY "select_profiles" ON profiles FOR SELECT  USING (auth.uid() = id);
CREATE POLICY "insert_profiles" ON profiles FOR INSERT WITH CHECK (auth.uid() = id);
CREATE POLICY "update_profiles" ON profiles FOR UPDATE USING (auth.uid() = id);
CREATE POLICY "delete_profiles" ON profiles FOR DELETE USING (auth.uid() = id);

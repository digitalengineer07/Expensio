CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE EXTENSION IF NOT EXISTS citext;

DO $$
BEGIN
    CREATE TYPE membership_role AS ENUM ('owner', 'admin', 'member');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$
BEGIN
    CREATE TYPE group_type AS ENUM ('general', 'trip', 'home', 'project');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$
BEGIN
    CREATE TYPE split_method AS ENUM ('equal', 'exact', 'percentage');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$
BEGIN
    CREATE TYPE invitation_status AS ENUM ('pending', 'accepted', 'revoked', 'expired');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

DO $$
BEGIN
    CREATE TYPE settlement_status AS ENUM ('posted', 'reversed');
EXCEPTION
    WHEN duplicate_object THEN NULL;
END $$;

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS trigger AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    full_name VARCHAR(160) NOT NULL,
    email CITEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    preferred_currency CHAR(3) NOT NULL DEFAULT 'INR',
    timezone TEXT NOT NULL DEFAULT 'UTC',
    avatar_url TEXT,
    is_email_verified BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT users_currency_code_chk CHECK (preferred_currency ~ '^[A-Z]{3}$')
);

CREATE TABLE IF NOT EXISTS groups (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    owner_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    name VARCHAR(160) NOT NULL,
    description TEXT,
    group_type group_type NOT NULL DEFAULT 'general',
    base_currency CHAR(3) NOT NULL DEFAULT 'INR',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT groups_currency_code_chk CHECK (base_currency ~ '^[A-Z]{3}$')
);

CREATE TABLE IF NOT EXISTS group_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    group_id UUID NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role membership_role NOT NULL DEFAULT 'member',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    joined_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    left_at TIMESTAMPTZ,
    UNIQUE (group_id, user_id)
);

CREATE TABLE IF NOT EXISTS expenses (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    group_id UUID REFERENCES groups(id) ON DELETE SET NULL,
    paid_by_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_by_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    title VARCHAR(180) NOT NULL,
    merchant_name VARCHAR(180),
    note TEXT,
    split_method split_method NOT NULL,
    currency_code CHAR(3) NOT NULL,
    total_amount_minor BIGINT NOT NULL,
    exchange_rate_to_group_base NUMERIC(18, 8),
    expense_date DATE NOT NULL,
    metadata JSONB NOT NULL DEFAULT '{}'::JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT expenses_amount_positive_chk CHECK (total_amount_minor > 0),
    CONSTRAINT expenses_currency_code_chk CHECK (currency_code ~ '^[A-Z]{3}$')
);

CREATE TABLE IF NOT EXISTS expense_splits (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    expense_id UUID NOT NULL REFERENCES expenses(id) ON DELETE CASCADE,
    participant_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    split_method split_method NOT NULL,
    percentage_basis_points INTEGER,
    exact_amount_minor BIGINT,
    owed_amount_minor BIGINT NOT NULL,
    currency_code CHAR(3) NOT NULL,
    note TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (expense_id, participant_user_id),
    CONSTRAINT expense_splits_currency_code_chk CHECK (currency_code ~ '^[A-Z]{3}$'),
    CONSTRAINT expense_splits_owed_non_negative_chk CHECK (owed_amount_minor >= 0),
    CONSTRAINT expense_splits_exact_non_negative_chk CHECK (exact_amount_minor IS NULL OR exact_amount_minor >= 0),
    CONSTRAINT expense_splits_percentage_chk CHECK (
        percentage_basis_points IS NULL
        OR (percentage_basis_points >= 0 AND percentage_basis_points <= 10000)
    ),
    CONSTRAINT expense_splits_shape_chk CHECK (
        (split_method = 'equal' AND percentage_basis_points IS NULL AND exact_amount_minor IS NULL)
        OR (split_method = 'exact' AND exact_amount_minor IS NOT NULL AND percentage_basis_points IS NULL)
        OR (split_method = 'percentage' AND percentage_basis_points IS NOT NULL AND exact_amount_minor IS NULL)
    )
);

CREATE TABLE IF NOT EXISTS balances (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    group_id UUID NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    debtor_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    creditor_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    currency_code CHAR(3) NOT NULL,
    net_amount_minor BIGINT NOT NULL,
    last_expense_id UUID REFERENCES expenses(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (group_id, debtor_user_id, creditor_user_id, currency_code),
    CONSTRAINT balances_positive_chk CHECK (net_amount_minor > 0),
    CONSTRAINT balances_distinct_users_chk CHECK (debtor_user_id <> creditor_user_id),
    CONSTRAINT balances_currency_code_chk CHECK (currency_code ~ '^[A-Z]{3}$')
);

CREATE TABLE IF NOT EXISTS settlements (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    group_id UUID NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    payer_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    payee_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_by_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    currency_code CHAR(3) NOT NULL,
    amount_minor BIGINT NOT NULL,
    note TEXT,
    status settlement_status NOT NULL DEFAULT 'posted',
    settled_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT settlements_positive_chk CHECK (amount_minor > 0),
    CONSTRAINT settlements_distinct_users_chk CHECK (payer_user_id <> payee_user_id),
    CONSTRAINT settlements_currency_code_chk CHECK (currency_code ~ '^[A-Z]{3}$')
);

CREATE TABLE IF NOT EXISTS group_invitations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    group_id UUID NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    created_by_user_id UUID NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    invitee_email CITEXT,
    token_hash CHAR(64) NOT NULL UNIQUE,
    status invitation_status NOT NULL DEFAULT 'pending',
    expires_at TIMESTAMPTZ NOT NULL,
    accepted_by_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    accepted_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS expense_receipts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    expense_id UUID REFERENCES expenses(id) ON DELETE CASCADE,
    storage_url TEXT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    ocr_provider VARCHAR(32) NOT NULL,
    status VARCHAR(24) NOT NULL DEFAULT 'queued',
    raw_ocr_response JSONB,
    extracted_total_minor BIGINT,
    currency_code CHAR(3),
    processed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT expense_receipts_currency_code_chk CHECK (
        currency_code IS NULL OR currency_code ~ '^[A-Z]{3}$'
    ),
    CONSTRAINT expense_receipts_status_chk CHECK (
        status IN ('queued', 'processed', 'failed')
    )
);

CREATE TABLE IF NOT EXISTS receipt_line_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    receipt_id UUID NOT NULL REFERENCES expense_receipts(id) ON DELETE CASCADE,
    line_number INTEGER NOT NULL,
    description TEXT NOT NULL,
    quantity NUMERIC(12, 3) NOT NULL DEFAULT 1,
    unit_price_minor BIGINT,
    line_total_minor BIGINT NOT NULL,
    currency_code CHAR(3),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (receipt_id, line_number),
    CONSTRAINT receipt_line_items_currency_code_chk CHECK (
        currency_code IS NULL OR currency_code ~ '^[A-Z]{3}$'
    )
);

CREATE INDEX IF NOT EXISTS idx_group_members_group_active
    ON group_members (group_id, is_active)
    WHERE is_active = TRUE;

CREATE INDEX IF NOT EXISTS idx_expenses_group_date
    ON expenses (group_id, expense_date DESC);

CREATE INDEX IF NOT EXISTS idx_expenses_paid_by
    ON expenses (paid_by_user_id, expense_date DESC);

CREATE INDEX IF NOT EXISTS idx_expense_splits_participant
    ON expense_splits (participant_user_id, expense_id);

CREATE INDEX IF NOT EXISTS idx_balances_group_currency
    ON balances (group_id, currency_code, debtor_user_id, creditor_user_id);

CREATE INDEX IF NOT EXISTS idx_settlements_group_date
    ON settlements (group_id, settled_at DESC);

CREATE INDEX IF NOT EXISTS idx_group_invitations_lookup
    ON group_invitations (group_id, status, expires_at DESC);

CREATE INDEX IF NOT EXISTS idx_expense_receipts_expense
    ON expense_receipts (expense_id);

CREATE INDEX IF NOT EXISTS idx_receipt_line_items_receipt
    ON receipt_line_items (receipt_id, line_number);

DROP TRIGGER IF EXISTS users_set_updated_at ON users;
CREATE TRIGGER users_set_updated_at
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION set_updated_at();

DROP TRIGGER IF EXISTS groups_set_updated_at ON groups;
CREATE TRIGGER groups_set_updated_at
BEFORE UPDATE ON groups
FOR EACH ROW
EXECUTE FUNCTION set_updated_at();

DROP TRIGGER IF EXISTS expenses_set_updated_at ON expenses;
CREATE TRIGGER expenses_set_updated_at
BEFORE UPDATE ON expenses
FOR EACH ROW
EXECUTE FUNCTION set_updated_at();

DROP TRIGGER IF EXISTS balances_set_updated_at ON balances;
CREATE TRIGGER balances_set_updated_at
BEFORE UPDATE ON balances
FOR EACH ROW
EXECUTE FUNCTION set_updated_at();


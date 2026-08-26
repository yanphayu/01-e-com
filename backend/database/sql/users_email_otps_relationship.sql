-- ============================================================================
-- Authentication Schema: users  ──<  email_otps   (one-to-many relationship)
-- ----------------------------------------------------------------------------
-- Relationship:
--   users.id (ONE)  ->  email_otps.user_id (MANY)
--   - One user can have zero or many email OTP records.
--   - Each email OTP belongs to exactly one user.
--   - FK on email_otps.user_id with ON DELETE CASCADE:
--     deleting a user automatically deletes all of their OTP records.
--
-- Requirements : PostgreSQL 10+ (GENERATED ALWAYS AS IDENTITY).
-- Safe re-runs : Tables/indexes use IF NOT EXISTS; constraints are checked
--                before being added so repeated executions do not error.
--
-- NOTE ON DATABASE CREATION:
--   CREATE DATABASE cannot run inside a transaction or DO block, and plain
--   SQL has no "CREATE DATABASE IF NOT EXISTS". The psql meta-command \gexec
--   below runs the CREATE DATABASE statement only when the database is
--   missing. Run this file with psql:
--       psql -U postgres -f users_email_otps_relationship.sql
-- ============================================================================


-- ============================================================================
-- 1) DATABASE
-- ----------------------------------------------------------------------------
-- This project uses the existing "laravel" database (see .env DB_DATABASE),
-- so pass it with:  psql ... -d laravel -f <this_file>
--
-- To create+use a dedicated database instead, uncomment the two lines below
-- (requires running via psql; \gexec and \c are psql meta-commands):
-- SELECT 'CREATE DATABASE auth_db'
-- WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = 'auth_db')\gexec
-- \c auth_db
-- ============================================================================


-- ============================================================================
-- 2) USERS TABLE (the "one" side)
-- ============================================================================
CREATE TABLE IF NOT EXISTS users (
    -- GENERATED ALWAYS AS IDENTITY: PostgreSQL-native auto-increment.
    -- "ALWAYS" rejects manual id inserts unless OVERRIDING SYSTEM VALUE is used.
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    -- User's e-mail address. Unique constraint also creates a B-tree index,
    -- so lookups by email are fast without an extra index.
    email      VARCHAR(255) NOT NULL,

    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),

    -- Unique constraint guarantees one account per e-mail address.
    CONSTRAINT users_email_unique UNIQUE (email)
);

COMMENT ON TABLE  users            IS 'Application users (one side of 1:N with email_otps).';
COMMENT ON COLUMN users.email      IS 'Unique e-mail address used for authentication.';
COMMENT ON CONSTRAINT users_pkey        ON users IS 'Primary key: users.id';
COMMENT ON CONSTRAINT users_email_unique ON users IS 'Prevents duplicate accounts per e-mail.';


-- ============================================================================
-- 3) EMAIL OTPS TABLE (the "many" side)
-- ============================================================================
CREATE TABLE IF NOT EXISTS email_otps (
    id         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    -- Foreign key to users.id.
    user_id    BIGINT NOT NULL,

    -- One-time passcode sent to the user's e-mail.
    otp_code   VARCHAR(10) NOT NULL,

    -- When the OTP stops being valid.
    expires_at TIMESTAMPTZ NOT NULL,

    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),

    -- ---------------------------------------------------------------------------
    -- FOREIGN KEY + CASCADE
    -- ---------------------------------------------------------------------------
    -- FK: email_otps.user_id -> users.id
    --   * Enforces that every OTP belongs to exactly one existing user.
    --   * ON DELETE CASCADE removes all of a user's OTP rows when the user
    --     row is deleted (no orphaned OTPs possible).
    --   * ON UPDATE CASCADE keeps references valid if a user id ever changes.
    -- ---------------------------------------------------------------------------
    CONSTRAINT email_otps_user_id_foreign
        FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

COMMENT ON TABLE  email_otps              IS 'One-time passwords sent to users by e-mail (many side).';
COMMENT ON COLUMN email_otps.user_id      IS 'Owner of this OTP; cascades on user delete.';
COMMENT ON COLUMN email_otps.otp_code     IS 'The one-time passcode value.';
COMMENT ON COLUMN email_otps.expires_at   IS 'Timestamp after which the OTP is invalid.';
COMMENT ON CONSTRAINT email_otps_user_id_foreign ON email_otps
    IS '1:N link to users.id; ON DELETE CASCADE cleans up OTPs with their user.';


-- ============================================================================
-- 4) INDEXES
-- ============================================================================
-- Index on the FK column speeds up:
--   * JOINs between email_otps and users
--   * The CASCADE delete (PostgreSQL must find child rows quickly)
-- Note: users.email is already indexed automatically by its UNIQUE constraint.
CREATE INDEX IF NOT EXISTS email_otps_user_id_index
    ON email_otps (user_id);

-- Optional helper: frequently the app looks up the newest OTP for a user.
CREATE INDEX IF NOT EXISTS email_otps_user_id_created_at_index
    ON email_otps (user_id, created_at DESC);

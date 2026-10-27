-- ====================================================================
-- ISUFSTPASS: SUPABASE SECURITY ADVISOR FIX SCRIPT
-- ====================================================================
-- Run this script in your Supabase Project:
-- Dashboard > SQL Editor > New query > Paste & Run
--
-- Why this is needed:
-- 1. In Supabase, all tables in the `public` schema are exposed to the
--    public PostgREST API by default.
-- 2. Supabase Security Advisor raises "RLS Disabled" errors when tables
--    do not have Row Level Security enabled.
-- 3. This script enables RLS on all tables and secures function search paths.
--
-- NOTE: Your Laravel backend connects directly via PostgreSQL (user 'postgres'),
-- which has the BYPASSRLS privilege. Laravel operations will NOT be affected!
-- ====================================================================

-- Step 1: Enable Row Level Security (RLS) on all public tables
DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN (
        SELECT tablename 
        FROM pg_tables 
        WHERE schemaname = 'public'
    ) LOOP
        EXECUTE 'ALTER TABLE public.' || quote_ident(r.tablename) || ' ENABLE ROW LEVEL SECURITY;';
    END LOOP;
END $$;

-- Step 2: Revoke direct anon & authenticated API access to sensitive tables
-- (Prevents unauthorized users from querying tables via Supabase's JS SDK / REST API)
DO $$
DECLARE
    tbl text;
    sensitive_tables text[] := ARRAY[
        'users',
        'student_profiles',
        'password_reset_tokens',
        'sessions',
        'settings',
        'audit_logs',
        'gate_logs',
        'appointments',
        'document_requests',
        'slot_availabilities',
        'feedbacks'
    ];
BEGIN
    FOREACH tbl IN ARRAY sensitive_tables LOOP
        IF EXISTS (SELECT 1 FROM pg_tables WHERE schemaname = 'public' AND tablename = tbl) THEN
            EXECUTE 'REVOKE ALL ON TABLE public.' || quote_ident(tbl) || ' FROM anon, authenticated;';
        END IF;
    END LOOP;
END $$;

-- Step 3: Secure function search paths (prevents function_search_path_mutable warning)
DO $$
DECLARE
    f RECORD;
BEGIN
    FOR f IN (
        SELECT proname, oidvectortypes(proargtypes) as argtypes
        FROM pg_proc
        JOIN pg_namespace ON pg_proc.pronamespace = pg_namespace.oid
        WHERE nspname = 'public' 
          AND prokind = 'f'
          AND proisstrict = false
    ) LOOP
        BEGIN
            EXECUTE 'ALTER FUNCTION public.' || quote_ident(f.proname) || '(' || f.argtypes || ') SET search_path = public;';
        EXCEPTION WHEN OTHERS THEN
            -- Ignore functions that cannot be altered
        END;
    END LOOP;
END $$;

-- Verify RLS status on all tables
SELECT 
    schemaname, 
    tablename, 
    rowsecurity as rls_enabled 
FROM pg_tables 
WHERE schemaname = 'public'
ORDER BY tablename;
